<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;

class SectionController extends Controller
{

    public function index()
    {
        $sections = Section::select('id', 'name')->get();

        return response()->json($sections);
    }

    public function store(StoreSectionRequest $request)
    {
        $section = Section::create($request->validated());

        return response()->json([
            'message' => 'Section created successfully',
            'section' => $section,
        ], 201);
    }

    public function show(Section $section)
    {
        return response()->json($section);
    }

    public function update(UpdateSectionRequest $request, Section $section)
    {
        $section->update($request->validated());

        return response()->json([
            'message' => 'Section updated successfully',
            'section' => $section,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        $section->delete();

        return response()->json(['message' => 'Section deleted successfully']);
    }
}
