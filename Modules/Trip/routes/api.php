<?php

use Illuminate\Support\Facades\Route;
use Modules\Trip\Http\Controllers\TripController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('trips', TripController::class)->names('trip');
});
