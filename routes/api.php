<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\VisitReportController;
use App\Http\Controllers\AcuerdosController;

Route::post('/login/{collaborator_number}', [AuthController::class, 'logincollaborator']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::resource('/roles', RolesController::class);
    Route::resource('/usuarios', UsersController::class);
    Route::resource('/modulos', ModuleController::class);
    Route::resource('/visitas', VisitReportController::class);
    Route::resource('/acuerdos', AcuerdosController::class);


    Route::get('/getEditVisit/{id}', [VisitReportController::class, 'getEditVisit']);
    Route::put('/editVisit/{id}', [VisitReportController::class, 'update']);
});
