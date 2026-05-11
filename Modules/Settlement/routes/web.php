<?php

use Illuminate\Support\Facades\Route;
use Modules\Settlement\Http\Controllers\SettlementController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/trips/{trip}/settlements', [SettlementController::class, 'index'])->name('trips.settlements');
    Route::post('/trips/{trip}/settlements/calculate', [SettlementController::class, 'calculate'])->name('trips.settlements.calculate');
    Route::patch('/settlements/{settlement}/mark-paid', [SettlementController::class, 'markPaid'])->name('settlements.mark-paid');
});
