<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = Agent::with([
            'user',
            'company.schedule',
            'students'
        ]);

        if ($search = $request->input('agent')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($company = $request->input('company')) {
            $query->whereHas('company', function ($q) use ($company) {
                $q->where('name', 'like', "%{$company}%");
            });
        }

        if ($email = $request->input('email')) {
            $query->whereHas('user', function ($q) use ($email) {
                $q->where('email', 'like', "%{$email}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $agents = $query->paginate($perPage);

        return response()->json($agents);
    }

    public function show(Agent $agent)
    {
        $agent->load(['user', 'company', 'students']);

        return response()->json($agent);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255|unique:companies,email',
        ]);

        $user = DB::transaction(function () use ($fields) {
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password']),
                'role' => 'agent',
                'is_active' => 1,
            ]);

            $company = Company::create([
                'name' => $fields['company_name'],
                'email' => $fields['company_email'],
                'user_id' => $user->id,
                'is_active' => 1,
            ]);

            $user->agent()->create(['company_id' => $company->id]);

            return $user;
        });

        return response()->json([
            'message' => 'Agent created successfully',
            'user' => $user,
        ], 201);
    }
}
