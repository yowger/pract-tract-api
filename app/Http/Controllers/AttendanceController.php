<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('student')->latest();

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
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

        return response()->json($attendances);
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
}
