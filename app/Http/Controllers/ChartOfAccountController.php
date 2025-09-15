<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // The BelongsToTenant scope automatically filters accounts.
        $accounts = ChartOfAccount::orderBy('name')->paginate(15);
        return view('accounting.chart-of-accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.chart-of-accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Ensure the name is unique for the current tenant
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id);
                }),
            ],
            'type' => 'required|string|in:asset,liability,equity,income,expense',
        ]);

        ChartOfAccount::create($validated);

        return redirect()->route('accounting.chart-of-accounts.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount, Request $request)
    {
        // Eager load the necessary relationships for the entries
        $query = $chartOfAccount->ledgerEntries()->with('generalLedger')
            ->latest('id');

        // Optional: Add date filtering to the account ledger view
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereHas('generalLedger', function ($q) use ($request) {
                $q->whereBetween('date', [$request->start_date, $request->end_date]);
            });
        }

        $ledgerEntries = $query->paginate(25)->withQueryString();

        return view('accounting.chart-of-accounts.show', compact('chartOfAccount', 'ledgerEntries'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $chartOfAccount)
    {
        return view('accounting.chart-of-accounts.edit', compact('chartOfAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        // Prevent editing of locked core accounts
        if ($chartOfAccount->is_locked) {
            return redirect()->route('accounting.chart-of-accounts.index')
                ->with('error', 'Cannot edit a locked system account.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id);
                })->ignore($chartOfAccount->id),
            ],
            'type' => 'required|string|in:asset,liability,equity,income,expense',
        ]);

        $chartOfAccount->update($validated);

        return redirect()->route('accounting.chart-of-accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        // Prevent deletion of locked core accounts
        if ($chartOfAccount->is_locked) {
            return redirect()->route('accounting.chart-of-accounts.index')
                ->with('error', 'Cannot delete a locked system account.');
        }

        // Add a check to prevent deletion if the account has entries
        // In a real system, you might archive it instead.
        if ($chartOfAccount->ledgerEntries()->exists()) {
            return redirect()->route('accounting.chart-of-accounts.index')
                ->with('error', 'Cannot delete an account that has ledger entries. Please move entries to another account first.');
        }

        $chartOfAccount->delete();

        return redirect()->route('accounting.chart-of-accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
