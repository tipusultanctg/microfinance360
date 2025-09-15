<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LoanRepaymentController;
use App\Http\Controllers\SavingsTransactionController;
use App\Models\LoanAccount;
use App\Models\SavingsAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Store a new savings deposit from the API.
     */
    public function storeSavingsDeposit(Request $request, SavingsAccount $savingsAccount)
    {
        // Authorize: Ensure the account belongs to the officer's tenant
        if ($savingsAccount->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        // We will reuse the logic from our existing web controller.
        // This avoids code duplication and ensures business rules are consistent.
        $webController = new SavingsTransactionController();

        try {
            // We need to create a new Request object to pass to the method,
            // as it expects specific keys ('cash_account_id' is not needed here yet)
            $webRequest = new Request($request->only(['amount', 'description', 'transaction_date', 'cash_account_id']));

            $webController->storeDeposit($webRequest, $savingsAccount);
        } catch (ValidationException $e) {
            // Catch validation errors (e.g., insufficient funds) and return a proper API error
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Savings deposit recorded successfully.',
            'new_balance' => $savingsAccount->fresh()->balance,
        ], 201); // 201 Created
    }

    /**
     * Store a new loan repayment from the API.
     */
    public function storeLoanRepayment(Request $request, LoanAccount $loanAccount)
    {
        // Authorize: Ensure the account belongs to the officer's tenant
        if ($loanAccount->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'cash_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $webController = new LoanRepaymentController();

        try {
            // Create a new Request object for the web controller method
            $webRequest = new Request($request->only(['amount', 'payment_date', 'cash_account_id']));

            $webController->store($webRequest, $loanAccount);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Loan repayment recorded successfully.',
            'new_balance' => $loanAccount->fresh()->balance,
        ], 201);
    }
}
