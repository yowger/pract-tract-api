<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
Route::post("/logout", [AuthController::class, "logout"])
    ->middleware('auth:sanctum');

Route::apiResource('students', StudentController::class)
    ->only(['index', 'show'])
    ->middleware('auth:sanctum');

Route::apiResource("programs", ProgramController::class)
    ->middleware('auth:sanctum');
Route::apiResource("sections", ProgramController::class,)
    ->middleware('auth:sanctum');
