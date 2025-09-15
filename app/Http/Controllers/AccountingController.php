<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger; // <-- CHANGE: We query this model now
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    public function index()
    {
        return view('accounting.index');
    }

    /**
     * Display the general ledger with filtering capabilities.
     */
    public function generalLedger(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // --- REFACTORED QUERY ---
        // Start the query on the parent GeneralLedger model.
        // Eager load the ledgerEntries and their associated account name.
        $query = GeneralLedger::with(['ledgerEntries.account']);

        // Apply date range filter directly on this table
        $query->whereBetween('date', [$startDate, $endDate]);

        // Apply optional filters by querying the relationships
        if ($request->filled('account_id')) {
            $query->whereHas('ledgerEntries', function ($q) use ($request) {
                $q->where('chart_of_account_id', $request->account_id);
            });
        }

        if ($request->filled('type')) {
            $query->whereHas('ledgerEntries', function ($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        // Order by date and then ID for consistent grouping, and paginate.
        $journals = $query->latest('date')->latest('id')->paginate(15)->withQueryString();

        $accounts = ChartOfAccount::orderBy('name')->get();

        return view('accounting.general-ledger', [
            'journals' => $journals, // <-- CHANGED: Pass journals instead of ledgerEntries
            'accounts' => $accounts,
            'filters' => $request->all(),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function trialBalance(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $accounts = ChartOfAccount::with(['ledgerEntries' => function ($query) use ($endDate) {
            $query->whereHas('generalLedger', fn($q) => $q->where('date', '<=', $endDate));
        }])->orderBy('name')->get();

        $trialBalanceData = [];
        foreach ($accounts as $account) {
            $totalDebits = $account->ledgerEntries->where('type', 'debit')->sum('amount');
            $totalCredits = $account->ledgerEntries->where('type', 'credit')->sum('amount');

            // Only process accounts that have had activity
            if ($totalDebits == 0 && $totalCredits == 0) {
                continue;
            }

            // --- THE CORRECTED LOGIC ---
            // 1. Calculate the final balance as Debits - Credits.
            $finalBalance = $totalDebits - $totalCredits;

            // 2. Determine where to place the balance.
            $debitBalance = 0;
            $creditBalance = 0;

            if ($finalBalance > 0) {
                // A positive result is a Debit Balance.
                $debitBalance = $finalBalance;
            } else {
                // A negative or zero result is a Credit Balance.
                // We show it as a positive number in the Credit column.
                $creditBalance = abs($finalBalance);
            }

            $trialBalanceData[] = (object)[
                'account_name' => $account->name,
                'final_balance_debit' => $debitBalance,
                'final_balance_credit' => $creditBalance,
            ];
        }

        return view('accounting.trial-balance', compact('trialBalanceData', 'endDate'));
    }
    /**
     * --- NEW METHOD ---
     * Display the Income Statement for a given period.
     */
    public function incomeStatement(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // --- Fetch all INCOME accounts ---
        $incomeAccounts = ChartOfAccount::where('type', 'income')
            ->with(['ledgerEntries' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('generalLedger', fn($q) => $q->whereBetween('date', [$startDate, $endDate]));
            }])->get();

        // --- Fetch all EXPENSE accounts ---
        $expenseAccounts = ChartOfAccount::where('type', 'expense')
            ->with(['ledgerEntries' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('generalLedger', fn($q) => $q->whereBetween('date', [$startDate, $endDate]));
            }])->get();

        // Calculate totals
        $totalIncome = 0;
        foreach ($incomeAccounts as $account) {
            $totalIncome += $account->ledgerEntries->where('type', 'credit')->sum('amount');
            $totalIncome -= $account->ledgerEntries->where('type', 'debit')->sum('amount'); // For reversals/refunds
        }

        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            $totalExpenses += $account->ledgerEntries->where('type', 'debit')->sum('amount');
            $totalExpenses -= $account->ledgerEntries->where('type', 'credit')->sum('amount');
        }

        $netProfit = $totalIncome - $totalExpenses;

        return view('accounting.income-statement', compact(
            'incomeAccounts', 'expenseAccounts', 'totalIncome',
            'totalExpenses', 'netProfit', 'startDate', 'endDate'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('end_date', Carbon::today()->toDateString());

        // --- 1. CALCULATE NET PROFIT / RETAINED EARNINGS ---

        // Fetch all income accounts with entries up to the specified date
        $incomeAccounts = ChartOfAccount::where('type', 'income')
            ->with(['ledgerEntries' => function ($query) use ($asOfDate) {
                $query->whereHas('generalLedger', fn($q) => $q->where('date', '<=', $asOfDate));
            }])->get();

        // Fetch all expense accounts with entries up to the specified date
        $expenseAccounts = ChartOfAccount::where('type', 'expense')
            ->with(['ledgerEntries' => function ($query) use ($asOfDate) {
                $query->whereHas('generalLedger', fn($q) => $q->where('date', '<=', $asOfDate));
            }])->get();

        // Calculate total income
        $totalIncome = 0;
        foreach ($incomeAccounts as $account) {
            $totalIncome += $account->ledgerEntries->where('type', 'credit')->sum('amount');
            $totalIncome -= $account->ledgerEntries->where('type', 'debit')->sum('amount');
        }

        // Calculate total expenses
        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            $totalExpenses += $account->ledgerEntries->where('type', 'debit')->sum('amount');
            $totalExpenses -= $account->ledgerEntries->where('type', 'credit')->sum('amount');
        }

        // This is our crucial Retained Earnings figure
        $netProfitOrLoss = $totalIncome - $totalExpenses;


        // --- 2. CALCULATE ASSET, LIABILITY, AND EQUITY BALANCES (as before) ---
        $calculateBalances = function ($type) use ($asOfDate) {
            return ChartOfAccount::where('type', $type)
                ->with(['ledgerEntries' => function ($query) use ($asOfDate) {
                    $query->whereHas('generalLedger', fn($q) => $q->where('date', '<=', $asOfDate));
                }])->get();
        };

        $assets = $calculateBalances('asset');
        $liabilities = $calculateBalances('liability');
        $equityAccounts = $calculateBalances('equity'); // Renamed for clarity

        return view('accounting.balance-sheet', compact(
            'assets', 'liabilities', 'equityAccounts',
            'netProfitOrLoss', // <-- Pass the new variable to the view
            'asOfDate'
        ));
    }
}
