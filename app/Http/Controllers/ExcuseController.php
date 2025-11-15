<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Excuse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExcuseController extends Controller
{
    public function index(Request $request)
    {
        $query = Excuse::with([
            'student.user',
            'student.company',
            'student.advisor.user'
        ])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('company_id')) {
            $query->whereHas('student.company', function ($q) use ($request) {
                $q->where('id', $request->company_id);
            });
        }

        if ($request->filled('advisor_id')) {
            $query->whereHas('student.advisor', function ($q) use ($request) {
                $q->where('id', $request->advisor_id);
            });
        }

        if ($search = $request->input('name')) {
            $query->whereHas('student.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $excuses = $query->paginate($request->input('per_page', 10));

        return response()->json($excuses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'attendance_id' => 'nullable|exists:attendances,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'attachments' => 'nullable|array',
            'attachments.*.type' => 'required|string|in:file,image',
            'attachments.*.name' => 'required|string',
            'attachments.*.url' => 'required|url',
        ]);

        $excuse = Excuse::create([
            'student_id' => $validated['student_id'],
            'attendance_id' => $validated['attendance_id'] ?? null,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
            'attachments' => $validated['attachments'] ?? null,
        ]);

        return response()->json([
            'message' => 'Excuse submitted successfully.',
            'excuse' => $excuse->load('student.user'),
        ], 201);
    }

    public function show(Excuse $excuse)
    {
        $excuse->load(['student.user']);
        return response()->json($excuse);
    }

    public function update(Request $request, Excuse $excuse)
    {
        $validated = $request->validate([
            'status' => 'in:pending,approved,rejected',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'attachments' => 'nullable|array',
            'attachments.*.type' => 'required|string|in:file,image',
            'attachments.*.name' => 'required|string',
            'attachments.*.url' => 'required|url',
        ]);

        $excuse->update(array_filter($validated));

        return response()->json([
            'message' => 'Excuse updated successfully.',
            'excuse' => $excuse->fresh('student.user'),
        ]);
    }

    public function destroy(Excuse $excuse)
    {
        $excuse->delete();

        return response()->json(['message' => 'Excuse deleted successfully.']);
    }

    public function approve(Excuse $excuse)
    {
        if ($excuse->status === 'approved') {
            return response()->json(['message' => 'Excuse already approved.'], 400);
        }

        DB::beginTransaction();

        try {
            $excuse->update(['status' => 'approved']);

            $existingAttendance = Attendance::where('student_id', $excuse->student_id)
                ->whereDate('date', $excuse->date)
                ->first();

            if (!$existingAttendance) {
                Attendance::create([
                    'student_id' => $excuse->student_id,
                    'date' => $excuse->date,
                    'status' => 'excused',
                    'remarks' => 'Excused via approved excuse slip',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Excuse approved and attendance recorded successfully.',
                'excuse' => $excuse->fresh('student.user'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reject(Excuse $excuse)
    {
        if ($excuse->status === 'rejected') {
            return response()->json(['message' => 'Excuse already rejected.'], 400);
        }

        $excuse->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Excuse rejected successfully.',
            'excuse' => $excuse->fresh('student.user'),
        ]);
    }
}
