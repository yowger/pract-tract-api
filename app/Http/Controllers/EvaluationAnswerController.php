<?php

namespace App\Http\Controllers;

use App\Models\EvaluationAnswer;
use Illuminate\Http\Request;

class EvaluationAnswerController extends Controller
{
    public function submit(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'evaluation_id' => 'required|exists:evaluations,id',
            'student_id' => 'required|exists:students,id',
            'answers' => 'required|array',
            'answers.*.question' => 'required|string',
            'answers.*.type' => 'required|string',
            'answers.*.answer' => 'required|string',
        ]);

        $validated['submitted_by'] = $user->id;

        $answer = EvaluationAnswer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation submitted successfully.',
            'data' => $answer,
        ]);
    }


    public function index()
    {
        $answers = EvaluationAnswer::with(['student.user', 'evaluation'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($answers);
    }

    public function show(EvaluationAnswer $evaluationAnswer)
    {
        $evaluationAnswer->load(['student.user', 'evaluation']);

        return response()->json($evaluationAnswer);
    }
}
