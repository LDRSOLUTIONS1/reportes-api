<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientVisitController;
use App\Http\Controllers\Api\VisitReportController;
use App\Http\Controllers\Api\DistributorVisitController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::resource('visit-reports', VisitReportController::class);
    Route::resource('client-visits', ClientVisitController::class);
    Route::resource('distributor-visits', DistributorVisitController::class);

});
