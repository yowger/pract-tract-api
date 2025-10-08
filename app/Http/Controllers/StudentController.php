<?php

namespace App\Http\Controllers;

use App\Filters\StudentFilter;
use App\Models\Student;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Student::with([
            'user',
            'program',
            'section',
            'advisor.user',
            'agent.company'
        ]);

        $students = (new StudentFilter($query, $request))->apply()->paginate(10);

        return response()->json($students);
    }

    public function show(Student $student)
    {
        $student->load(['user', 'program', 'section']);

        return response()->json($student);
    }

    public function updateStatus(Request $request, Student $student)
    {
        $this->authorize('updateStatus', $student);

        $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $student->update(['is_active' => $request->is_active]);

        return response()->json([
            'message' => 'Status updated successfully.',
            'student' => $student,
        ]);
    }
}
