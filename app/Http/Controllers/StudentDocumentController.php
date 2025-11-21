<?php

namespace App\Http\Controllers;

use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentDocumentController extends Controller
{
    public function index(Request $request)
    {
        $studentId = $request->query('student_id');
        $documents = StudentDocument::with('uploader')
            ->where('student_id', $studentId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'name' => 'required|string',
            'type' => 'nullable|string',
            'url' => 'required|url',
        ]);

        $validated['uploaded_by'] = Auth::id();

        $document = StudentDocument::create($validated);

        return response()->json([
            'success' => true,
            'data' => $document,
        ]);
    }

    public function show(StudentDocument $document)
    {
        $document->load('uploader');

        return response()->json([
            'success' => true,
            'data' => $document,
        ]);
    }

    public function destroy(StudentDocument $document)
    {
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }
}
