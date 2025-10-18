<?php

namespace App\Http\Controllers;

use App\Filters\StudentFilter;
use App\Http\Resources\StudentListResource;
use App\Models\Student;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        Log::info('Incoming student request:', $request->all());

        $query = Student::with([
            'user',
            'program',
            'section',
            'advisor.user',
            'company'
        ]);

        $perPage = $request->input('per_page', 10);
        $students = (new StudentFilter($query, $request))->apply()->paginate($perPage);

        return StudentListResource::collection($students);
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

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
