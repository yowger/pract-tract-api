<?php

use App\Http\Controllers\AdvisorController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DirectorDashboardController;
use App\Http\Controllers\EvaluationAnswerController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ExcuseController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormResponseController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViolationController;
use Illuminate\Support\Facades\Route;

Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'log.requests'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::patch('/users/status/bulk', [UserController::class, 'bulkUpdateStatus']);

    Route::apiResource('students', StudentController::class)->only(['index', 'show']);
    Route::patch('/students/company/bulk', [StudentController::class, 'bulkUpdateCompany']);
    Route::patch('/students/advisor/bulk', [StudentController::class, 'bulkUpdateAdvisor']);

    Route::apiResource('programs', ProgramController::class);
    Route::apiResource('sections', SectionController::class);
    Route::apiResource('excuses', ExcuseController::class);

    Route::patch('/excuses/{excuse}/approve', [ExcuseController::class, 'approve']);
    Route::patch('/excuses/{excuse}/reject', [ExcuseController::class, 'reject']);


    Route::apiResource('agents', AgentController::class)->only(['index', 'show', 'store']);

    Route::apiResource('/advisors', AdvisorController::class);

    Route::get('/companies/list', [CompanyController::class, 'list']);
    Route::apiResource('companies', CompanyController::class)->only(['index', 'show', 'update']);

    Route::apiResource('forms', FormController::class);
    Route::post('/forms/{form}/responses', [FormResponseController::class, 'store']);
    Route::get('/forms/{form}/responses', [FormResponseController::class, 'index']);

    Route::get('/director/dashboard', [DirectorDashboardController::class, 'index']);

    Route::get('/attendances/pdf', [AttendanceController::class, 'exportPdf']);
    Route::get('/attendances/charts', [AttendanceController::class, 'charts']);
    Route::post('/attendances/record', [AttendanceController::class, 'recordAttendance']);
    Route::post('/attendances/record/self', [AttendanceController::class, 'recordSelfAttendance']);
    Route::get('/attendances/status', [AttendanceController::class, 'status']);
    Route::apiResource('attendances', AttendanceController::class);

    Route::apiResource('schedules', ScheduleController::class);

    Route::apiResource('violations', ViolationController::class);

    Route::post('/evaluations/{evaluation}/assign', [EvaluationController::class, 'assignToUser']);
    Route::apiResource('evaluations', EvaluationController::class);


    Route::post('/evaluations/submit', [EvaluationAnswerController::class, 'submit']);

    Route::get('/evaluations/answers', [EvaluationAnswerController::class, 'index']);
    Route::get('/evaluations/answers/{evaluationAnswer}', [EvaluationAnswerController::class, 'show']);

    Route::get('/student-documents', [StudentDocumentController::class, 'index']);
    Route::apiResource('student-documents', StudentDocumentController::class)->except(['index']);
});
