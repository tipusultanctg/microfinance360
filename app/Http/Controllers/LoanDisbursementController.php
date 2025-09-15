<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\LoanApplication;
use App\Models\LoanAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanDisbursementController extends Controller
{

    /**
     * Show the form for creating a new resource.
     */
    public function create(LoanApplication $loanApplication)
    {
        // Ensure the application is approved before showing the form
        if ($loanApplication->status !== 'approved') {
            return redirect()->route('loan-applications.show', $loanApplication->id)
                ->with('error', 'Loan must be approved before it can be disbursed.');
        }
        $cashAccounts = ChartOfAccount::where('is_cash_account', true)->orderBy('name')->get();
        return view('loan-disbursement.create', compact('loanApplication', 'cashAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, LoanApplication $loanApplication)
    {
        if ($loanApplication->status !== 'approved') {
            return redirect()->route('loan-applications.show', $loanApplication->id)
                ->with('error', 'Loan must be approved before it can be disbursed.');
        }

        $validated = $request->validate([
            'disbursement_date' => 'required|date',
            'processing_fee' => 'nullable|numeric|min:0',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $loanProduct = $loanApplication->loanProduct;
        $principal = (float) $loanApplication->requested_amount;
        $processingFee = (float) ($validated['processing_fee'] ?? 0.00);
        $term = (int) $loanApplication->requested_term;
        $annualInterestRate = (float) $loanProduct->interest_rate;

        // Use a transaction to ensure all database operations are atomic
        DB::transaction(function () use ($loanApplication, $request, $loanProduct, $principal, $processingFee, $term, $annualInterestRate) {

            // --- 1. FETCH REQUIRED ACCOUNTS FROM CHART OF ACCOUNTS ---
            $cashAccount = ChartOfAccount::find($request['cash_account_id']);
            $feeIncomeAccount = ChartOfAccount::where('name', 'Fee Income')->firstOrFail();
            $loansReceivableAccount = ChartOfAccount::where('name', 'Loans Receivable')->firstOrFail();

            // --- 2. HANDLE THE FEE TRANSACTION (if any) ---
            if ($processingFee > 0) {
                // Create the Journal Entry for the fee
                $feeJournal = $loanApplication->journalEntries()->create([
                    'date' => now()->toDateString(),
                    'description' => "Processing Fee for Loan Application #{$loanApplication->id}",
                ]);
                // Create the Line Items (Ledger Entries)
                $feeJournal->ledgerEntries()->createMany([
                    ['chart_of_account_id' => $cashAccount->id, 'type' => 'debit', 'amount' => $processingFee],
                    ['chart_of_account_id' => $feeIncomeAccount->id, 'type' => 'credit', 'amount' => $processingFee],
                ]);
            }

            // --- 3. CALCULATE TOTALS & CREATE LOAN ACCOUNT ---
            $totalInterest = 0;
            if ($loanProduct->interest_method === 'flat') {
                $termInYears = $term / 12;
                $totalInterest = round($principal * ($annualInterestRate / 100) * $termInYears, 2);
            } else { // 'reducing' balance interest
                $monthlyRate = ($annualInterestRate / 100) / 12;
                if ($monthlyRate > 0) {
                    $monthlyInstallment = ($principal * $monthlyRate * pow(1 + $monthlyRate, $term)) / (pow(1 + $monthlyRate, $term) - 1);
                    $totalPayable = round($monthlyInstallment * $term, 2);
                    $totalInterest = $totalPayable - $principal;
                } else {
                    // Handle 0% interest case for reducing balance
                    $totalInterest = 0;
                }
            }
            $totalPayable = $principal + $totalInterest;

            $loanAccount = LoanAccount::create([
                'account_number' => 'L-' . $loanApplication->tenant_id . '-' . time(),
                'loan_application_id' => $loanApplication->id,
                'member_id' => $loanApplication->member_id,
                'loan_product_id' => $loanApplication->loan_product_id,
                'principal_amount' => $principal,
                'processing_fee' => $processingFee,
                'total_interest' => $totalInterest,
                'total_payable' => $totalPayable,
                'balance' => $totalPayable,
                'term' => $term,
                'disbursement_date' => $request->disbursement_date,
                'status' => 'active',
            ]);

            // Post the disbursement to the General Ledger
            $disbursementJournal = $loanAccount->journalEntries()->create([
                'date' => $loanAccount->disbursement_date,
                'description' => "Loan Disbursement for Account #{$loanAccount->account_number}",
            ]);
            $disbursementJournal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $loansReceivableAccount->id, 'type' => 'debit', 'amount' => $principal],
                ['chart_of_account_id' => $cashAccount->id, 'type' => 'credit', 'amount' => $principal],
            ]);

            // --- 4. GENERATE AND STORE THE REPAYMENT SCHEDULE ---
            $currentDueDate = Carbon::parse($request->disbursement_date);
            $outstandingPrincipal = $principal;

            for ($i = 1; $i <= $term; $i++) {
                $interestForInstallment = 0;
                $principalForInstallment = 0;

                if ($loanProduct->interest_method === 'flat') {
                    $interestForInstallment = $totalInterest / $term;
                    $principalForInstallment = $principal / $term;
                } else {
                    $monthlyRate = ($annualInterestRate / 100) / 12;
                    if ($monthlyRate > 0) {
                        $interestForInstallment = $outstandingPrincipal * $monthlyRate;
                        $monthlyInstallment = ($principal * $monthlyRate * pow(1 + $monthlyRate, $term)) / (pow(1 + $monthlyRate, $term) - 1);
                        $principalForInstallment = $monthlyInstallment - $interestForInstallment;
                        $outstandingPrincipal -= $principalForInstallment;
                    } else {
                        // Handle 0% interest case
                        $interestForInstallment = 0;
                        $principalForInstallment = $principal / $term;
                    }
                }

                switch ($loanProduct->repayment_frequency) {
                    case 'daily':   $currentDueDate->addDay();   break;
                    case 'weekly':  $currentDueDate->addWeek();  break;
                    default:        $currentDueDate->addMonth(); break;
                }

                $totalForInstallment = $principalForInstallment + $interestForInstallment;

                // Final installment adjustment to correct for rounding errors
                if ($i == $term) {
                    $sumOfPrincipal = $loanAccount->schedule()->sum('principal_amount');
                    $sumOfInterest = $loanAccount->schedule()->sum('interest_amount');
                    $principalForInstallment = $principal - $sumOfPrincipal;
                    $interestForInstallment = $totalInterest - $sumOfInterest;
                    $totalForInstallment = $principalForInstallment + $interestForInstallment;
                }

                $loanAccount->schedule()->create([
                    'due_date' => $currentDueDate,
                    'principal_amount' => $principalForInstallment,
                    'interest_amount' => $interestForInstallment,
                    'total_amount' => $totalForInstallment,
                ]);
            }

            // --- 5. UPDATE APPLICATION STATUS ---
            $loanApplication->update(['status' => 'disbursed']);

        }); // End of transaction

        return redirect()->route('loan-applications.index')
            ->with('success', 'Loan has been disbursed successfully and the schedule has been generated.');
    }
}
