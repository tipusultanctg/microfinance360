<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\SavingsAccount;
use App\Models\SavingsClosure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingsClosureController extends Controller
{
    public function create(SavingsAccount $savingsAccount)
    {
        if ($savingsAccount->status !== 'active') {
            return redirect()->route('savings-accounts.show', $savingsAccount->id)
                ->with('error', 'Only active accounts can be closed.');
        }
        $cashAccounts = ChartOfAccount::where('is_cash_account', true)->get();
        return view('savings-closure.create', compact('savingsAccount', 'cashAccounts'));
    }

    public function store(Request $request, SavingsAccount $savingsAccount)
    {
        $validated = $request->validate([
            'final_interest' => 'nullable|numeric|min:0',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
            'description' => 'nullable|string|max:255',
        ]);

        $finalInterest = (float) ($validated['final_interest'] ?? 0);

        DB::transaction(function () use ($savingsAccount, $finalInterest, $validated, $request) {

            // Instantiate the controller to use its methods
            $txnController = new SavingsTransactionController();

            if ($finalInterest > 0) {
                // Call the new internal method
                $txnController->storeInterest($savingsAccount, $finalInterest, 'Final interest on account closure.');
            }

            // Get the final balance AFTER any interest has been added
            $totalWithdrawalAmount = $savingsAccount->fresh()->balance;

            // Create a new request object to pass to the withdrawal method
            $withdrawalRequest = new Request([
                'amount' => $totalWithdrawalAmount,
                'description' => $validated['description'] ?: 'Full balance withdrawal on account closure.',
                'cash_account_id' => $validated['cash_account_id'],
                'transaction_date' => now()->toDateString(),
            ]);

            // Call the withdrawal method, passing 'true' to bypass the balance check
            $txnController->storeWithdrawal($withdrawalRequest, $savingsAccount, true);

            // Create the closure record for the audit trail
            SavingsClosure::create([
                'savings_account_id' => $savingsAccount->id,
                'user_id' => auth()->id(),
                'closure_date' => now(),
                'final_interest_amount' => $finalInterest,
                'total_withdrawal_amount' => $totalWithdrawalAmount,
                'description' => $validated['description'],
            ]);

            // Finally, update the account status
            $savingsAccount->update(['status' => 'closed']);
        });

        return redirect()->route('savings-accounts.show', $savingsAccount->id)
            ->with('success', 'Savings account has been successfully closed.');
    }

    public function destroy(SavingsClosure $savingsClosure)
    {
        if (!auth()->user()->hasRole('Organization Admin')) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $account = $savingsClosure->savingsAccount;

        // Ensure the account is actually closed before trying to reverse it.
        if ($account->status !== 'closed') {
            return redirect()->back()->with('error', 'This account is not closed.');
        }

        DB::transaction(function() use ($savingsClosure, $account) {

            // --- 1. FIND THE TRANSACTIONS RELATED TO THE CLOSURE ---
            // A robust way is to find the last withdrawal and interest transactions
            // that occurred around the closure time.

            // Find the final withdrawal transaction
            $finalWithdrawal = $account->transactions()
                ->where('type', 'withdrawal')
                ->where('amount', $savingsClosure->total_withdrawal_amount)
                ->latest()
                ->first();

            // Find the final interest transaction, if one was posted
            $finalInterest = null;
            if ($savingsClosure->final_interest_amount > 0) {
                $finalInterest = $account->transactions()
                    ->where('type', 'interest')
                    ->where('amount', $savingsClosure->final_interest_amount)
                    ->latest()
                    ->first();
            }

            // --- 2. REVERSE THE TRANSACTIONS USING THE DEDICATED CONTROLLER ---
            $txnController = new SavingsTransactionController();

            // Reverse the withdrawal first, as it happened last
            if ($finalWithdrawal) {
                $txnController->destroy($finalWithdrawal);
            }

            // Reverse the interest payment
            if ($finalInterest) {
                $txnController->destroy($finalInterest);
            }

            // --- 3. REVERT THE ACCOUNT STATUS ---
            // The balance will be automatically restored by the destroy methods.
            // We just need to change the status back.
            $account->update(['status' => 'active']);

            // --- 4. DELETE THE CLOSURE RECORD ITSELF ---
            $savingsClosure->delete();
        });

        return redirect()->route('savings-accounts.show', $account->id)
            ->with('success', 'Account closure has been successfully reversed. The account is now active.');
    }
}
