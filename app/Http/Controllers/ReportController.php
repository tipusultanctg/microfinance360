<?php

namespace App\Http\Controllers;

use App\Models\LoanAccount;
use App\Models\LoanRepayment;
use App\Models\LoanRepaymentSchedule;
use App\Models\SavingsTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    /**
     * Display the daily collection sheet for field officers.
     */
    public function collectionSheet()
    {
        // Get today's date
        $today = Carbon::today();

        // Find all loan repayment installments that are pending and have a due date
        // on or before today. We use eager loading to prevent N+1 query issues.
        // The BelongsToTenant scope will automatically filter these for the current organization.
        $dueInstallments = LoanRepaymentSchedule::with(['loanAccount.member'])
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', $today)
            ->orderBy('due_date', 'asc')
            ->get();

        return view('reports.collection-sheet', compact('dueInstallments', 'today'));
    }

    public function disbursementReport(Request $request)
    {
        // Set default date range to the current month if not provided
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Query loan accounts disbursed within the date range
        // The BelongsToTenant scope will automatically filter these.
        $disbursements = LoanAccount::with(['member', 'loanProduct'])
            ->whereBetween('disbursement_date', [$startDate, $endDate])
            ->latest('disbursement_date')
            ->get();

        return view('reports.disbursement-report', compact('disbursements', 'startDate', 'endDate'));
    }

    public function collectionReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $userId = $request->input('user_id');

        // Query for Loan Repayments
        $loanRepaymentsQuery = LoanRepayment::with(['loanAccount.member', 'user'])
            ->whereBetween('payment_date', [$startDate, $endDate]);

        // Query for Savings Deposits
        $savingsDepositsQuery = SavingsTransaction::with(['savingsAccount.member', 'user'])
            ->where('type', 'deposit')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        // Apply optional user filter
        if ($userId) {
            $loanRepaymentsQuery->where('user_id', $userId);
            $savingsDepositsQuery->where('user_id', $userId);
        }

        $loanRepayments = $loanRepaymentsQuery->get();
        $savingsDeposits = $savingsDepositsQuery->get();

        // --- Combine and format the results ---
        $collections = new Collection();

        foreach($loanRepayments as $repayment) {
            $collections->push((object)[
                'date' => $repayment->payment_date,
                'type' => 'Loan Repayment',
                'member_name' => $repayment->loanAccount->member->name ?? 'N/A',
                'account_number' => $repayment->loanAccount->account_number ?? 'N/A',
                'amount' => $repayment->amount,
                'collected_by' => $repayment->user->name ?? 'N/A',
            ]);
        }

        foreach($savingsDeposits as $deposit) {
            $collections->push((object)[
                'date' => $deposit->transaction_date,
                'type' => 'Savings Deposit',
                'member_name' => $deposit->savingsAccount->member->name ?? 'N/A',
                'account_number' => $deposit->savingsAccount->account_number ?? 'N/A',
                'amount' => $deposit->amount,
                'collected_by' => $deposit->user->name ?? 'N/A',
            ]);
        }

        // Sort the combined collection by date
        $sortedCollections = $collections->sortBy('date');

        // Get a list of staff for the filter dropdown
        $staff = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Field Officer', 'Branch Manager', 'Organization Admin']);
        })->orderBy('name')->get();

        return view('reports.collection-report', [
            'collections' => $sortedCollections,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'staff' => $staff,
            'selectedStaff' => $userId,
        ]);
    }

    public function portfolioAtRisk(Request $request)
    {
        $asOfDate = $request->input('as_of_date', Carbon::today()->toDateString());
        $asOfDate = Carbon::parse($asOfDate);

        // Get all loan accounts that were active on or before the "as of" date
        // and are not yet fully paid.
        $activeLoans = LoanAccount::with(['schedule' => function ($query) use ($asOfDate) {
            // We only need the first overdue installment to determine the risk
            $query->where('status', 'pending')
                ->whereDate('due_date', '<=', $asOfDate)
                ->orderBy('due_date', 'asc');
        }])
            ->where('status', 'active')
            ->whereDate('disbursement_date', '<=', $asOfDate)
            ->get();

        // Get the total loan portfolio (sum of all outstanding balances)
        $totalPortfolio = $activeLoans->sum('balance');

        // Initialize PAR buckets
        $parBuckets = [
            '1-30' => ['count' => 0, 'amount' => 0],
            '31-60' => ['count' => 0, 'amount' => 0],
            '61-90' => ['count' => 0, 'amount' => 0],
            '90+' => ['count' => 0, 'amount' => 0],
        ];

        // This collection will hold the loans that are actually at risk
        $loansAtRisk = new Collection();

        foreach ($activeLoans as $loan) {
            // Check if the loan has any overdue installments
            $firstOverdueInstallment = $loan->schedule->first();

            if ($firstOverdueInstallment) {
                $daysOverdue = $asOfDate->diffInDays($firstOverdueInstallment->due_date);

                // Categorize the loan into a PAR bucket based on days overdue
                if ($daysOverdue >= 1 && $daysOverdue <= 30) {
                    $bucket = '1-30';
                } elseif ($daysOverdue >= 31 && $daysOverdue <= 60) {
                    $bucket = '31-60';
                } elseif ($daysOverdue >= 61 && $daysOverdue <= 90) {
                    $bucket = '61-90';
                } else {
                    $bucket = '90+';
                }

                // Add the entire outstanding balance to the bucket
                $parBuckets[$bucket]['count']++;
                $parBuckets[$bucket]['amount'] += $loan->balance;

                // Add the loan to our list for detailed display
                $loan->days_overdue = $daysOverdue;
                $loansAtRisk->push($loan);
            }
        }

        return view('reports.par-report', compact(
            'asOfDate',
            'totalPortfolio',
            'parBuckets',
            'loansAtRisk'
        ));
    }
}
