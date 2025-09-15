<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CapitalInvestmentController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\CollectionCenterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LoanAccountController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\LoanClosureController;
use App\Http\Controllers\LoanDisbursementController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\LoanRepaymentController;
use App\Http\Controllers\ManualEntryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrganizationProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResetTenantController;
use App\Http\Controllers\SavingsAccountController;
use App\Http\Controllers\SavingsClosureController;
use App\Http\Controllers\SavingsProductController;
use App\Http\Controllers\SavingsTransactionController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SuperAdmin\SubscriptionPlanController;
use App\Http\Controllers\SuperAdmin\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::resource('branches', BranchController::class);
    Route::resource('staff', StaffController::class);
    Route::resource('members', MemberController::class);

    Route::resource('savings-accounts', SavingsAccountController::class);
    Route::post('/savings-accounts/{savingsAccount}/deposit', [SavingsTransactionController::class, 'storeDeposit'])->name('savings-transactions.deposit');
    Route::post('/savings-accounts/{savingsAccount}/withdrawal', [SavingsTransactionController::class, 'storeWithdrawal'])->name('savings-transactions.withdrawal');

    Route::post('loan-applications/{loanApplication}/approve', [LoanApplicationController::class, 'approve'])->name('loan-applications.approve');
    Route::post('loan-applications/{loanApplication}/reject', [LoanApplicationController::class, 'reject'])->name('loan-applications.reject');
    Route::resource('loan-applications', LoanApplicationController::class);

    Route::get('loan-applications/{loanApplication}/disburse', [LoanDisbursementController::class, 'create'])->name('loan-disbursement.create');
    Route::post('loan-applications/{loanApplication}/disburse', [LoanDisbursementController::class, 'store'])->name('loan-disbursement.store');

    Route::resource('loan-accounts', LoanAccountController::class)->except(['create', 'store', 'edit', 'update']);
    Route::post('loan-accounts/{loanAccount}/repayments', [LoanRepaymentController::class, 'store'])->name('loan-repayments.store');

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/collection-sheet', [ReportController::class, 'collectionSheet'])->name('collection-sheet');
        Route::get('/disbursement-report', [ReportController::class, 'disbursementReport'])->name('disbursement-report');
        Route::get('/collection-report', [ReportController::class, 'collectionReport'])->name('collection-report');
        Route::get('/par-report', [ReportController::class, 'portfolioAtRisk'])->name('par-report');
    });

    Route::middleware('role:Organization Admin')
        ->prefix('organization')
        ->name('organization.')
        ->group(function () {
            Route::get('/profile', [OrganizationProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [OrganizationProfileController::class, 'update'])->name('profile.update');
            Route::resource('savings-products', SavingsProductController::class);
            Route::resource('loan-products', LoanProductController::class);

            Route::delete('/savings-transactions/{savingsTransaction}', [SavingsTransactionController::class, 'destroy'])->name('savings-transactions.destroy');
            Route::delete('/loan-repayments/{loanRepayment}', [LoanRepaymentController::class, 'destroy'])->name('loan-repayments.destroy');

        });

    // Protected by Organization Admin role
    Route::middleware('role:Organization Admin')->prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/', [AccountingController::class, 'index'])->name('index');
        Route::get('/general-ledger', [AccountingController::class, 'generalLedger'])->name('general-ledger');
        Route::resource('chart-of-accounts', ChartOfAccountController::class);
        Route::get('/general-ledger', [AccountingController::class, 'generalLedger'])->name('general-ledger');

        Route::get('/manual-entries/create', [ManualEntryController::class, 'create'])->name('manual-entries.create');
        Route::post('/manual-entries', [ManualEntryController::class, 'store'])->name('manual-entries.store');
        Route::resource('expenses', ExpenseController::class)->except('show');
        Route::get('/trial-balance', [AccountingController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/income-statement', [AccountingController::class, 'incomeStatement'])->name('income-statement');
        Route::get('/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('balance-sheet');
        Route::resource('capital-investments', CapitalInvestmentController::class)->except(['show']);

    });


    Route::get('/savings-transactions/{savingsTransaction}/receipt', [SavingsTransactionController::class, 'showReceipt'])->name('savings-transactions.receipt');

    Route::get('loan-accounts/{loanAccount}/close', [LoanClosureController::class, 'create'])->name('loan-closure.create');
    Route::post('loan-accounts/{loanAccount}/close', [LoanClosureController::class, 'store'])->name('loan-closure.store');

    Route::get('savings-accounts/{savingsAccount}/close', [SavingsClosureController::class, 'create'])->name('savings-closure.create');
    Route::post('savings-accounts/{savingsAccount}/close', [SavingsClosureController::class, 'store'])->name('savings-closure.store');

    Route::delete('savings-closures/{savingsClosure}', [SavingsClosureController::class, 'destroy'])->name('savings-closure.destroy');

    Route::get('/collection-center', [CollectionCenterController::class, 'index'])->name('collection-center.index');
    Route::post('/collection-center', [CollectionCenterController::class, 'store'])->name('collection-center.store');

    // An API-like route to fetch accounts for a selected member
    Route::get('/api/members/{member}/accounts', [CollectionCenterController::class, 'getMemberData'])->name('api.member-accounts');



    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/loan-accounts/{loanAccount}/agreement', [DocumentController::class, 'generateLoanAgreement'])->name('loan-agreement');
        Route::get('/loan-accounts/{loanAccount}/no-dues-certificate', [DocumentController::class, 'generateNoDuesCertificate'])->name('no-dues-certificate');
        Route::get('/savings-accounts/{savingsAccount}/statement', [DocumentController::class, 'generateSavingsStatement'])->name('savings-statement');


    });

    Route::middleware('role:Organization Admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/reset-data', [ResetTenantController::class, 'index'])->name('reset.index');
        Route::post('/reset-data', [ResetTenantController::class, 'reset'])->name('reset.confirm');
    });

});
Route::middleware(['auth', 'role:Super Admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('super-admin.dashboard');
    })->name('dashboard');

    Route::resource('tenants', TenantController::class);
    Route::resource('subscription-plans', SubscriptionPlanController::class);
});
require __DIR__.'/auth.php';
