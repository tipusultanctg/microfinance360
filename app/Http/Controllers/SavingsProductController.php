<?php

namespace App\Http\Controllers;

use App\Models\SavingsProduct;
use Illuminate\Http\Request;

class SavingsProductController extends Controller
{
    public function index()
    {
        // The BelongsToTenant scope will automatically filter these.
        $products = SavingsProduct::latest()->paginate(10);
        return view('savings-products.index', compact('products'));
    }

    public function create()
    {
        return view('savings-products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'interest_posting_frequency' => 'required|string|in:monthly,quarterly,semi_annually,annually',
            'min_balance_for_interest' => 'required|numeric|min:0',
        ]);

        SavingsProduct::create($validated);

        return redirect()->route('organization.savings-products.index')
            ->with('success', 'Savings product created successfully.');
    }

    public function show(SavingsProduct $savingsProduct)
    {
        return view('savings-products.show', compact('savingsProduct'));
    }

    public function edit(SavingsProduct $savingsProduct)
    {
        return view('savings-products.edit', compact('savingsProduct'));
    }

    public function update(Request $request, SavingsProduct $savingsProduct)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'interest_posting_frequency' => 'required|string|in:monthly,quarterly,semi_annually,annually',
            'min_balance_for_interest' => 'required|numeric|min:0',
        ]);

        $savingsProduct->update($validated);

        return redirect()->route('organization.savings-products.index')
            ->with('success', 'Savings product updated successfully.');
    }

    public function destroy(SavingsProduct $savingsProduct)
    {
        // Note: Add logic here to prevent deletion if the product is linked to active savings accounts.
        // For now, we will allow direct deletion.
        $savingsProduct->delete();

        return redirect()->route('organization.savings-products.index')
            ->with('success', 'Savings product deleted successfully.');
    }
}
