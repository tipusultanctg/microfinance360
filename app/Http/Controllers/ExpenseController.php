<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with(['category', 'user'])->latest('expense_date')->paginate(15);
        return view('accounting.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $expenseCategories = ChartOfAccount::where('type', 'expense')->get();
        $cashAccounts = ChartOfAccount::where('is_cash_account', true)->get();
        return view('accounting.expenses.create', compact('expenseCategories', 'cashAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'cash_account_id' => 'required|exists:chart_of_accounts,id'
        ]);

        // Ensure the selected account is actually an expense type
        $category = ChartOfAccount::find($validated['chart_of_account_id']);
        if ($category->type !== 'expense') {
            return redirect()->back()->with('error', 'The selected category is not a valid expense account.');
        }

        DB::transaction(function () use ($validated, $category) {
            // 1. Create the Expense record
            $expense = Expense::create([
                'chart_of_account_id' => $validated['chart_of_account_id'],
                'user_id' => auth()->id(),
                'expense_date' => $validated['expense_date'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
            ]);

            // 2. Post to the General Ledger
            $cashAccount = ChartOfAccount::find($validated['cash_account_id']);

            $journal = $expense->journalEntries()->create([
                'date' => $expense->expense_date,
                'description' => "Expense: {$category->name}" . ($expense->description ? " - {$expense->description}" : ""),
            ]);

            $journal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $category->id, 'type' => 'debit', 'amount' => $expense->amount],
                ['chart_of_account_id' => $cashAccount->id, 'type' => 'credit', 'amount' => $expense->amount],
            ]);
        });

        return redirect()->route('accounting.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    // Edit, Update, and Delete can be added if needed, but often expenses are reversed
    // with another journal entry rather than being deleted. We'll add them for completeness.
    public function edit(Expense $expense)
    {
        $expenseCategories = ChartOfAccount::where('type', 'expense')->orderBy('name')->get();
        return view('accounting.expenses.edit', compact('expense', 'expenseCategories'));
    }

    /**
     * --- UPDATED METHOD ---
     * Update the specified resource in storage.
     * This method reverses the old GL entry and creates a new one.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $newCategory = ChartOfAccount::find($validated['chart_of_account_id']);
        if ($newCategory->type !== 'expense') {
            throw ValidationException::withMessages([
                'chart_of_account_id' => 'The selected category is not a valid expense account.',
            ]);
        }

        DB::transaction(function () use ($expense, $validated, $newCategory) {

            // --- 1. FIND AND PERMANENTLY DELETE THE OLD JOURNAL ENTRIES ---
            // The HasJournalEntries trait on the Expense model allows this.
            // This will cascade-delete the associated LedgerEntry line items.
            $expense->journalEntries()->delete();

            // --- 2. UPDATE THE EXPENSE RECORD ITSELF ---
            $expense->update([
                'chart_of_account_id' => $validated['chart_of_account_id'],
                'expense_date' => $validated['expense_date'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
            ]);

            // --- 3. CREATE THE NEW, CORRECTED GL ENTRY ---
            $cashAccount = ChartOfAccount::where('name', 'Cash')->firstOrFail();
            $newAmount = $expense->amount;

            $newJournal = $expense->journalEntries()->create([
                'date' => $expense->expense_date,
                'description' => "Expense: {$newCategory->name}" . ($expense->description ? " - {$expense->description}" : ""),
            ]);

            $newJournal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $newCategory->id, 'type' => 'debit', 'amount' => $newAmount],
                ['chart_of_account_id' => $cashAccount->id, 'type' => 'credit', 'amount' => $newAmount],
            ]);
        });

        return redirect()->route('accounting.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }


    public function destroy(Expense $expense)
    {
        DB::transaction(function () use ($expense) {
            // Delete the associated journal entries first
            $expense->journalEntries()->delete();
            // Then delete the expense record itself
            $expense->delete();
        });

        return redirect()->route('accounting.expenses.index')
            ->with('success', 'Expense and its journal entry permanently deleted.');
    }
}
