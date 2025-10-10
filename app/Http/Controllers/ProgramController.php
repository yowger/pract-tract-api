<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = Program::query()->select('id', 'code', 'name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->get());
    }

    public function store(StoreProgramRequest $request)
    {
        $program = Program::create($request->validated());

        return response()->json([
            'message' => 'Program created successfully',
            'program' => $program,
        ], 201);
    }

    public function show(Program $program)
    {
        return response()->json($program);
    }

    public function update(UpdateProgramRequest $request, Program $program)
    {
        $program->update($request->validated());

        return response()->json([
            'message' => 'Program updated successfully',
            'program' => $program,
        ]);
    }

    public function destroy(Program $program)
    {
        $program->delete();
        return response()->json(['message' => 'Program deleted successfully']);
    }
}
