<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingsAccountController extends Controller
{
    public function index()
    {
        $accounts = SavingsAccount::with(['member', 'savingsProduct'])->latest()->paginate(15);
        return view('savings-accounts.index', compact('accounts'));
    }

    public function create(Request $request)
    {
// Pre-select member if coming from member's page
        $selectedMember = $request->has('member_id') ? Member::find($request->get('member_id')) : null;

        $members = Member::orderBy('name')->get();
        $products = SavingsProduct::orderBy('name')->get();
        $cashAccounts = ChartOfAccount::where('is_cash_account', true)->get();

        return view('savings-accounts.create', compact('members', 'products', 'selectedMember','cashAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'savings_product_id' => 'required|exists:savings_products,id',
            'initial_deposit' => 'required|numeric|min:0',
            // It's good practice to also require the cash account for the initial deposit
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        // --- WRAP THE ENTIRE LOGIC IN A TRANSACTION ---
        $account = DB::transaction(function () use ($validated) {

            $accountNumber = 'SA-' . auth()->user()->tenant_id . '-' . time();

            // 1. Create the Savings Account
            $account = SavingsAccount::create([
                'member_id' => $validated['member_id'],
                'savings_product_id' => $validated['savings_product_id'],
                'account_number' => $accountNumber,
                'balance' => $validated['initial_deposit'],
            ]);

            // 2. If there was an initial deposit, create the transaction and journal entry
            if ($validated['initial_deposit'] > 0) {
                $savingsTransaction = $account->transactions()->create([
                    'user_id' => auth()->id(),
                    'type' => 'deposit',
                    'amount' => $validated['initial_deposit'],
                    'description' => 'Initial account opening deposit',
                    'transaction_date' => now(),
                ]);

                // Fetch the required ledger accounts
                $cashAccount = ChartOfAccount::find($validated['cash_account_id']);
                $savingsLiabilityAccount = ChartOfAccount::where('name', 'Savings Liability')->firstOrFail();

                // Create the Journal Entry
                $journal = $savingsTransaction->journalEntries()->create([
                    'date' => $savingsTransaction->transaction_date,
                    'description' => "Initial Deposit for Account #{$account->account_number}",
                ]);

                // Create the Ledger Entries
                $journal->ledgerEntries()->createMany([
                    ['chart_of_account_id' => $cashAccount->id, 'type' => 'debit', 'amount' => $validated['initial_deposit']],
                    ['chart_of_account_id' => $savingsLiabilityAccount->id, 'type' => 'credit', 'amount' => $validated['initial_deposit']],
                ]);
            }

            // Return the created account from the transaction closure
            return $account;
        });

        return redirect()->route('savings-accounts.show', $account->id)
            ->with('success', 'Savings account created successfully.');
    }


    public function show(SavingsAccount $savingsAccount)
    {
// Eager load everything for efficiency
        $savingsAccount->load(['member', 'savingsProduct', 'transactions.user']);
        $cashAccounts = ChartOfAccount::where('is_cash_account', true)->orderBy('name')->get();
        return view('savings-accounts.show', compact('savingsAccount','cashAccounts'));
    }

// Edit, Update, and Destroy are less common for accounts but can be implemented if needed.
// For now, they are placeholders.
    public function edit(SavingsAccount $savingsAccount)
    {
        abort(404); // Or implement a view to change status, etc.
    }

    public function update(Request $request, SavingsAccount $savingsAccount)
    {
        abort(404);
    }

    public function destroy(SavingsAccount $savingsAccount)
    {
        $savingsAccount->transactions()->delete();
        $savingsAccount->delete();

        return redirect()->route('savings-accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
