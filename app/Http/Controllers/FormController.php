<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Form::with('user:id,name')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(Form $form)
    {
        return response()->json($form);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Form $form)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'schema' => 'sometimes|array',
        ]);

        $form->update($request->only('title', 'schema'));
        return response()->json($form);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Form $form)
    {
        $form->delete();
        return response()->noContent();
    }
}
