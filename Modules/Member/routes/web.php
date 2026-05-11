<?php

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\MemberController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/trips/{trip}/members', [MemberController::class, 'store'])->name('trips.members.store');
    Route::patch('/trips/{trip}/members/{member}/toggle', [MemberController::class, 'toggle'])->name('trips.members.toggle');
    Route::delete('/trips/{trip}/members/{member}', [MemberController::class, 'destroy'])->name('trips.members.destroy');
    Route::post('/trips/{trip}/members/{member}/resend-invite', [MemberController::class, 'resendInvite'])->name('trips.members.resend-invite');
    Route::patch('/trips/{trip}/members/{member}/email', [MemberController::class, 'updateEmail'])->name('trips.members.update-email');
    Route::post('/trips/{trip}/car-groups', [MemberController::class, 'storeCarGroup'])->name('trips.cargroups.store');
    Route::delete('/trips/{trip}/car-groups/{carGroup}', [MemberController::class, 'destroyCarGroup'])->name('trips.cargroups.destroy');
});
