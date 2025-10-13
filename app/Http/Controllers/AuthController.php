<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\DirectorResource;
use App\Http\Resources\StudentResource;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'role' => 'required|string|in:student,director,agent,advisor,admin',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',

            'student_id' => 'required_if:role,student|string|max:50|unique:students,student_id',
            'program_id' => 'required_if:role,student|integer|exists:programs,id',
            'section_id' => 'required_if:role,student|integer|exists:sections,id',
            'advisor_id' => 'nullable|integer|exists:advisors,id',
            'company_id' => 'nullable|integer|exists:companies,id',

            'company_name' => 'required_if:role,agent|string|max:255',
            'company_email' => 'required_if:role,agent|email|max:255|unique:companies,email',
        ]);


        $user = DB::transaction(function () use ($fields, $request) {
            $isActiveDefault = in_array($fields['role'], ['student', 'advisor', 'agent']) ? false : true;

            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password']),
                'role' => $fields['role'],
                'phone' => $fields['phone'] ?? null,
                'address' => $fields['address'] ?? null,
                'is_active' => $fields['is_active'] ?? $isActiveDefault
            ]);

            if ($user->isDirector()) {
                $user->director()->create([]);
            } elseif ($user->isAdvisor()) {
                $user->advisor()->create([]);
            } elseif ($user->isAgent()) {
                $user->agent()->create([
                    'company_id' => Company::create([
                        'name' => $fields['company_name'],
                        'email' => $fields['company_email'],
                        'user_id' => $user->id,
                        'is_active' => true,
                    ])->id,
                ]);
            } elseif ($user->isStudent()) {
                $user->student()->create([
                    'student_id' => $fields['student_id'],
                    'program_id' => $fields['program_id'],
                    'section_id' => $fields['section_id'],
                    'advisor_id' => $fields['advisor_id'] ?? null,
                    'company_id' => $fields['company_id'] ?? null,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $this->resolveUserData($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        return response()->json([
            'user' => $this->resolveUserData($user),
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }


        return response()->json([
            'user' => $this->resolveUserData($user),
        ]);
    }

    private function resolveUserData($user)
    {
        return match ($user->role) {
            UserRole::Student => $this->handleStudent($user),
            UserRole::Advisor => $this->handleAdvisor($user),
            UserRole::Agent => $this->handleAgent($user),
            UserRole::Director => $this->handleDirector($user),
            UserRole::Admin => $user,
            default => $user,
        };
    }

    protected function handleStudent($user)
    {
        $user->loadMissing('student.program', 'student.section', 'student.advisor', 'student.company');
        return new StudentResource($user->student);
    }

    protected function handleAdvisor($user)
    {
        $user->loadMissing('advisor');
        return $user;
    }

    protected function handleAgent($user)
    {
        $user->loadMissing('agent.company');
        return $user;
    }

    protected function handleDirector($user)
    {
        $user->loadMissing('director');
        return new DirectorResource($user);
    }
}
