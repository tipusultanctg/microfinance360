<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\LoanAccount;
use Illuminate\Http\Request;

class LoanAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /*public function index()
    {
        $accounts = LoanAccount::with(['member', 'loanProduct'])
            ->where('status', 'active') // Only show active loans by default
            ->latest('disbursement_date')
            ->paginate(15);
        return view('loan-accounts.index', compact('accounts'));
    }*/

    public function index(Request $request)
    {

        $query = LoanAccount::with(['member', 'loanProduct'])->latest('disbursement_date');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('account_number', 'like', "%{$searchTerm}%")
                    ->orWhereHas('member', function($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $accounts = $query->paginate(15)->withQueryString();

        return view('loan-accounts.index', compact('accounts'));
    }

    /**
     * Display the specified resource.
     */
    public function show(LoanAccount $loanAccount)
    {
        // Eager load all related data for the detail view
        $loanAccount->load(['member', 'loanProduct', 'schedule', 'repayments.user']);
        $cashAccounts = ChartOfAccount::where('is_cash_account', true)->orderBy('name')->get();

        return view('loan-accounts.show', compact('loanAccount','cashAccounts'));
    }

    public function destroy(LoanAccount $loanAccount)
    {
        // Use a policy or a direct role check for authorization
        if (!auth()->user()->hasRole('Organization Admin')) {
            abort(403, 'You are not authorized to perform this action.');
        }

        // The loanApplication status can be reverted to 'approved' to allow for re-disbursement if needed.
        // This is optional but good practice.
        if ($loanAccount->loanApplication) {
            $loanAccount->loanApplication->update(['status' => 'approved']);
        }

        // Because we used cascadeOnDelete() in our migrations for `loan_repayment_schedules`
        // and `loan_repayments`, the database will automatically delete all child records
        // associated with this loan account.
        $loanAccount->delete();

        return redirect()->route('loan-accounts.index')
            ->with('success', 'Loan account and all its repayment data have been permanently deleted.');
    }
}
