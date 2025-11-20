<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function index()
    {
        $evaluations = Evaluation::with('users')->get();
        return response()->json($evaluations);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array',
        ]);

        $evaluation = Evaluation::create($data);

        return response()->json($evaluation, 201);
    }

    public function show(Evaluation $evaluation)
    {
        $evaluation->load('users');
        return response()->json($evaluation);
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array',
        ]);

        $evaluation->update($data);

        return response()->json($evaluation);
    }

    public function destroy(Evaluation $evaluation)
    {
        $evaluation->delete();
        return response()->json(['message' => 'Evaluation deleted']);
    }

    public function assignToUser(Request $request, Evaluation $evaluation)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        foreach ($request->user_ids as $userId) {
            $evaluation->users()->syncWithoutDetaching([
                $userId => ['assigned_at' => now()]
            ]);
        }

        return response()->json([
            'message' => 'Evaluation assigned to users successfully',
            'evaluation' => $evaluation->load('users')
        ]);
    }
}
