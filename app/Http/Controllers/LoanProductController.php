<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use Illuminate\Http\Request;

class LoanProductController extends Controller
{
    public function index()
    {
        $products = LoanProduct::latest()->paginate(10);
        return view('loan-products.index', compact('products'));
    }

    public function create()
    {
        return view('loan-products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0',
            'interest_method' => 'required|string|in:flat,reducing',
            'repayment_frequency' => 'required|string|in:daily,weekly,monthly',
            'max_loan_term' => 'required|integer|min:1',
            'late_payment_fee' => 'required|numeric|min:0',
            'max_loan_amount' => ['nullable', 'numeric', 'min:0',],
            'processing_fee_type' => 'nullable|string|in:fixed,percentage', // <-- ADD THIS
            'processing_fee_value' => 'nullable|numeric|min:0|required_with:processing_fee_type',
        ]);

        LoanProduct::create($validated);

        return redirect()->route('organization.loan-products.index')
            ->with('success', 'Loan product created successfully.');
    }

    public function edit(LoanProduct $loanProduct)
    {
        return view('loan-products.edit', compact('loanProduct'));
    }

    public function update(Request $request, LoanProduct $loanProduct)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0',
            'interest_method' => 'required|string|in:flat,reducing',
            'repayment_frequency' => 'required|string|in:daily,weekly,monthly',
            'max_loan_term' => 'required|integer|min:1',
            'late_payment_fee' => 'required|numeric|min:0',
            'max_loan_amount' => ['nullable', 'numeric', 'min:0'],
            'processing_fee_type' => 'nullable|string|in:fixed,percentage', // <-- ADD THIS
            'processing_fee_value' => 'nullable|numeric|min:0|required_with:processing_fee_type',
        ]);

        $loanProduct->update($validated);

        return redirect()->route('organization.loan-products.index')
            ->with('success', 'Loan product updated successfully.');
    }

    public function destroy(LoanProduct $loanProduct)
    {
        // Add logic here to prevent deletion if product is in use
        // For now, we will allow deletion.
        $loanProduct->delete();

        return redirect()->route('organization.loan-products.index')
            ->with('success', 'Loan product deleted successfully.');
    }
}
