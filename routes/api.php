<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ModuleController;

use App\Http\Controllers\ClientVisitController;
use App\Http\Controllers\VisitReportController;
use App\Http\Controllers\DistributorVisitController;

Route::post('/login', [AuthController::class, 'loginn']);
Route::post('/login/{collaborator_number}', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::resource('/roles', RolesController::class);
    Route::resource('/usuarios', UsersController::class);
    Route::resource('/modulos', ModuleController::class);

    Route::resource('visit-reports', VisitReportController::class);
    Route::resource('client-visits', ClientVisitController::class);
    Route::resource('distributor-visits', DistributorVisitController::class);
});
