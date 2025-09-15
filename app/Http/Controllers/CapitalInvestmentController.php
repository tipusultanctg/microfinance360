<?php

namespace App\Http\Controllers;

use App\Models\CapitalInvestment;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapitalInvestmentController extends Controller
{
    public function index()
    {
        $investments = CapitalInvestment::with(['assetAccount', 'equityAccount', 'user'])->latest('investment_date')->paginate(15);
        return view('accounting.capital-investments.index', compact('investments'));
    }

    public function create()
    {
        $cashAccounts = ChartOfAccount::where('is_cash_account', true)->orderBy('name')->get();
        $equityAccounts = ChartOfAccount::where('type', 'equity')->orderBy('name')->get();
        return view('accounting.capital-investments.create', compact('cashAccounts', 'equityAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'investment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'asset_account_id' => 'required|exists:chart_of_accounts,id',
            'equity_account_id' => 'required|exists:chart_of_accounts,id',
            'description' => 'nullable|string|max:255',
        ]);

        // Further validation to ensure correct account types are used
        $assetAccount = ChartOfAccount::find($validated['asset_account_id']);
        if (!$assetAccount || !$assetAccount->is_cash_account) {
            return back()->with('error', 'Invalid asset account selected.');
        }
        $equityAccount = ChartOfAccount::find($validated['equity_account_id']);
        if (!$equityAccount || $equityAccount->type !== 'equity') {
            return back()->with('error', 'Invalid equity account selected.');
        }

        DB::transaction(function () use ($validated, $assetAccount, $equityAccount) {
            // 1. Create the Capital Investment record
            $investment = CapitalInvestment::create([
                'user_id' => auth()->id(),
                'asset_account_id' => $validated['asset_account_id'],
                'equity_account_id' => $validated['equity_account_id'],
                'investment_date' => $validated['investment_date'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
            ]);

            // 2. Post to the General Ledger
            $journal = $investment->journalEntries()->create([
                'date' => $investment->investment_date,
                'description' => $investment->description ?: "Capital Investment #{$investment->id}",
            ]);

            $journal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $assetAccount->id, 'type' => 'debit', 'amount' => $investment->amount],
                ['chart_of_account_id' => $equityAccount->id, 'type' => 'credit', 'amount' => $investment->amount],
            ]);
        });

        return redirect()->route('accounting.capital-investments.index')
            ->with('success', 'Capital investment recorded successfully.');
    }
    /**
     * --- NEW METHOD ---
     * Show the form for editing the specified resource.
     */
    public function edit(CapitalInvestment $capitalInvestment)
    {
        $cashAccounts = ChartOfAccount::where('is_cash_account', true)->orderBy('name')->get();
        $equityAccounts = ChartOfAccount::where('type', 'equity')->orderBy('name')->get();
        return view('accounting.capital-investments.edit', compact('capitalInvestment', 'cashAccounts', 'equityAccounts'));
    }

    /**
     * --- NEW METHOD ---
     * Update the specified resource in storage.
     */
    public function update(Request $request, CapitalInvestment $capitalInvestment)
    {
        $validated = $request->validate([
            'investment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'asset_account_id' => 'required|exists:chart_of_accounts,id',
            'equity_account_id' => 'required|exists:chart_of_accounts,id',
            'description' => 'nullable|string|max:255',
        ]);

        $assetAccount = ChartOfAccount::find($validated['asset_account_id']);

        $equityAccount = ChartOfAccount::find($validated['equity_account_id']);


        DB::transaction(function () use ($capitalInvestment, $validated, $assetAccount, $equityAccount) {

            // 1. Delete the old journal entry
            $capitalInvestment->journalEntries()->delete();

            // 2. Update the investment record itself
            $capitalInvestment->update($validated);

            // 3. Create the new, corrected journal entry
            $journal = $capitalInvestment->journalEntries()->create([
                'date' => $capitalInvestment->investment_date,
                'description' => $capitalInvestment->description ?: "Capital Investment #{$capitalInvestment->id}",
            ]);

            $journal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $assetAccount->id, 'type' => 'debit', 'amount' => $capitalInvestment->amount],
                ['chart_of_account_id' => $equityAccount->id, 'type' => 'credit', 'amount' => $capitalInvestment->amount],
            ]);
        });

        return redirect()->route('accounting.capital-investments.index')
            ->with('success', 'Capital investment updated successfully.');
    }
    // A delete/reversal method would be implemented here, similar to the Expense controller.
    public function destroy(CapitalInvestment $capitalInvestment)
    {
        DB::transaction(function () use ($capitalInvestment) {
            // Delete associated journal entries first
            $capitalInvestment->journalEntries()->delete();
            // Then delete the investment record
            $capitalInvestment->delete();
        });

        return redirect()->route('accounting.capital-investments.index')
            ->with('success', 'Capital investment and its journal entry permanently deleted.');
    }
}
