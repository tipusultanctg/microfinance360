<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsProduct;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Demo Tenant Seeder...');
        Artisan::call('permission:cache-reset');

        // --- 1. CREATE DEMO TENANT & USERS ---
        $this->command->info('Creating Demo Tenant and Users...');
        $tenant = Tenant::create(['name' => 'Grameen Digital Cooperative']);

        $orgAdmin = User::create(['tenant_id' => $tenant->id, 'name' => 'Admin User', 'email' => 'admin@demo.com', 'password' => Hash::make('password')]);
        $orgAdmin->assignRole(Role::findByName('Organization Admin'));

        $fieldOfficer = User::create(['tenant_id' => $tenant->id, 'name' => 'Field Officer Jane', 'email' => 'officer@demo.com', 'password' => Hash::make('password')]);
        $fieldOfficer->assignRole(Role::findByName('Field Officer'));

        // --- 2. SEED THE CHART OF ACCOUNTS FOR THIS NEW TENANT ---
        $this->command->info('Seeding Chart of Accounts for the new Tenant...');
        (new ChartOfAccountSeeder)->run($tenant->id);

        // Fetch the core accounts for this tenant that we will use repeatedly
        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->get()->keyBy('name');

        // --- 3. CREATE BRANCHES ---
        $this->command->info('Creating Branches...');
        $branchDhaka = Branch::create(['tenant_id' => $tenant->id, 'name' => 'Dhaka Main Branch']);
        $branchChittagong = Branch::create(['tenant_id' => $tenant->id, 'name' => 'Chittagong Branch']);

        // --- 4. CREATE FINANCIAL PRODUCTS ---
        $this->command->info('Creating Financial Products...');
        $savingsProduct = SavingsProduct::create(['tenant_id' => $tenant->id, 'name' => 'Daily Savings Scheme', 'interest_rate' => 3.5, 'interest_posting_frequency' => 'monthly', 'min_balance_for_interest' => 10.00]);
        $loanProduct = LoanProduct::create(['tenant_id' => $tenant->id, 'name' => 'General Business Loan', 'interest_rate' => 12.00, 'interest_method' => 'flat', 'repayment_frequency' => 'monthly', 'max_loan_term' => 12]);

        // --- 5. CREATE MEMBERS ---
        $this->command->info('Creating Members...');
        $members = Member::factory(10)->create(['tenant_id' => $tenant->id, 'branch_id' => fn() => rand(0, 1) ? $branchDhaka->id : $branchChittagong->id]);

        // --- 6. CREATE SAVINGS ACCOUNTS & TRANSACTIONS ---
        $this->command->info('Creating Savings Accounts...');
        foreach ($members->take(5) as $member) {
            $initialDeposit = rand(50, 500);
            $account = SavingsAccount::create(['tenant_id' => $tenant->id, 'member_id' => $member->id, 'savings_product_id' => $savingsProduct->id, 'account_number' => 'SA-' . $tenant->id . '-' . $member->id, 'balance' => $initialDeposit]);
            $deposit = $account->transactions()->create(['user_id' => $fieldOfficer->id, 'type' => 'deposit', 'amount' => $initialDeposit, 'description' => 'Initial Deposit']);

            // Create the Journal Entry
            $journal = $deposit->journalEntries()->create(['date' => now(), 'description' => "Savings Deposit for Account #{$account->account_number}"]);
            $journal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $accounts['Cash']->id, 'type' => 'debit', 'amount' => $initialDeposit],
                ['chart_of_account_id' => $accounts['Savings Liability']->id, 'type' => 'credit', 'amount' => $initialDeposit],
            ]);
        }

        // --- 7. CREATE LOAN DATA ---
        $this->command->info('Creating Loan Applications & Accounts...');

        // A. Disbursed and partially paid loan
        $disbursedApp = LoanApplication::create(['tenant_id' => $tenant->id, 'member_id' => $members[0]->id, 'loan_product_id' => $loanProduct->id, 'requested_amount' => 5000, 'requested_term' => 12, 'purpose' => 'Business expansion', 'status' => 'disbursed', 'approved_by_user_id' => $orgAdmin->id, 'approved_at' => now()->subMonths(3)]);

        $principal = 5000; $term = 12; $interestRate = 12.00;
        $totalInterest = ($principal * ($interestRate / 100)) * ($term / 12);
        $totalPayable = $principal + $totalInterest;
        $disbursementDate = now()->subMonths(3);

        $loanAccount = LoanAccount::create(['tenant_id' => $tenant->id, 'account_number' => 'L-' . $tenant->id . '-' . $members[0]->id, 'loan_application_id' => $disbursedApp->id, 'member_id' => $members[0]->id, 'loan_product_id' => $loanProduct->id, 'principal_amount' => $principal, 'total_interest' => $totalInterest, 'total_payable' => $totalPayable, 'balance' => $totalPayable, 'term' => $term, 'disbursement_date' => $disbursementDate, 'status' => 'active']);

        $disbursementJournal = $loanAccount->journalEntries()->create(['date' => $disbursementDate, 'description' => "Loan Disbursement for Account #{$loanAccount->account_number}"]);
        $disbursementJournal->ledgerEntries()->createMany([
            ['chart_of_account_id' => $accounts['Loans Receivable']->id, 'type' => 'debit', 'amount' => $principal],
            ['chart_of_account_id' => $accounts['Cash']->id, 'type' => 'credit', 'amount' => $principal],
        ]);

        $currentDueDate = Carbon::parse($disbursementDate);
        for ($i = 1; $i <= $term; $i++) {
            $loanAccount->schedule()->create(['due_date' => $currentDueDate->addMonth(), 'principal_amount' => $principal / $term, 'interest_amount' => $totalInterest / $term, 'total_amount' => $totalPayable / $term]);
        }

        // Simulate 2 repayments
        $this->command->info('Simulating repayments...');
        $installmentAmount = $totalPayable / $term;
        for ($i = 1; $i <= 2; $i++) {
            $paymentDate = (clone $disbursementDate)->addMonths($i);
            $repayment = $loanAccount->repayments()->create(['user_id' => $fieldOfficer->id, 'amount' => $installmentAmount, 'payment_date' => $paymentDate]);
            $loanAccount->schedule()->where('status', 'pending')->first()->update(['status' => 'paid', 'amount_paid' => $installmentAmount]);
            $loanAccount->decrement('balance', $installmentAmount);
            $loanAccount->increment('amount_paid', $installmentAmount);

            $repaymentJournal = $repayment->journalEntries()->create(['date' => $paymentDate, 'description' => "Loan Repayment for Account #{$loanAccount->account_number}"]);
            $repaymentJournal->ledgerEntries()->createMany([
                ['chart_of_account_id' => $accounts['Cash']->id, 'type' => 'debit', 'amount' => $installmentAmount],
                ['chart_of_account_id' => $accounts['Loans Receivable']->id, 'type' => 'credit', 'amount' => $principal / $term],
                ['chart_of_account_id' => $accounts['Interest Income']->id, 'type' => 'credit', 'amount' => $totalInterest / $term],
            ]);
        }

        // B. Approved application
        LoanApplication::create(['tenant_id' => $tenant->id, 'member_id' => $members[1]->id, 'loan_product_id' => $loanProduct->id, 'requested_amount' => 2500, 'requested_term' => 6, 'status' => 'approved', 'approved_by_user_id' => $orgAdmin->id, 'approved_at' => now()]);

        // C. Pending application
        LoanApplication::create(['tenant_id' => $tenant->id, 'member_id' => $members[2]->id, 'loan_product_id' => $loanProduct->id, 'requested_amount' => 10000, 'requested_term' => 24, 'status' => 'pending']);

        $this->command->info('Demo Tenant Seeder finished successfully!');
        $this->command->info('------------------------------------------');
        $this->command->info('Organization Admin: admin@demo.com');
        $this->command->info('Field Officer: officer@demo.com');
        $this->command->info('Password for all users: password');
        $this->command->info('------------------------------------------');
    }
}
