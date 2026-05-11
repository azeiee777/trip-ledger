<?php

use Illuminate\Support\Facades\Route;
use Modules\Trip\Http\Controllers\TripController;
use Modules\Trip\Http\Controllers\TripExportController;
use Modules\Trip\Http\Controllers\TripOtpController;
use Modules\Trip\Http\Controllers\TripStopController;

// OTP verification is public (no auth required — invitees may not have accounts)
Route::get('/trips/{trip}/verify-otp', [TripOtpController::class, 'show'])->name('trips.otp.show');
Route::post('/trips/{trip}/verify-otp', [TripOtpController::class, 'verify'])->name('trips.otp.verify');

// Guest trip view — after OTP verification, no account needed
Route::get('/trips/{trip}/guest', [TripController::class, 'guestShow'])->name('trips.guest');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('trips', TripController::class);
    Route::get('/trips/{trip}/invite/{token}', [TripController::class, 'joinViaInvite'])->name('trips.join');
    Route::get('/trips/{trip}/export-pdf', [TripExportController::class, 'pdf'])->name('trips.export-pdf');

    // Itinerary stops
    Route::post('/trips/{trip}/stops', [TripStopController::class, 'store'])->name('trips.stops.store');
    Route::put('/trips/{trip}/stops/{stop}', [TripStopController::class, 'update'])->name('trips.stops.update');
    Route::delete('/trips/{trip}/stops/{stop}', [TripStopController::class, 'destroy'])->name('trips.stops.destroy');
});
