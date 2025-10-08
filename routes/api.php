<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('students', StudentController::class)
        ->only(['index', 'show']);
    Route::patch('/students/{student}/status', [StudentController::class, 'updateStatus'])
        ->name('students.updateStatus');
    Route::apiResource('programs', ProgramController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('sections', SectionController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('agents', AgentController::class)
        ->only(['index', 'show']);
    Route::apiResource('companies', CompanyController::class)
        ->only(['index', 'show', 'update',]);
});
