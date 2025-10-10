<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index()
    {
        return Form::with('user:id,name')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'schema' => 'required|array',
        ]);

        $form = Form::create([
            'title' => $request->title,
            'schema' => $request->schema,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($form, 201);
    }

    public function show(Form $form)
    {
        return response()->json($form);
    }

    public function update(Request $request, Form $form)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'schema' => 'sometimes|array',
        ]);

        $form->update($request->only('title', 'schema'));
        return response()->json($form);
    }

    public function destroy(Form $form)
    {
        $form->delete();
        return response()->noContent();
    }
}
