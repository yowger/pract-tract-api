<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::with(['owner', 'agents.user'])
            ->withCount(['students']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $companies = $query->paginate(10);

        return response()->json($companies);
    }

    public function show(Company $company)
    {
        $company->load(['owner', 'agents.user']);
        $company->loadCount(['students']);

        return response()->json($company);
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'email' => "sometimes|required|email|max:255|unique:companies,email,{$company->id}",
            'is_active' => 'boolean',
        ]);

        $company->update($validated);

        return response()->json([
            'message' => 'Company updated successfully',
            'company' => $company->fresh(['owner']),
        ]);
    }
}
