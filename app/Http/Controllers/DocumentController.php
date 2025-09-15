<?php

namespace App\Http\Controllers;

use App\Models\LoanAccount;
use App\Models\SavingsAccount;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Generate and stream a Loan Agreement PDF.
     */
    public function generateLoanAgreement(LoanAccount $loanAccount)
    {
        // Eager load all necessary data to pass to the view
        $loanAccount->load(['member', 'loanProduct', 'schedule']);
        $tenant = auth()->user()->tenant;

        // Data to pass to the Blade view
        $data = [
            'loanAccount' => $loanAccount,
            'tenant' => $tenant,
        ];

        // Load the view and pass in the data
        $pdf = Pdf::loadView('documents.loan-agreement', $data);

        // (Optional) Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Create a filename
        $fileName = 'Loan-Agreement-' . $loanAccount->account_number . '.pdf';

        // Stream the PDF to the browser for download
        return $pdf->stream($fileName);
    }


    /**
     * --- NEW METHOD ---
     * Generate and stream a No-Dues Certificate PDF.
     */
    public function generateNoDuesCertificate(LoanAccount $loanAccount)
    {
        // Security check: Only allow for fully paid loans
        if (!in_array($loanAccount->status, ['paid', 'closed'])) {
            abort(403, 'A No-Dues Certificate can only be generated for fully paid loans.');
        }

        $loanAccount->load('member');
        $tenant = auth()->user()->tenant;

        $data = [
            'loanAccount' => $loanAccount,
            'tenant' => $tenant,
        ];

        $pdf = Pdf::loadView('documents.no-dues-certificate', $data);
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'No-Dues-Certificate-' . $loanAccount->account_number . '.pdf';
        return $pdf->stream($fileName);
    }

    /**
     * --- NEW METHOD ---
     * Generate and stream a Savings Account Statement PDF.
     */
    public function generateSavingsStatement(SavingsAccount $savingsAccount, Request $request)
    {
        // Load the member and product relationships first
        $savingsAccount->load(['member', 'savingsProduct']);
        $tenant = auth()->user()->tenant;

        // Set the date range for the statement
        $startDate = $request->input('start_date', $savingsAccount->created_at->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // --- THE CRITICAL FIX ---
        // Query the transactions relationship directly with the date filter,
        // ensuring we get a fresh, filtered result set.
        $transactions = $savingsAccount->transactions()
            ->with('user') // Eager load the user for the transaction
            ->whereBetween('transaction_date', [$startDate, Carbon::parse($endDate)->endOfDay()]) // Use endOfDay() to include all of today
            ->get();

        $data = [
            'savingsAccount' => $savingsAccount,
            'tenant' => $tenant,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'transactions' => $transactions, // Pass the correctly filtered transactions
        ];

        $pdf = Pdf::loadView('documents.savings-statement', $data);
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'Savings-Statement-' . $savingsAccount->account_number . '.pdf';
        return $pdf->stream($fileName);
    }
}
