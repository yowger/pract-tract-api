<?php

namespace App\Http\Controllers;

use App\Models\Advisor;
use App\Models\Company;
use App\Models\Student;
use Illuminate\Http\Request;

class DirectorDashboardController extends Controller
{
    public function index()
    {
        $studentCount = Student::count();
        $advisorCount = Advisor::count();
        $companyCount = Company::count();

        $studentsByProgram = Student::with('program')
            ->get()
            ->groupBy('program.name')
            ->map->count();

        $internshipStatus = [
            'active' => Student::where('status', 'active')->count(),
            'completed' => Student::where('status', 'completed')->count(),
            'pending' => Student::where('status', 'pending')->count(),
        ];

        return response()->json([
            'counts' => [
                'students' => $studentCount,
                'advisors' => $advisorCount,
                'companies' => $companyCount,
            ],
            'charts' => [
                'students_by_program' => $studentsByProgram,
                'internship_status' => $internshipStatus,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
