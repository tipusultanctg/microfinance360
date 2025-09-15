<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\LoanAccount;
use App\Models\LoanRepayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanRepaymentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     * This version is compatible with the final three-table accounting system
     * and fully supports partial payments.
     */
    public function store(Request $request, LoanAccount $loanAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $paymentAmount = floatval($validated['amount']);

        // --- VALIDATION & PRE-CHECKS ---
        if ($loanAccount->status !== 'active') {
            return redirect()->back()->with('error', 'Repayments can only be made on active loans.');
        }

        if ($paymentAmount > ($loanAccount->balance + 0.001)) {
            throw ValidationException::withMessages([
                'amount' => 'The payment amount cannot exceed the remaining loan balance of $' . number_format($loanAccount->balance, 2),
            ]);
        }

        DB::transaction(function () use ($loanAccount, $paymentAmount, $validated) {

            // --- 1. ALLOCATE PAYMENT AND UPDATE SCHEDULE ---

            $pendingInstallments = $loanAccount->schedule()->where('status', '!=', 'paid')->orderBy('due_date', 'asc')->get();
            $remainingPaymentToAllocate = $paymentAmount;

            $totalPrincipalPaidInThisTxn = 0;
            $totalInterestPaidInThisTxn = 0;

            foreach ($pendingInstallments as $installment) {
                if ($remainingPaymentToAllocate <= 0) break;

                $dueOnInstallment = $installment->balance;
                $paymentForThisInstallment = min($remainingPaymentToAllocate, $dueOnInstallment);

                // This logic correctly calculates the principal/interest split even for partial payments
                if ($installment->total_amount > 0) {
                    $principalPortion = ($paymentForThisInstallment / $installment->total_amount) * $installment->principal_amount;
                    $interestPortion = $paymentForThisInstallment - $principalPortion;

                    $totalPrincipalPaidInThisTxn += $principalPortion;
                    $totalInterestPaidInThisTxn += $interestPortion;
                }

                $installment->increment('amount_paid', $paymentForThisInstallment);
                if ($installment->fresh()->balance <= 0.001) {
                    $installment->update(['status' => 'paid']);
                }

                $remainingPaymentToAllocate -= $paymentForThisInstallment;
            }

            // --- 2. CREATE THE REPAYMENT RECORD & JOURNAL ENTRY ---

            $repayment = $loanAccount->repayments()->create([
                'user_id' => auth()->id(),
                'amount' => $paymentAmount,
                'payment_date' => $validated['payment_date'],
            ]);

            // --- USE THE CORRECT 3-TABLE ACCOUNTING SYSTEM ---
            $cashAccount = ChartOfAccount::find($validated['cash_account_id']);
            $loansReceivableAccount = ChartOfAccount::where('name', 'Loans Receivable')->firstOrFail();
            $interestIncomeAccount = ChartOfAccount::where('name', 'Interest Income')->firstOrFail();

            $journal = $repayment->journalEntries()->create([
                'date' => $repayment->payment_date,
                'description' => "Loan Repayment for Account #{$loanAccount->account_number}",
            ]);

            $journal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $cashAccount->id, 'type' => 'debit', 'amount' => $paymentAmount],
                ['chart_of_account_id' => $loansReceivableAccount->id, 'type' => 'credit', 'amount' => round($totalPrincipalPaidInThisTxn, 2)],
                ['chart_of_account_id' => $interestIncomeAccount->id, 'type' => 'credit', 'amount' => round($totalInterestPaidInThisTxn, 2)],
            ]);

            // --- 3. UPDATE THE MASTER LOAN ACCOUNT ---
            $loanAccount->decrement('balance', $paymentAmount);
            $loanAccount->increment('amount_paid', $paymentAmount);

            if ($loanAccount->fresh()->balance <= 0.001) {
                $loanAccount->update(['status' => 'paid']);
                $loanAccount->schedule()->where('status', '!=', 'paid')->update(['status' => 'paid']);
            }
        });

        return redirect()->route('loan-accounts.show', $loanAccount->id)
            ->with('success', 'Repayment recorded successfully.');
    }

    public function destroy(LoanRepayment $loanRepayment)
    {
        if (!auth()->user()->hasRole('Organization Admin')) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $account = $loanRepayment->loanAccount;
        $amount = $loanRepayment->amount;

        DB::transaction(function () use ($loanRepayment, $account, $amount) {

            // 1. Revert the main account balances
            $account->increment('balance', $amount);
            $account->decrement('amount_paid', $amount);

            // --- 2. REWRITTEN & CORRECTED SCHEDULE REVERSAL LOGIC ---

            // We need to find the installments this payment was applied to.
            // A simple way is to find the most recently paid installments and revert them.
            $installmentsToRevert = $account->schedule()
                ->where('amount_paid', '>', 0)
                ->orderBy('due_date', 'desc')
                ->get();

            $amountLeftToRevert = $amount;

            foreach ($installmentsToRevert as $installment) {
                if ($amountLeftToRevert <= 0) {
                    break;
                }

                // Determine how much of this repayment was applied to this installment
                $revertFromThisInstallment = min($amountLeftToRevert, $installment->amount_paid);

                // Decrement the amount_paid on the installment
                $installment->decrement('amount_paid', $revertFromThisInstallment);

                // --- THE CRITICAL FIX ---
                // If the installment was 'paid', its balance is now positive,
                // so we must revert its status back to 'pending'.
                if ($installment->status === 'paid') {
                    $installment->update(['status' => 'pending']);
                }

                $amountLeftToRevert -= $revertFromThisInstallment;
            }

            // 3. If the master account was 'paid', revert its status to 'active'
            if ($account->status === 'paid') {
                $account->update(['status' => 'active']);
            }

            // 4. Find and delete the associated GeneralLedger entry and its line items.
            $loanRepayment->journalEntries()->delete();

            // 5. Delete the repayment record itself.
            $loanRepayment->delete();
        });

        return redirect()->route('loan-accounts.show', $account->id)
            ->with('success', 'Loan repayment permanently deleted successfully.');
    }

}
