<?php

namespace App\Http\Controllers;

use App\Filters\StudentFilter;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
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
}
