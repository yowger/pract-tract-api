<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;

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
}
