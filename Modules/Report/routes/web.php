<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\ReportController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/trips/{trip}/report', [ReportController::class, 'show'])->name('trips.report');
});
