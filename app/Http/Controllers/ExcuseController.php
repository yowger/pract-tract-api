<?php

namespace App\Http\Controllers;

use App\Models\Excuse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExcuseController extends Controller
{
    public function index(Request $request)
    {
        $query = Excuse::with(['student.user'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
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
}
