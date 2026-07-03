<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientVisitController;
use App\Http\Controllers\Api\VisitReportController;
use App\Http\Controllers\Api\DistributorVisitController;

Route::post('/login', [AuthController::class, 'loginn']);
Route::post('/login/{collaborator_number}', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    Route::resource('visit-reports', VisitReportController::class);
    Route::resource('client-visits', ClientVisitController::class);
    Route::resource('distributor-visits', DistributorVisitController::class);
});
