<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormResponse;
use Illuminate\Http\Request;

class FormResponseController extends Controller
{
    public function index(Form $form)
    {
        return $form->responses()->with('user:id,name')->get();
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

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
