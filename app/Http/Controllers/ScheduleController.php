<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with('company');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $perPage = $request->input('per_page', 10);

        return response()->json($query->paginate($perPage));
    }

    public function store(StoreScheduleRequest $request)
    {
        $schedule = Schedule::create($request->validated());

        return response()->json([
            'message' => 'Schedule created successfully',
            'schedule' => $schedule
        ], 201);
    }

    public function show(Schedule $schedule)
    {
        return response()->json($schedule->load('company'), 200);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $schedule->update($request->validated());

        return response()->json([
            'message' => 'Schedule updated successfully',
            'schedule' => $schedule
        ], 200);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return response()->noContent();
    }
}
