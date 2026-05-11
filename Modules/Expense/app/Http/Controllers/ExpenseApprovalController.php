<?php

namespace Modules\Expense\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\Expense\Models\Expense;
use Modules\Core\Mail\ExpenseDecisionMail;

class ExpenseApprovalController extends Controller
{
    public function approve(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense->trip);
        abort_unless($expense->approval_status === 'pending_approval', 422, 'Expense is not pending approval.');

        $expense->update([
            'approval_status' => 'approved',
            'approved_by'     => $request->user()->id,
            'approved_at'     => now(),
            'rejection_reason' => null,
        ]);

        $expense->trip->refreshTotals();

        $this->sendDecisionMail($expense, 'approved');

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('trips.show', ['trip' => $expense->trip, 'tab' => 'expenses'])->with('success', 'Expense approved.');
    }

    public function reject(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense->trip);
        abort_unless($expense->approval_status === 'pending_approval', 422, 'Expense is not pending approval.');

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $expense->update([
            'approval_status'  => 'rejected',
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        $expense->trip->refreshTotals();

        $this->sendDecisionMail($expense, 'rejected', $validated['reason'] ?? null);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('trips.show', ['trip' => $expense->trip, 'tab' => 'expenses'])->with('success', 'Expense rejected.');
    }

    private function sendDecisionMail(Expense $expense, string $decision, ?string $reason = null): void
    {
        $creatorUser = $expense->createdBy;
        if (! $creatorUser?->email) {
            return;
        }

        try {
            Mail::to($creatorUser->email)->send(
                new ExpenseDecisionMail($expense->trip, $expense, $decision, $reason)
            );
        } catch (\Exception $e) {
            logger()->error('Expense decision mail failed', ['error' => $e->getMessage(), 'expense_id' => $expense->id]);
        }
    }
}
