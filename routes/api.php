<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FieldOfficerController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Public API Routes ---
Route::post('/v1/login', [AuthController::class, 'login']);

// --- Protected API Routes (Require a valid token) ---
Route::middleware('auth:sanctum')->group(function () {

    // Logout route
    Route::post('/v1/logout', [AuthController::class, 'logout']);

    // Test endpoint to verify authentication is working
    Route::get('/v1/user', function (Request $request) {
        return $request->user();
    });

    // --- NEW FIELD OFFICER ENDPOINT ---
    Route::get('/v1/collection-sheet', [FieldOfficerController::class, 'collectionSheet']);

    Route::post('/v1/savings-accounts/{savingsAccount}/deposits', [TransactionController::class, 'storeSavingsDeposit']);
    Route::post('/v1/loan-accounts/{loanAccount}/repayments', [TransactionController::class, 'storeLoanRepayment']);

    Route::get('/v1/branches', [MemberController::class, 'getBranches']);
    Route::post('/v1/members', [MemberController::class, 'store']);
    Route::get('/v1/members/{member}/accounts', [MemberController::class, 'getMemberData']);

});
