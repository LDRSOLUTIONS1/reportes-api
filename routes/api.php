<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\VisitReportController;
use App\Http\Controllers\AcuerdosController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\FollowupAgreementController;

Route::post('/login/{collaborator_number}', [AuthController::class, 'logincollaborator']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::resource('/roles', RolesController::class);
    Route::resource('/usuarios', UsersController::class);
    Route::resource('/modulos', ModuleController::class);
    Route::resource('/visitas', VisitReportController::class);
    Route::resource('/acuerdos', AcuerdosController::class);
    Route::resource('/logs', LogsController::class);


    Route::get('/getEditVisit/{id}', [VisitReportController::class, 'getEditVisit']);
    Route::put('/editVisit/{id}', [VisitReportController::class, 'update']);
    Route::get('/visitas/{id}/pdf', [VisitReportController::class, 'pdf']);

    Route::post('/followup-agreements/{id}/complete', [FollowupAgreementController::class, 'complete']);
    Route::post('/followup-agreements/{id}/cancel', [FollowupAgreementController::class, 'cancel']);
    Route::post('/followup-agreements/{id}/reschedule', [FollowupAgreementController::class, 'reschedule']);
});
