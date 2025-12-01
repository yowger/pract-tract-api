<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('student_name')) {
            $query->whereHas('student.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->student_name . '%');
            });
        }

        if ($request->filled('student_id')) {
            $query->whereHas('student.user', function ($q) use ($request) {
                $q->where('id', $request->student_id);
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

        return $query;
    }

    protected $attendanceService;

    // public function __construct(AttendanceService $attendanceService)
    public function __construct()
    {
        $this->attendanceService = new AttendanceService();
    }

    public function index(Request $request)
    {
        $query = Attendance::with('student.user')->orderBy('date', 'desc');

        $query = $this->applyFilters($query, $request);

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

    public function recordSelfAttendance(Request $request)
    {
        $student = $request->user()->student;
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $photo = $request->input('photo');

        if (!is_numeric($lat) || !is_numeric($lng)) {
            return response()->json(['error' => 'Invalid or missing coordinates.'], 400);
        }

        if (!$student) {
            return response()->json(['error' => 'Student record not found for this user.'], 404);
        }

        $company = $student->company;

        if (!$company) {
            return response()->json(['error' => 'No company associated with this student.'], 404);
        }

        $schedule = $company->schedule;

        if (!$schedule) {
            return response()->json(['error' => 'No schedule found for your company.'], 404);
        }

        if (!$this->attendanceService->isScheduleActiveToday($schedule)) {
            return response()->json(['error' => 'No schedule for today.'], 400);
        }

        $attendance = $this->attendanceService->findOrCreateToday($student, $schedule);

        try {
            $message = $this->attendanceService->record(
                $attendance,
                $schedule,
                now(),
                $lat,
                $lng,
                $photo
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json([
            'message' => $message,
            'attendance' => $attendance,
        ]);
    }

    public function recordAttendance(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'schedule_id'   => 'required|exists:schedules,id',
            'time'          => 'nullable|date_format:H:i',
        ]);

        try {
            $attendance = Attendance::findOrFail($request->attendance_id);
            $schedule   = Schedule::findOrFail($request->schedule_id);
            $time = $request->filled('time') ? Carbon::parse($request->time) : now();

            $message = $this->attendanceService->record($attendance, $schedule, $time);

            return response()->json(['message' => $message, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function charts(Request $request)
    {
        $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
            'student_name' => 'nullable|string',
            'student_id'   => 'nullable|exists:students,student_id',
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

    public function status(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $company = $student->company;
        if (!$company || !$company->schedule) {
            return response()->json(['error' => 'No schedule found'], 404);
        }

        $schedule = $company->schedule;

        if (!$this->attendanceService->isScheduleActiveToday($schedule)) {
            return response()->json([
                'can_clock' => false,
                'type' => null,
                'shift' => null,
                'require_photo' => false,
                'reason' => 'No schedule today',
            ]);
        }

        $attendance = $this->attendanceService->findOrCreateToday($student, $schedule);
        $now = now();

        try {
            $session = $this->attendanceService->determineSession($schedule, $now);
        } catch (\Exception $e) {
            return response()->json([
                'can_clock' => false,
                'type' => null,
                'shift' => null,
                'require_photo' => false,
                'reason' => 'Outside attendance period',
            ]);
        }

        $type = null;
        $requirePhoto = false;

        if ($session === 'am') {
            if (!$attendance->am_time_in) {
                $type = 'IN';
                $requirePhoto = (bool)$schedule->am_require_photo_in;
            } elseif (!$attendance->am_time_out) {
                $type = 'OUT';
                $requirePhoto = (bool)$schedule->am_require_photo_out;
            }
        } else {
            if (!$attendance->pm_time_in) {
                $type = 'IN';
                $requirePhoto = (bool)$schedule->pm_require_photo_in;
            } elseif (!$attendance->pm_time_out) {
                $type = 'OUT';
                $requirePhoto = (bool)$schedule->pm_require_photo_out;
            }
        }

        $canClock = !is_null($type);

        return response()->json([
            'can_clock' => $canClock,
            'type' => $type,
            'shift' => strtoupper($session),
            'require_photo' => $requirePhoto,
        ]);
    }


    public function exportPdf(Request $request)
    {
        $query = Attendance::with('student.user')->orderBy('date', 'desc');
        $query = $this->applyFilters($query, $request);

        $attendances = $query->get();

        $html = '<h1 style="text-align:center;">Attendance Report</h1>';
        $html .= '<table width="100%" border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">';
        $html .= '<tr style="background-color:#f1f1f1;">';
        $html .= '<th>Date</th>';
        $html .= '<th>Student</th>';
        $html .= '<th>AM Time In</th>';
        $html .= '<th>AM Time Out</th>';
        $html .= '<th>PM Time In</th>';
        $html .= '<th>PM Time Out</th>';
        $html .= '</tr>';

        foreach ($attendances as $a) {
            $html .= '<tr>';
            $html .= '<td>' . $a->date . '</td>';
            $html .= '<td>' . ($a->student->user->name ?? '-') . '</td>';
            $html .= '<td>' . ($a->am_time_in ?? '') . '</td>';
            $html .= '<td>' . ($a->am_time_out ?? '') . '</td>';
            $html .= '<td>' . ($a->pm_time_in ?? '') . '</td>';
            $html .= '<td>' . ($a->pm_time_out ?? '') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $pdf = PDF::loadHTML($html);

        return $pdf->download('attendance-report.pdf');
    }
}

class AttendanceService
{
    public function record(
        Attendance $attendance,
        Schedule $schedule,
        Carbon $time,
        ?float $userLat = null,
        ?float $userLng = null,
        ?string $photo = null
    ): string {
        if (
            $schedule->lat !== null && $schedule->lng !== null && $schedule->radius !== null
            && $userLat !== null && $userLng !== null
        ) {
            $distance = $this->calculateDistance($schedule->lat, $schedule->lng, $userLat, $userLng);
            if ($distance > $schedule->radius) {
                throw new \Exception("You are outside the allowed attendance radius ({$schedule->radius} m).");
            }
        }

        $session = $this->determineSession($schedule, $time);

        if ($session === 'am') {
            if (!$attendance->am_time_in) {
                $this->handleAmTimeIn($attendance, $schedule, $time);
                if ($photo) $attendance->am_photo_in = $photo;
                $message = 'AM time in recorded successfully';
            } elseif (!$attendance->am_time_out) {
                $this->handleAmTimeOut($attendance, $schedule, $time);
                if ($photo) $attendance->am_photo_out = $photo;
                $message = 'AM time out recorded successfully';
            } else {
                throw new \Exception('AM attendance already completed');
            }
        } else {
            if (!$attendance->pm_time_in) {
                $this->handlePmTimeIn($attendance, $schedule, $time);
                if ($photo) $attendance->pm_photo_in = $photo;
                $message = 'PM time in recorded successfully';
            } elseif (!$attendance->pm_time_out) {
                $this->handlePmTimeOut($attendance, $schedule, $time);
                if ($photo) $attendance->pm_photo_out = $photo;
                $message = 'PM time out recorded successfully';
            } else {
                throw new \Exception('PM attendance already completed');
            }
        }

        $this->updateTotalDuration($attendance);
        $attendance->save();

        return $message;
    }


    protected function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function findOrCreateToday(Student $student, Schedule $schedule)
    {
        return Attendance::firstOrCreate(
            [
                'student_id' => $student->id,
                'date' => now()->toDateString(),
            ],
            [
                'am_status' => null,
                'am_time_in' => null,
                'am_time_out' => null,
                'am_photo_in' => null,
                'am_photo_out' => null,
                'am_lat_in' => $schedule->lat,
                'am_lng_in' => $schedule->lng,
                'am_radius' => $schedule->radius,

                'pm_status' => null,
                'pm_time_in' => null,
                'pm_time_out' => null,
                'pm_photo_in' => null,
                'pm_photo_out' => null,
                'pm_lat_in' => $schedule->lat,
                'pm_lng_in' => $schedule->lng,
                'pm_radius' => $schedule->radius,
            ]
        );
    }

    public function isScheduleActiveToday(Schedule $schedule): bool
    {
        $today = strtolower(now()->format('D'));
        $days = $schedule->day_of_week;

        // Log::info('Checking if schedule is active today', [
        //     'today' => $today,
        //     'schedule_days' => $days,
        //     'schedule_id' => $schedule->id,
        //     'company_id' => $schedule->company_id,
        // ]);

        if (!is_array($days)) return false;

        return in_array($today, $days);
    }

    protected function updateTotalDuration(Attendance $attendance): void
    {
        $totalMinutes = 0;

        if ($attendance->am_time_in && $attendance->am_time_out) {
            $amIn = Carbon::parse($attendance->am_time_in);
            $amOut = Carbon::parse($attendance->am_time_out);
            $totalMinutes += $amOut->diffInMinutes($amIn);
        }

        if ($attendance->pm_time_in && $attendance->pm_time_out) {
            $pmIn = Carbon::parse($attendance->pm_time_in);
            $pmOut = Carbon::parse($attendance->pm_time_out);
            $totalMinutes += $pmOut->diffInMinutes($pmIn);
        }

        $attendance->duration_minutes = $totalMinutes > 0 ? $totalMinutes : null;
    }

    public function determineSession(Schedule $schedule, Carbon $time): string
    {
        $amStart = $schedule->am_time_in ? Carbon::parse($schedule->am_time_in) : null;
        $amEnd   = $schedule->am_time_out ? Carbon::parse($schedule->am_time_out) : null;
        $pmStart = $schedule->pm_time_in ? Carbon::parse($schedule->pm_time_in) : null;
        $pmEnd   = $schedule->pm_time_out ? Carbon::parse($schedule->pm_time_out) : null;

        // Log::info('Determining session...', [
        //     'current_time' => $time->format('H:i:s'),
        //     'am_start' => $amStart?->format('H:i:s'),
        //     'am_end' => $amEnd?->format('H:i:s'),
        //     'pm_start' => $pmStart?->format('H:i:s'),
        //     'pm_end' => $pmEnd?->format('H:i:s'),
        // ]);

        if ($amStart && $amEnd) {
            $earlyInLimit = $schedule->allow_early_in
                ? $amStart->copy()->subMinutes($schedule->early_in_limit_minutes ?? 0)
                : $amStart;

            if ($time->between($earlyInLimit, $amEnd)) {
                return 'am';
            }
        }

        if ($pmStart && $pmEnd) {
            $earlyInLimit = $schedule->allow_early_in
                ? $pmStart->copy()->subMinutes($schedule->early_in_limit_minutes ?? 0)
                : $pmStart;

            if ($time->between($earlyInLimit, $pmEnd)) {
                return 'pm';
            }
        }

        throw new \Exception('You are not within any valid attendance period.');
    }


    public function handleAmTimeIn(Attendance $attendance, Schedule $schedule, Carbon $time)
    {
        $scheduled = Carbon::parse($schedule->am_time_in);
        $grace = $schedule->am_grace_period_minutes ?? 0;

        if ($schedule->allow_early_in && $time->lt($scheduled)) {
            $attendance->am_status = 'present';
        } elseif ($time->gt($scheduled->copy()->addMinutes($grace))) {
            $attendance->am_status = 'late';
        } else {
            $attendance->am_status = 'present';
        }

        $attendance->am_time_in = $time;
    }

    public function handleAmTimeOut(Attendance $attendance, Schedule $schedule, Carbon $time)
    {
        $scheduled = Carbon::parse($schedule->am_time_out);
        $undertimeGrace = $schedule->am_undertime_grace_minutes ?? 0;

        if ($time->lt($scheduled->copy()->subMinutes($undertimeGrace))) {
            $attendance->am_status = 'undertime';
        }

        $attendance->am_time_out = $time;
    }

    public function handlePmTimeIn(Attendance $attendance, Schedule $schedule, Carbon $time)
    {
        $scheduled = Carbon::parse($schedule->pm_time_in);
        $grace = $schedule->pm_grace_period_minutes ?? 0;

        if ($schedule->allow_early_in && $time->lt($scheduled)) {
            $attendance->pm_status = 'present';
        } elseif ($time->gt($scheduled->copy()->addMinutes($grace))) {
            $attendance->pm_status = 'late';
        } else {
            $attendance->pm_status = 'present';
        }

        $attendance->pm_time_in = $time;
    }


    public function handlePmTimeOut(Attendance $attendance, Schedule $schedule, Carbon $time)
    {
        $scheduled = Carbon::parse($schedule->pm_time_out);
        $undertimeGrace = $schedule->pm_undertime_grace_minutes ?? 0;

        if ($time->lt($scheduled->copy()->subMinutes($undertimeGrace))) {
            $attendance->pm_status = 'undertime';
        }

        $attendance->pm_time_out = $time;
    }
}
