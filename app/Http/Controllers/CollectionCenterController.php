<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\LoanAccount;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CollectionCenterController extends Controller
{
    // We will use other controllers' logic by instantiating them.
    // In a larger app, this logic would be moved to reusable Service classes.
    protected $savingsTxnController;
    protected $loanRepaymentController;

    public function __construct()
    {
        $this->savingsTxnController = new SavingsTransactionController();
        $this->loanRepaymentController = new LoanRepaymentController();
    }

    /**
     * Display the collection center page.
     */
    public function index()
    {
        $members = Member::orderBy('name')->get();
        $cashAccounts = ChartOfAccount::where('is_cash_account', true)->orderBy('name')->get();

        // --- REWRITTEN QUERY SECTION ---
        $today = Carbon::today();

        // Get all savings transactions for today (deposits and withdrawals)
        $todaysSavingsTransactions = SavingsTransaction::with(['savingsAccount.member', 'user'])
            ->whereDate('transaction_date', $today)
            ->latest()
            ->get();

        // Get all loan repayments for today
        $todaysLoanRepayments = LoanRepayment::with(['loanAccount.member', 'user'])
            ->whereDate('payment_date', $today)
            ->latest()
            ->get();

        return view('collection-center.index', compact(
            'members',
            'cashAccounts',
            'todaysSavingsTransactions',
            'todaysLoanRepayments',
        ));
    }

    /**
     * Fetch all active savings and loan accounts for a given member.
     */
    public function getMemberData(Member $member)
    {
        // Eager load for efficiency
        $member->load(['savingsAccounts', 'loanAccounts']);

        // --- Get Account Details ---
        $savingsAccounts = $member->savingsAccounts()->where('status', 'active')->get(['id', 'account_number', 'balance']);
        $loanAccounts = $member->loanAccounts()->where('status', 'active')->get(['id', 'account_number', 'balance']);

        // --- Get Snapshot Details ---
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

        // --- Combine into a single JSON response ---
        return response()->json([
            // Snapshot Data
            'snapshot' => [
                'name' => $member->name,
                'phone' => $member->phone,
                'address' => $member->address,
                'image_url' => $member->getFirstMediaUrl('member_photo', 'thumb') ?: 'https://placehold.co/100',
                'total_savings' => number_format($totalSavings, 2),
                'total_loan_due' => number_format($totalLoanDue, 2),
                'last_transaction_date' => $lastTransactionDate ? Carbon::parse($lastTransactionDate)->format('d M, Y') : 'N/A',
            ],
            // Account Data
            'accounts' => [
                'savings' => $savingsAccounts,
                'loans' => $loanAccounts,
            ]
        ]);
    }

    /**
     * Store one or more transactions from the unified form.
     */
    /* public function store(Request $request)
     {
         $validated = $request->validate([
             'member_id' => 'required|exists:members,id',
             'cash_account_id' => 'required|exists:chart_of_accounts,id',
             'date' => 'required|date',
             'note' => 'nullable|string',

             // --- IMPROVED SAVINGS VALIDATION ---
             'savings_account_id' => 'nullable|exists:savings_accounts,id',
             // Deposit is required IF a savings account is selected AND withdrawal is empty.
             'savings_deposit_amount' => [
                 'nullable', 'numeric', 'min:0',
                 Rule::requiredIf(function () use ($request) {
                     return !empty($request->savings_account_id) && empty($request->savings_withdrawal_amount);
                 }),
             ],
             // Withdrawal has the same logic, but we can simplify and just check both are not empty below.
             'savings_withdrawal_amount' => 'nullable|numeric|min:0',

             // --- IMPROVED LOAN VALIDATION ---
             'loan_account_id' => 'nullable|exists:loan_accounts,id',
             'loan_repayment_amount' => 'nullable|numeric|min:0.01|required_with:loan_account_id',

             // --- NEW CUSTOM VALIDATION RULE ---
             // We'll add a custom check to ensure at least one transaction is entered.
         ]);

         // --- Custom validation logic after initial checks ---
         $hasSavingsDeposit = !empty($validated['savings_deposit_amount']) && $validated['savings_deposit_amount'] > 0;
         $hasSavingsWithdrawal = !empty($validated['savings_withdrawal_amount']) && $validated['savings_withdrawal_amount'] > 0;
         $hasLoanRepayment = !empty($validated['loan_repayment_amount']) && $validated['loan_repayment_amount'] > 0;

         // If a savings account is selected, at least one of the savings amounts must be greater than 0.
         if (!empty($validated['savings_account_id']) && !$hasSavingsDeposit && !$hasSavingsWithdrawal) {
             throw ValidationException::withMessages([
                 'savings_deposit_amount' => 'Please enter a deposit or withdrawal amount for the selected savings account.',
             ]);
         }

         // If both savings deposit AND withdrawal are entered, throw an error.
         if ($hasSavingsDeposit && $hasSavingsWithdrawal) {
             throw ValidationException::withMessages([
                 'savings_deposit_amount' => 'You can only process a deposit or a withdrawal at one time, not both.',
             ]);
         }

         // Ensure at least one transaction exists overall
         if (!$hasSavingsDeposit && !$hasSavingsWithdrawal && !$hasLoanRepayment) {
             throw ValidationException::withMessages([
                 'savings_deposit_amount' => 'You must enter at least one transaction amount to proceed.',
             ]);
         }

         try {
             DB::transaction(function () use ($validated, $request) {
                 // Process Savings Deposit
                 if (!empty($validated['savings_deposit_amount']) && $validated['savings_deposit_amount'] > 0) {
                     $savingsAccount = SavingsAccount::find($validated['savings_account_id']);

                     $depositRequest = new Request([
                         'amount' => $validated['savings_deposit_amount'],
                         'description' => $validated['note'],
                         'cash_account_id' => $validated['cash_account_id']
                     ]);
                     $this->savingsTxnController->storeDeposit($depositRequest, $savingsAccount);
                 }

                 // Process Savings Withdrawal
                 if (!empty($validated['savings_withdrawal_amount']) && $validated['savings_withdrawal_amount'] > 0) {
                     $savingsAccount = SavingsAccount::find($validated['savings_account_id']);
                     $withdrawalRequest = new Request([
                         'amount' => $validated['savings_withdrawal_amount'],
                         'description' => $validated['note'],
                         'cash_account_id' => $validated['cash_account_id']
                     ]);
                     $this->savingsTxnController->storeWithdrawal($withdrawalRequest, $savingsAccount);
                 }

                 // Process Loan Repayment
                 if (!empty($validated['loan_repayment_amount']) && $validated['loan_repayment_amount'] > 0) {
                     $loanAccount = LoanAccount::find($validated['loan_account_id']);
                     $repaymentRequest = new Request([
                         'amount' => $validated['loan_repayment_amount'],
                         'payment_date' => $validated['date'],
                         'cash_account_id' => $validated['cash_account_id']
                     ]);
                     $this->loanRepaymentController->store($repaymentRequest, $loanAccount);
                 }
             });
         } catch (\Exception $e) {
             // It's better to show a more specific error from the exception if possible
             $errorMessage = 'An error occurred during the transaction.';
             if ($e instanceof ValidationException) {
                 // Return the specific validation error (e.g., "Insufficient funds")
                 return redirect()->back()->withErrors($e->errors())->withInput();
             }
             // For other generic errors
             return redirect()->back()->with('error', $errorMessage . ' ' . $e->getMessage())->withInput();
         }

         return redirect()->route('collection-center.index')->with('success', 'Transactions processed successfully.');
     }*/

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
            'date' => 'required|date',
            'note' => 'nullable|string',

            // --- IMPROVED SAVINGS VALIDATION ---
            'savings_account_id' => 'nullable|exists:savings_accounts,id',
            // Deposit is required IF a savings account is selected AND withdrawal is empty.
            'savings_deposit_amount' => [
                'nullable', 'numeric', 'min:0',
                Rule::requiredIf(function () use ($request) {
                    return !empty($request->savings_account_id) && empty($request->savings_withdrawal_amount);
                }),
            ],
            // Withdrawal has the same logic, but we can simplify and just check both are not empty below.
            'savings_withdrawal_amount' => 'nullable|numeric|min:0',

            // --- IMPROVED LOAN VALIDATION ---
            'loan_account_id' => 'nullable|exists:loan_accounts,id',
            'loan_repayment_amount' => 'nullable|numeric|min:0.01|required_with:loan_account_id',

            // --- NEW CUSTOM VALIDATION RULE ---
            // We'll add a custom check to ensure at least one transaction is entered.
        ]);

        // --- Custom validation logic after initial checks ---
        $hasSavingsDeposit = !empty($validated['savings_deposit_amount']) && $validated['savings_deposit_amount'] > 0;
        $hasSavingsWithdrawal = !empty($validated['savings_withdrawal_amount']) && $validated['savings_withdrawal_amount'] > 0;
        $hasLoanRepayment = !empty($validated['loan_repayment_amount']) && $validated['loan_repayment_amount'] > 0;

        // If a savings account is selected, at least one of the savings amounts must be greater than 0.
        if (!empty($validated['savings_account_id']) && !$hasSavingsDeposit && !$hasSavingsWithdrawal) {
            throw ValidationException::withMessages([
                'savings_deposit_amount' => 'Please enter a deposit or withdrawal amount for the selected savings account.',
            ]);
        }

        // If both savings deposit AND withdrawal are entered, throw an error.
        if ($hasSavingsDeposit && $hasSavingsWithdrawal) {
            throw ValidationException::withMessages([
                'savings_deposit_amount' => 'You can only process a deposit or a withdrawal at one time, not both.',
            ]);
        }

        // Ensure at least one transaction exists overall
        if (!$hasSavingsDeposit && !$hasSavingsWithdrawal && !$hasLoanRepayment) {
            throw ValidationException::withMessages([
                'savings_deposit_amount' => 'You must enter at least one transaction amount to proceed.',
            ]);
        }

        // The rest of the try-catch block and DB::transaction logic remains the same
        try {
            DB::transaction(function () use ($validated, $hasSavingsDeposit, $hasSavingsWithdrawal, $hasLoanRepayment) {

                // Process Savings Deposit
                if ($hasSavingsDeposit) {
                    $savingsAccount = SavingsAccount::find($validated['savings_account_id']);
                    // Pass the correct date from the form
                    $depositRequest = new Request([
                        'amount' => $validated['savings_deposit_amount'],
                        'description' => $validated['note'],
                        'transaction_date' => $validated['date'],
                        'cash_account_id' => $validated['cash_account_id']
                    ]);
                    $this->savingsTxnController->storeDeposit($depositRequest, $savingsAccount);
                }

                // Process Savings Withdrawal
                if ($hasSavingsWithdrawal) {
                    $savingsAccount = SavingsAccount::find($validated['savings_account_id']);
                    $withdrawalRequest = new Request([
                        'amount' => $validated['savings_withdrawal_amount'],
                        'description' => $validated['note'],
                        'transaction_date' => $validated['date'],
                        'cash_account_id' => $validated['cash_account_id']
                    ]);
                    $this->savingsTxnController->storeWithdrawal($withdrawalRequest, $savingsAccount);
                }

                // Process Loan Repayment
                if ($hasLoanRepayment) {
                    $loanAccount = LoanAccount::find($validated['loan_account_id']);
                    $repaymentRequest = new Request([
                        'amount' => $validated['loan_repayment_amount'],
                        'payment_date' => $validated['date'],
                        'cash_account_id' => $validated['cash_account_id']
                    ]);
                    $this->loanRepaymentController->store($repaymentRequest, $loanAccount);
                }
            });
        } catch (\Exception $e) {
            // It's better to show a more specific error from the exception if possible
            $errorMessage = 'An error occurred during the transaction.';
            if ($e instanceof ValidationException) {
                // Return the specific validation error (e.g., "Insufficient funds")
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
            // For other generic errors
            return redirect()->back()->with('error', $errorMessage . ' ' . $e->getMessage())->withInput();
        }

        return redirect()->route('collection-center.index')->with('success', 'Transactions processed successfully.');
    }
}
