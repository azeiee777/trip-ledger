<?php

use Illuminate\Support\Facades\Route;
use Modules\Expense\Http\Controllers\ExpenseController;
use Modules\Expense\Http\Controllers\ExpenseApprovalController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/trips/{trip}/expenses', [ExpenseController::class, 'store'])->name('trips.expenses.store');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::post('/expenses/{expense}/approve', [ExpenseApprovalController::class, 'approve'])->name('expenses.approve');
    Route::post('/expenses/{expense}/reject', [ExpenseApprovalController::class, 'reject'])->name('expenses.reject');
});
