<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ViolationController extends Controller
{

    public function index()
    {
        $violations = Violation::with('student')->latest()->paginate(10);
        return response()->json($violations);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'nullable|exists:students,id',
            'name' => 'required|string|max:255',
            'violation_type' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'date' => 'required|date',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $violation = Violation::create([
            'student_id' => $request->student_id,
            'name' => $request->name,
            'violation_type' => $request->violation_type,
            'remarks' => $request->remarks,
            'date' => $request->date,
            'attachments' => $request->attachments ?? [],
        ]);

        return response()->json([
            'message' => 'Violation reported successfully.',
            'violation' => $violation->load('student'),
        ], 201);
    }

    public function show(Violation $violation)
    {
        return response()->json($violation->load('student'));
    }

    public function destroy(Violation $violation)
    {
        $violation->delete();
        return response()->json(['message' => 'Violation deleted successfully.']);
    }
}
