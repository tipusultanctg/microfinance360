<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\LoanAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanClosureController extends Controller
{
    /**
     * Show the form for closing a loan.
     */
    public function create(LoanAccount $loanAccount)
    {
        if ($loanAccount->status !== 'active') {
            return redirect()->route('loan-accounts.show', $loanAccount->id)
                ->with('error', 'Only active loans can be closed.');
        }

        // The amount to close is the entire remaining balance.
        $closingAmount = $loanAccount->balance;

        return view('loan-closure.create', compact('loanAccount', 'closingAmount'));
    }

    /**
     * Store a newly created resource in storage (process the closure).
     */
    public function store(Request $request, LoanAccount $loanAccount)
    {
        if ($loanAccount->status !== 'active') {
            return redirect()->route('loan-accounts.show', $loanAccount->id)
                ->with('error', 'Only active loans can be closed.');
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
        ]);

        $closingAmount = $loanAccount->balance;

        DB::transaction(function () use ($loanAccount, $closingAmount, $validated) {

            // 1. Determine the principal and interest components of the final payment.
            // This is the sum of the remaining components in the schedule.
            $pendingInstallments = $loanAccount->schedule()->where('status', '!=', 'paid')->get();
            $totalPrincipalPaidInThisTxn = $pendingInstallments->sum('principal_amount') - $pendingInstallments->sum(function($installment) {
                    // Subtract any partial payments already made to the principal portion of an installment
                    $ratio = $installment->total_amount > 0 ? $installment->amount_paid / $installment->total_amount : 0;
                    return $installment->principal_amount * $ratio;
                });

            $totalInterestPaidInThisTxn = $closingAmount - $totalPrincipalPaidInThisTxn;

            // 2. Create the final, large repayment record
            $repayment = $loanAccount->repayments()->create([
                'user_id' => auth()->id(),
                'amount' => $closingAmount,
                'payment_date' => $validated['payment_date'],
            ]);

            // 3. Create the corresponding Journal Entry
            $cashAccount = ChartOfAccount::where('name', 'Cash')->firstOrFail();
            $loansReceivableAccount = ChartOfAccount::where('name', 'Loans Receivable')->firstOrFail();
            $interestIncomeAccount = ChartOfAccount::where('name', 'Interest Income')->firstOrFail();

            $journal = $repayment->journalEntries()->create([
                'date' => $repayment->payment_date,
                'description' => "Loan Foreclosure Payment for Account #{$loanAccount->account_number}",
            ]);

            $journal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $cashAccount->id, 'type' => 'debit', 'amount' => $closingAmount],
                ['chart_of_account_id' => $loansReceivableAccount->id, 'type' => 'credit', 'amount' => round($totalPrincipalPaidInThisTxn, 2)],
                ['chart_of_account_id' => $interestIncomeAccount->id, 'type' => 'credit', 'amount' => round($totalInterestPaidInThisTxn, 2)],
            ]);

            // 4. Update the master loan account to be fully paid
            $loanAccount->update([
                'balance' => 0.00,
                'amount_paid' => $loanAccount->amount_paid + $closingAmount,
                'status' => 'closed', // Change status to 'closed'
            ]);

            // 5. Mark all remaining pending installments as 'paid'
            $loanAccount->schedule()->where('status', '!=', 'paid')->update(['status' => 'paid']);

        }); // End of transaction

        return redirect()->route('loan-accounts.show', $loanAccount->id)
            ->with('success', 'Loan has been successfully closed.');
    }
}
