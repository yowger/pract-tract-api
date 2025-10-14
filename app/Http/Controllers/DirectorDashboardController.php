<?php

namespace App\Http\Controllers;

use App\Enums\StudentStatus;
use App\Enums\UserStatus;
use App\Models\Advisor;
use App\Models\Company;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class DirectorDashboardController extends Controller
{
    public function index()
    {
        $studentCount = Student::whereHas('user', function ($q) {
            $q->where('status', UserStatus::Accepted);
        })->count();

        $advisorCount = Advisor::whereHas('user', function ($q) {
            $q->where('status', UserStatus::Accepted);
        })->count();

        $companyCount = Company::where('is_active', true)->count();

        $studentsByProgram = Student::whereHas('user', function ($q) {
            $q->where('status', UserStatus::Accepted);
        })
            ->with('program')
            ->get()
            ->groupBy(fn($s) => $s->program->name ?? 'No Program')
            ->map->count();

        $internshipStatus = [
            'active' => Student::where('status', StudentStatus::Active)->count(),
            'completed' => Student::where('status', StudentStatus::Completed)->count(),
            'pending' => Student::where('status', StudentStatus::Pending)->count(),
        ];

        $pendingUsers = [
            'students' => User::where('role', 'student')
                ->where('status', UserStatus::Pending)
                ->count(),
            'advisors' => User::where('role', 'advisor')
                ->where('status', UserStatus::Pending)
                ->count(),
            'agents' => User::where('role', 'agent')
                ->where('status', UserStatus::Pending)
                ->count(),
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
            'pending_users' => $pendingUsers,
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
