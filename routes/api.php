<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DirectorDashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormResponseController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->post('/register', [AuthController::class, 'register']);
Route::middleware('web')->post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'log.requests'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::patch('/users/status/bulk', [UserController::class, 'bulkUpdateStatus'])
        ->name('users.bulkUpdateStatus');

    Route::apiResource('students', StudentController::class)
        ->only(['index', 'show']);
    Route::patch('/students/{student}/status', [StudentController::class, 'updateStatus'])
        ->name('students.updateStatus');
    Route::apiResource('programs', ProgramController::class);
    Route::apiResource('sections', SectionController::class);

    Route::apiResource('agents', AgentController::class)
        ->only(['index', 'show']);

    Route::apiResource('companies', CompanyController::class)
        ->only(['index', 'show', 'update',]);

    Route::apiResource('forms', FormController::class);
    Route::post('/forms/{form}/responses', [FormResponseController::class, 'store'])
        ->name('forms.responses.store');
    Route::get('/forms/{form}/responses', [FormResponseController::class, 'index'])
        ->name('forms.responses.index');

    Route::get('/director/dashboard', [DirectorDashboardController::class, 'index']);

    Route::apiResource('attendances', AttendanceController::class);
    Route::post('/attendances/record', [AttendanceController::class, 'recordAttendance'])
        ->name('attendances.record');

    Route::apiResource('schedules', ScheduleController::class);
});
