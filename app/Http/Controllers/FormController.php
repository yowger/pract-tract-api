<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    public function index(Request $request)
    {
        $query = Form::with('user:id,name')->latest();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('company_id')) {
            $query->whereHas('companies', function ($q) use ($request) {
                $q->where('companies.id', $request->company_id);
            });
        }

        $perPage = $request->input('per_page', 10);
        $forms = $query->paginate($perPage);

        return response()->json($forms);
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

    public function assignToCompanies(Request $request, Form $form)
    {
        $request->validate([
            'company_ids' => 'required|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        DB::transaction(function () use ($form, $request) {
            $form->companies()->sync($request->company_ids);
        });

        return response()->json(['message' => 'Form assigned successfully']);
    }
}
