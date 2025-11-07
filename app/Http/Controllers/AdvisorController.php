<?php

namespace App\Http\Controllers;

use App\Models\Advisor;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdvisorController extends Controller
{
    public function index(Request $request)
    {
        $query = Advisor::with([
            'user',
        ])->withCount('students');;

        if ($search = $request->input('name')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($email = $request->input('email')) {
            $query->whereHas('user', function ($q) use ($email) {
                $q->where('email', 'like', "%{$email}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status)
                    ->orWhere('is_active', $status);
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $advisors = $query->paginate($perPage);

        return response()->json($advisors);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
        ]);

        $advisor = DB::transaction(function () use ($fields) {
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => Hash::make($fields['password']),
                'role' => 'advisor',
                'phone' => $fields['phone'] ?? null,
                'address' => $fields['address'] ?? null,
                'is_active' => true,
            ]);

            return $user->advisor()->create();
        });

        return response()->json([
            'message' => 'Advisor created successfully.',
            'advisor' => $advisor->load('user'),
        ], 201);
    }


    public function show(Advisor $advisor)
    {
        $advisor->load([
            'user',
            'students.user',
            'students.program',
            'students.section',
            'students.company',
        ]);

        return response()->json($advisor);
    }

    public function update(Request $request, Advisor $advisor)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id|unique:advisors,user_id,' . $advisor->id,
        ]);

        $advisor->update($validated);

        return response()->json([
            'message' => 'Advisor updated successfully.',
            'advisor' => $advisor->load('user'),
        ]);
    }

    public function destroy(Advisor $advisor)
    {
        $advisor->delete();

        return response()->json([
            'message' => 'Advisor deleted successfully.',
        ]);
    }
}
