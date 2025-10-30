<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Schedule;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $query = Attendance::with('student.user')->orderBy('date', 'desc');

        if ($request->filled('student_name')) {
            $query->whereHas('student.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->student_name . '%');
            });
        }

        if ($request->filled('company_id')) {
            $query->whereHas('student.company', function ($q) use ($request) {
                $q->where('id', $request->company_id);
            });
        }

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where(function ($q) use ($request) {
                $q->where('am_status', $request->status)
                    ->orWhere('pm_status', $request->status);
            });
        }

        $perPage = $request->input('per_page', 15);
        $attendances = $query->paginate($perPage);

        return AttendanceResource::collection($attendances);
    }

    public function store(StoreAttendanceRequest $request)
    {
        $attendance = Attendance::create([
            ...$request->validated(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Attendance created successfully',
            'attendance' => $attendance,
        ], 201);
    }

    public function show(Attendance $attendance)
    {
        return $attendance->load('student');
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $attendance->update([
            ...$request->validated(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Attendance updated successfully',
            'attendance' => $attendance,
        ]);
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->noContent();
    }

    public function recordAttendance(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'schedule_id'   => 'required|exists:schedules,id',
            'time'          => 'nullable|date_format:H:i',
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);
        $schedule   = Schedule::findOrFail($request->schedule_id);

        $time = $request->filled('time') ? Carbon::parse($request->time) : now();

        $message = $this->attendanceService->record($attendance, $schedule, $time);

        return response()->json(['message' => $message, 'attendance' => $attendance]);
    }

    public function charts(Request $request)
    {
        $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
            'student_name' => 'nullable|string',
            'student_id'   => 'nullable|string',
        ]);

        $companyId = $request->company_id;
        $startDate = $request->start_date ?? Carbon::now()->startOfWeek()->toDateString();
        $endDate   = $request->end_date ?? Carbon::now()->endOfWeek()->toDateString();

        $baseQuery = Attendance::whereHas('student.company', function ($q) use ($companyId) {
            $q->where('id', $companyId);
        });

        if ($request->filled('student_name')) {
            $baseQuery = $baseQuery->whereHas('student.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->student_name . '%');
            });
        }

        if ($request->filled('student_id')) {
            $baseQuery = $baseQuery->whereHas('student', function ($q) use ($request) {
                $q->where('student_id', 'like', '%' . $request->student_id . '%');
            });
        }

        $baseQuery = $baseQuery->whereBetween('date', [$startDate, $endDate]);

        $lineData = (clone $baseQuery)
            ->select(
                'date',
                DB::raw("SUM(CASE WHEN am_status='present' OR pm_status='present' THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN am_status='absent' OR pm_status='absent' THEN 1 ELSE 0 END) as absent"),
                DB::raw("SUM(CASE WHEN am_status='late' OR pm_status='late' THEN 1 ELSE 0 END) as late"),
                DB::raw("SUM(CASE WHEN am_status='excused' OR pm_status='excused' THEN 1 ELSE 0 END) as excused"),
                DB::raw("SUM(CASE WHEN am_status='undertime' OR pm_status='undertime' THEN 1 ELSE 0 END) as undertime")
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'present' => (int) $row->present,
                    'absent' => (int) $row->absent,
                    'late' => (int) $row->late,
                    'excused' => (int) $row->excused,
                    'undertime' => (int) $row->undertime,
                ];
            });

        $statusCounts = (clone $baseQuery)
            ->select(
                DB::raw("SUM(CASE WHEN am_status='present' OR pm_status='present' THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN am_status='absent' OR pm_status='absent' THEN 1 ELSE 0 END) as absent"),
                DB::raw("SUM(CASE WHEN am_status='late' OR pm_status='late' THEN 1 ELSE 0 END) as late"),
                DB::raw("SUM(CASE WHEN am_status='excused' OR pm_status='excused' THEN 1 ELSE 0 END) as excused"),
                DB::raw("SUM(CASE WHEN am_status='undertime' OR pm_status='undertime' THEN 1 ELSE 0 END) as undertime")
            )
            ->first();

        $present = (int) ($statusCounts->present ?? 0);
        $absent = (int) ($statusCounts->absent ?? 0);
        $late = (int) ($statusCounts->late ?? 0);
        $excused = (int) ($statusCounts->excused ?? 0);
        $undertime = (int) ($statusCounts->undertime ?? 0);

        $pieData = [
            ['name' => 'present', 'value' => $present],
            ['name' => 'absent', 'value' => $absent],
            ['name' => 'late', 'value' => $late],
            ['name' => 'excused', 'value' => $excused],
            ['name' => 'undertime', 'value' => $undertime],
        ];

        return response()->json([
            'lineData'   => $lineData,
            'pieData'    => $pieData,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);
    }
}
