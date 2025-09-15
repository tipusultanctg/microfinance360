<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SavingsTransactionController extends Controller
{
    public function storeDeposit(Request $request, SavingsAccount $savingsAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $amount = $validated['amount'];

        DB::transaction(function () use ($savingsAccount, $amount, $validated) {

            // 1. Create the base savings transaction record
            $savingsTransaction = $savingsAccount->transactions()->create([
                'user_id' => auth()->id(),
                'type' => 'deposit',
                'amount' => $amount,
                'description' => $validated['description'],
                'transaction_date' => now(),
            ]);

            // 2. Increment the account balance
            $savingsAccount->increment('balance', $amount);

            // 3. Create the Journal Entry and its Ledger Entries
            //$cashAccount = ChartOfAccount::where('name', 'Cash')->firstOrFail();
            $cashAccount = ChartOfAccount::find($validated['cash_account_id']);
            $savingsLiabilityAccount = ChartOfAccount::where('name', 'Savings Liability')->firstOrFail();

            $journal = $savingsTransaction->journalEntries()->create([
                'date' => $savingsTransaction->transaction_date,
                'description' => "Savings Deposit for Account #{$savingsAccount->account_number}",
            ]);

            $journal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $cashAccount->id, 'type' => 'debit', 'amount' => $amount],
                ['chart_of_account_id' => $savingsLiabilityAccount->id, 'type' => 'credit', 'amount' => $amount],
            ]);
        });

        return redirect()->route('savings-accounts.show', $savingsAccount->id)
            ->with('success', 'Deposit processed successfully.');
    }


    public function storeWithdrawal(Request $request, SavingsAccount $savingsAccount, bool $bypassBalanceCheck = false)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $amount = $validated['amount'];

        if (!$bypassBalanceCheck && $savingsAccount->balance < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient funds. The withdrawal amount cannot exceed the current balance.',
            ]);
        }

        DB::transaction(function () use ($savingsAccount, $amount, $validated) {

            // 1. Create the base savings transaction record
            $savingsTransaction = $savingsAccount->transactions()->create([
                'user_id' => auth()->id(),
                'type' => 'withdrawal',
                'amount' => $amount,
                'description' => $validated['description'],
                'transaction_date' => now(),
            ]);

            // 2. Decrement the account balance
            $savingsAccount->decrement('balance', $amount);

            // 3. Create the Journal Entry and its Ledger Entries
            $cashAccount = ChartOfAccount::find($validated['cash_account_id']);
            $savingsLiabilityAccount = ChartOfAccount::where('name', 'Savings Liability')->firstOrFail();

            $journal = $savingsTransaction->journalEntries()->create([
                'date' => $savingsTransaction->transaction_date,
                'description' => "Savings Withdrawal from Account #{$savingsAccount->account_number}",
            ]);

            $journal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $savingsLiabilityAccount->id, 'type' => 'debit', 'amount' => $amount],
                ['chart_of_account_id' => $cashAccount->id, 'type' => 'credit', 'amount' => $amount],
            ]);
        });

        return redirect()->route('savings-accounts.show', $savingsAccount->id)
            ->with('success', 'Withdrawal processed successfully.');
    }

    /**
     * --- NEW METHOD ---
     * Store an interest transaction. This is an internal method called by other controllers.
     *
     * @param SavingsAccount $savingsAccount
     * @param float $amount
     * @param string $description
     * @return SavingsTransaction
     */
    public function storeInterest(SavingsAccount $savingsAccount, float $amount, string $description)
    {
        // No request validation needed as this is called internally.

        return DB::transaction(function () use ($savingsAccount, $amount, $description) {
            $interestTransaction = $savingsAccount->transactions()->create([
                'user_id' => auth()->id(), // Can be the system user or the admin who triggered it
                'type' => 'interest',
                'amount' => $amount,
                'description' => $description,
                'transaction_date' => now(),
            ]);

            $savingsAccount->increment('balance', $amount);

            $interestExpenseAccount = ChartOfAccount::where('name', 'Interest Expense')->firstOrFail();
            $savingsLiabilityAccount = ChartOfAccount::where('name', 'Savings Liability')->firstOrFail();

            $journal = $interestTransaction->journalEntries()->create([
                'date' => $interestTransaction->transaction_date,
                'description' => "Interest Posting for Acc #{$savingsAccount->account_number}",
            ]);
            $journal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $interestExpenseAccount->id, 'type' => 'debit', 'amount' => $amount],
                ['chart_of_account_id' => $savingsLiabilityAccount->id, 'type' => 'credit', 'amount' => $amount],
            ]);

            return $interestTransaction;
        });
    }

    public function destroy(SavingsTransaction $savingsTransaction)
    {
        if (!auth()->user()->hasRole('Organization Admin')) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $account = $savingsTransaction->savingsAccount;
        $amount = $savingsTransaction->amount;
        $type = $savingsTransaction->type;

        DB::transaction(function () use ($savingsTransaction, $account, $amount, $type) {

            // 1. Reverse the balance update on the parent savings account
            if ($type === 'deposit' || $type === 'interest') {
                if ($account->balance < $amount) {
                    throw ValidationException::withMessages([
                        'error' => 'Reversing this deposit would result in a negative balance. Process a withdrawal instead.',
                    ]);
                }
                $account->decrement('balance', $amount);
            } elseif ($type === 'withdrawal') {
                $account->increment('balance', $amount);
            }

            // 2. Find and delete the associated GeneralLedger entry.
            // The `cascadeOnDelete` in the migration for `ledger_entries`
            // will automatically delete all of its line items.
            $savingsTransaction->journalEntries()->delete();

            // 3. Delete the original savings transaction record itself.
            $savingsTransaction->delete();
        });

        return redirect()->route('savings-accounts.show', $account->id)
            ->with('success', 'Transaction permanently deleted successfully.');
    }

    /**
     * --- NEW METHOD ---
     * Display a printable receipt for a single transaction.
     */
    public function showReceipt(SavingsTransaction $savingsTransaction)
    {
        $savingsTransaction->load(['savingsAccount.member', 'user']);
        return view('savings-transactions.receipt', compact('savingsTransaction'));
    }
}
