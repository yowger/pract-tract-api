<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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

        if ($user->isStudent()) {
            $user->student()->create([
                'student_id' => $request->input('student_id'),
                'program' => $request->input('program'),
                'section' => $request->input('section'),
            ]);
        }
    }
}
