<?php

namespace Modules\Expense\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\Trip\Models\Trip;
use Modules\Expense\Models\Expense;
use Modules\Core\Models\Category;
use Modules\Core\Services\ExpenseSplitService;
use Modules\Core\Services\AuditLogService;
use Modules\Core\Mail\ExpensePendingApprovalMail;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseSplitService $splitService,
        private AuditLogService $auditLog,
    ) {}

    public function store(Request $request, Trip $trip)
    {
        $this->authorize('addExpense', $trip);

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'amount'            => 'required|numeric|min:0.01|max:9999999.99',
            'expense_date'      => 'required|date',
            'category_id'       => 'nullable|exists:categories,id',
            'paid_by_member_id' => 'required|exists:trip_members,id',
            'split_type'        => 'required|in:equal,custom,percentage,per_car,personal',
            'car_group_id'      => 'nullable|exists:car_groups,id',
            'note'              => 'nullable|string|max:500',
            'splits'            => 'nullable|array',
            'splits.*'          => 'numeric|min:0',
        ]);

        $carGroup = null;
        if ($validated['split_type'] === 'per_car' && ! empty($validated['car_group_id'])) {
            $carGroup = $trip->carGroups()->findOrFail($validated['car_group_id']);
        }

        $currentMember = $trip->members()
            ->where('user_id', $request->user()->id)
            ->where('invite_status', 'accepted')
            ->first();

        $isAdmin = $currentMember?->role === 'admin';

        $expenseData = [
            ...$validated,
            'trip_id'         => $trip->id,
            'created_by'      => $request->user()->id,
            'approval_status' => $isAdmin ? 'approved' : 'pending_approval',
            'approved_by'     => $isAdmin ? $request->user()->id : null,
            'approved_at'     => $isAdmin ? now() : null,
        ];
        unset($expenseData['splits']);

        $expense = $this->splitService->createWithSplits(
            $trip,
            $expenseData,
            $validated['splits'] ?? [],
            $carGroup,
        );

        $this->auditLog->log($trip->id, $request->user()->id, 'expense_created', null, $expense->toArray());

        if (! $isAdmin) {
            $this->notifyAdminOfPendingExpense($trip, $expense);
        }

        $successMsg = $isAdmin
            ? "Expense \"{$expense->title}\" added."
            : "Expense \"{$expense->title}\" submitted for admin approval.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'expense' => $expense->load('category', 'paidByMember')]);
        }

        return redirect()->route('trips.show', ['trip' => $trip, 'tab' => 'expenses'])->with('success', $successMsg);
    }

    public function edit(Expense $expense)
    {
        $this->authorize('view', $expense->trip);
        $this->gateCanModify($expense, auth()->user());

        $categories = Category::all();
        $trip = $expense->trip()->with('activeMembers', 'carGroups')->first();
        $expense->load('splits');
        return view('expense::edit', compact('expense', 'categories', 'trip'));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorize('view', $expense->trip);
        $this->gateCanModify($expense, $request->user());

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'amount'            => 'required|numeric|min:0.01|max:9999999.99',
            'expense_date'      => 'required|date',
            'category_id'       => 'nullable|exists:categories,id',
            'paid_by_member_id' => 'required|exists:trip_members,id',
            'split_type'        => 'required|in:equal,custom,percentage,per_car,personal',
            'car_group_id'      => 'nullable|exists:car_groups,id',
            'note'              => 'nullable|string|max:500',
            'splits'            => 'nullable|array',
            'splits.*'          => 'numeric|min:0',
        ]);

        $oldData  = $expense->toArray();
        $carGroup = null;
        if ($validated['split_type'] === 'per_car' && ! empty($validated['car_group_id'])) {
            $carGroup = $expense->trip->carGroups()->findOrFail($validated['car_group_id']);
        }

        $currentMember = $expense->trip->members()
            ->where('user_id', $request->user()->id)
            ->where('invite_status', 'accepted')
            ->first();

        $isAdmin = $currentMember?->role === 'admin';

        $expenseData = $validated;
        unset($expenseData['splits']);

        // Non-admin edits reset approval
        if (! $isAdmin) {
            $expenseData['approval_status'] = 'pending_approval';
            $expenseData['approved_by']     = null;
            $expenseData['approved_at']     = null;
            $expenseData['rejection_reason'] = null;
        }

        $expense = $this->splitService->updateWithSplits(
            $expense,
            $expense->trip,
            $expenseData,
            $validated['splits'] ?? [],
            $carGroup,
        );

        $this->auditLog->log($expense->trip_id, $request->user()->id, 'expense_updated', $oldData, $expense->toArray());

        if (! $isAdmin) {
            $this->notifyAdminOfPendingExpense($expense->trip, $expense);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'expense' => $expense->load('category', 'paidByMember')]);
        }

        return redirect()->route('trips.show', ['trip' => $expense->trip, 'tab' => 'expenses'])->with('success', 'Expense updated.');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $this->authorize('view', $expense->trip);
        $this->gateCanModify($expense, $request->user());

        $oldData = $expense->toArray();
        $trip    = $expense->trip;

        $expense->delete();
        $trip->refreshTotals();

        $this->auditLog->log($trip->id, $request->user()->id, 'expense_deleted', $oldData, null);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('trips.show', ['trip' => $trip, 'tab' => 'expenses'])->with('success', 'Expense removed.');
    }

    private function gateCanModify(Expense $expense, \App\Models\User $user): void
    {
        $member = $expense->trip->members()
            ->where('user_id', $user->id)
            ->where('invite_status', 'accepted')
            ->first();

        $isAdmin    = $member?->role === 'admin';
        $isCreator  = $expense->created_by === $user->id;

        abort_unless($isAdmin || $isCreator, 403, 'You cannot modify this expense.');
    }

    private function notifyAdminOfPendingExpense(Trip $trip, Expense $expense): void
    {
        $adminMember = $trip->members()->where('role', 'admin')->first();
        $adminEmail  = $adminMember?->user?->email;

        if (! $adminEmail) {
            return;
        }

        $approveUrl = route('expenses.approve', $expense);
        $rejectUrl  = route('expenses.reject', $expense);

        try {
            Mail::to($adminEmail)->send(new ExpensePendingApprovalMail($trip, $expense, $approveUrl, $rejectUrl));
        } catch (\Exception $e) {
            logger()->error('Approval notification mail failed', ['error' => $e->getMessage(), 'expense_id' => $expense->id]);
        }
    }
}
