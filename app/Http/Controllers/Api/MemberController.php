<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\SavingsTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Store a newly created member from the API.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'phone' => 'nullable|string|max:20',
            'present_address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'photo' => 'nullable|image|max:2048', // 2MB Max
            'kyc_documents' => 'nullable|array',
            'kyc_documents.*' => 'image|max:5120', // 5MB Max per document
        ]);

        // --- Authorization: Ensure the selected branch belongs to the user's tenant ---
        $branch = Branch::find($validated['branch_id']);
        if ($branch->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized action. The selected branch is invalid.');
        }

        // Generate a unique member ID
        $validated['member_uid'] = 'M-' . $request->user()->tenant_id . '-' . time();

        $member = Member::create($validated);

        // --- Handle File Uploads ---
        if ($request->hasFile('photo')) {
            $member->addMediaFromRequest('photo')->toMediaCollection('member_photo');
        }

        if ($request->hasFile('kyc_documents')) {
            foreach ($request->file('kyc_documents') as $file) {
                $member->addMedia($file)->toMediaCollection('kyc_documents');
            }
        }

        // Return a more detailed response for the app to use
        return response()->json([
            'message' => 'Member registered successfully.',
            'member' => [
                'id' => $member->id,
                'member_uid' => $member->member_uid,
                'name' => $member->name,
                'photo_url' => $member->getFirstMediaUrl('member_photo', 'thumb'),
            ]
        ], 201); // 201 Created
    }

    /**
     * A helper endpoint to get a list of branches for the registration form dropdown.
     */
    public function getBranches(Request $request)
    {
        // The BelongsToTenant scope is automatically applied to the User model,
        // so this will only fetch branches for the current tenant.
        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return response()->json($branches);
    }

    public function getMemberData(Request $request, Member $member)
    {
        // --- AUTHORIZATION CHECK ---
        // Ensure the requested member belongs to the same tenant as the authenticated user.
        if ($request->user()->tenant_id !== $member->tenant_id) {
            abort(403, 'You are not authorized to view this member.');
        }

        // Eager load for efficiency
        $member->load(['savingsAccounts', 'loanAccounts']);

        // Get Account Details
        $savingsAccounts = $member->savingsAccounts()->where('status', 'active')->get(['id', 'account_number', 'balance']);
        $loanAccounts = $member->loanAccounts()->where('status', 'active')->get(['id', 'account_number', 'balance']);

        // Get Snapshot Details
        $totalSavings = $member->savingsAccounts->sum('balance');
        $totalLoanDue = $member->loanAccounts->sum('balance');

        $lastSavingsTxn = SavingsTransaction::whereIn('savings_account_id', $member->savingsAccounts->pluck('id'))
            ->latest('transaction_date')->value('transaction_date');

        $lastLoanTxn = LoanRepayment::whereIn('loan_account_id', $member->loanAccounts->pluck('id'))
            ->latest('payment_date')->value('payment_date');

        $lastTransactionDate = null;
        if ($lastSavingsTxn && $lastLoanTxn) {
            $lastTransactionDate = max($lastSavingsTxn, $lastLoanTxn);
        } else {
            $lastTransactionDate = $lastSavingsTxn ?? $lastLoanTxn;
        }

        // Combine into a single JSON response
        return response()->json([
            'snapshot' => [
                'name' => $member->name,
                'phone' => $member->phone,
                'address' => $member->address,
                'image_url' => $member->getFirstMediaUrl('member_photo', 'thumb') ?: 'https://placehold.co/100',
                'total_savings' => number_format($totalSavings, 2),
                'total_loan_due' => number_format($totalLoanDue, 2),
                'last_transaction_date' => $lastTransactionDate ? Carbon::parse($lastTransactionDate)->format('d M, Y') : 'N/A',
            ],
            'accounts' => [
                'savings' => $savingsAccounts,
                'loans' => $loanAccounts,
            ]
        ]);
    }

}
