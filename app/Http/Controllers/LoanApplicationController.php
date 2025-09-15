<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Models\Member;
use Illuminate\Http\Request;

class LoanApplicationController extends Controller
{
    public function index()
    {
        $applications = LoanApplication::with(['member', 'loanProduct'])->latest()->paginate(15);
        return view('loan-applications.index', compact('applications'));
    }

    public function create()
    {
        $members = Member::orderBy('name')->get();
        $products = LoanProduct::orderBy('name')->get();
        return view('loan-applications.create', compact('members', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'requested_amount' => 'required|numeric|min:1',
            'requested_term' => 'required|integer|min:1',
            'purpose' => 'nullable|string',
            'documents.*' => 'nullable|file|max:10240',
        ]);

        $application = LoanApplication::create($validated);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $application->addMedia($file)->toMediaCollection('loan_documents');
            }
        }

        return redirect()->route('loan-applications.index')
            ->with('success', 'Loan application submitted successfully.');
    }

    public function show(LoanApplication $loanApplication)
    {
        // Eager load media to display on the show page
        $loanApplication->load(['member', 'loanProduct', 'approvedBy', 'media']);
        return view('loan-applications.show', compact('loanApplication'));
    }

    public function edit(LoanApplication $loanApplication)
    {
        // Only allow editing if the application is still pending
        if ($loanApplication->status !== 'pending') {
            return redirect()->route('loan-applications.show', $loanApplication->id)
                ->with('error', 'Cannot edit an application that has already been processed.');
        }
        $members = Member::orderBy('name')->get();
        $products = LoanProduct::orderBy('name')->get();
        return view('loan-applications.edit', compact('loanApplication', 'members', 'products'));
    }

    public function update(Request $request, LoanApplication $loanApplication)
    {
        if ($loanApplication->status !== 'pending') {
            return redirect()->route('loan-applications.show', $loanApplication->id)
                ->with('error', 'Cannot edit an application that has already been processed.');
        }

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'requested_amount' => 'required|numeric|min:1',
            'requested_term' => 'required|integer|min:1',
            'purpose' => 'nullable|string',
            'documents.*' => 'nullable|file|max:10240',
        ]);

        $loanApplication->update($validated);
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $loanApplication->addMedia($file)->toMediaCollection('loan_documents');
            }
        }

        return redirect()->route('loan-applications.index')
            ->with('success', 'Loan application updated successfully.');
    }

    public function destroy(LoanApplication $loanApplication)
    {
        if ($loanApplication->status !== 'pending' && $loanApplication->status !== 'rejected') {
            return redirect()->route('loan-applications.index')
                ->with('error', 'Cannot delete an application that has been approved or disbursed.');
        }
        $loanApplication->delete();
        return redirect()->route('loan-applications.index')
            ->with('success', 'Loan application deleted successfully.');
    }

    // --- Custom Action Methods ---

    public function approve(LoanApplication $loanApplication)
    {
        // Add Authorization logic here, e.g., Gate::authorize('approve-loan');
        if ($loanApplication->status !== 'pending') {
            return redirect()->back()->with('error', 'This application has already been processed.');
        }

        $loanApplication->update([
            'status' => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('loan-applications.show', $loanApplication->id)
            ->with('success', 'Loan application has been approved.');
    }

    public function reject(LoanApplication $loanApplication)
    {
        // Add Authorization logic here
        if ($loanApplication->status !== 'pending') {
            return redirect()->back()->with('error', 'This application has already been processed.');
        }

        $loanApplication->update(['status' => 'rejected']);

        return redirect()->route('loan-applications.show', $loanApplication->id)
            ->with('success', 'Loan application has been rejected.');
    }
}
