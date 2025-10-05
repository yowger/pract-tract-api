<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'role' => $fields['role'],
            'phone' => $fields['phone'] ?? null,
            'address' => $fields['address'] ?? null,
            'is_active' => $fields['is_active'] ?? true,
        ]);

        if ($user->isDirector()) {
            $user->director()->create([]);
        } elseif ($user->isAdvisor()) {
            $user->advisor()->create(attributes: []);
        } elseif ($user->isAgent()) {
            $user->agent()->create([]);
        } elseif ($user->isStudent()) {
            $user->student()->create([
                'student_id' => $request->input('student_id'),
                'program' => $request->input('program'),
                'section' => $request->input('section'),
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User logged out successfully',
        ], 200);
    }
}
