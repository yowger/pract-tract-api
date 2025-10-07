<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
Route::post("/logout", [AuthController::class, "logout"])
    ->middleware('auth:sanctum');

Route::apiResource('students', StudentController::class)
    ->only(['index', 'show'])
    ->middleware('auth:sanctum');
