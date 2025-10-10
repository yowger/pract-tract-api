<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormResponse;
use Illuminate\Http\Request;

class FormResponseController extends Controller
{
    public function index(Request $request, Form $form)
    {
        $query = $form->responses()->with('user:id,name')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->boolean('paginate', true)) {
            $perPage = $request->input('per_page', 10);
            $responses = $query->paginate($perPage);
        } else {
            $responses = $query->get();
        }

        return response()->json($responses);
    }

    public function store(Request $request, Form $form)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $response = FormResponse::create([
            'form_id' => $form->id,
            'user_id' => $request->user()->id,
            'answers' => $request->answers,
        ]);

        return response()->json($response, 201);
    }
}
