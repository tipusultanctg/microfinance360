<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Models\SavingsAccount;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PostSavingsInterest extends Command
{
    protected $signature = 'savings:post-interest';
    protected $description = 'Calculate and post interest for all eligible savings accounts';

    public function handle()
    {
        $this->info('Starting interest posting process...');

        // We must process interest for each tenant separately
        $tenants = Tenant::where('status', 'active')->get();
        foreach ($tenants as $tenant) {

            // --- Switch to the tenant's context ---
            // This is a simplified way. A full SaaS might use a package like stancl/tenancy.
            // For now, we manually filter all queries by tenant_id.
            $this->info("Processing tenant: {$tenant->name} (ID: {$tenant->id})");

            // Find all active savings accounts for this tenant
            $accounts = SavingsAccount::with('savingsProduct')
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->get();

            if ($accounts->isEmpty()) {
                $this->line('No active accounts found for this tenant.');
                continue;
            }

            // Get the required Chart of Accounts for this tenant
            $interestExpenseAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('name', 'Interest Expense')->first();
            $savingsLiabilityAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('name', 'Savings Liability')->first();

            // Note: We need to add 'Interest Expense' to our ChartOfAccountSeeder
            if (!$interestExpenseAccount || !$savingsLiabilityAccount) {
                $this->error("Core accounting ledgers (Interest Expense, Savings Liability) are missing for Tenant ID: {$tenant->id}. Skipping.");
                continue;
            }

            foreach ($accounts as $account) {
                // Logic to check if interest should be posted today
                // For a monthly product, this checks if today is the last day of the month.
                // This logic would be expanded for quarterly, etc.
                if ($account->savingsProduct->interest_posting_frequency === 'monthly' && today()->isLastOfMonth()) {

                    // Check if balance is sufficient
                    if ($account->balance < $account->savingsProduct->min_balance_for_interest) {
                        continue;
                    }

                    // --- CALCULATION ---
                    // Simple interest calculation for one month
                    $annualRate = $account->savingsProduct->interest_rate;
                    $monthlyRate = ($annualRate / 100) / 12;
                    $interestAmount = round($account->balance * $monthlyRate, 2);

                    if ($interestAmount > 0) {
                        // Use a transaction for safety
                        DB::transaction(function () use ($account, $interestAmount, $interestExpenseAccount, $savingsLiabilityAccount) {
                            $transaction = $account->transactions()->create([
                                'type' => 'interest',
                                'amount' => $interestAmount,
                                'description' => 'Monthly interest posting',
                                'transaction_date' => now(),
                            ]);
                            $account->increment('balance', $interestAmount);

                            // Post to GL
                            $journal = $transaction->journalEntries()->create(['date' => now(), 'description' => "Interest Posting for Acc #{$account->account_number}"]);
                            $journal->ledgerEntries()->createMany([
                                ['chart_of_account_id' => $interestExpenseAccount->id, 'type' => 'debit', 'amount' => $interestAmount], // Interest is an expense to the MFI
                                ['chart_of_account_id' => $savingsLiabilityAccount->id, 'type' => 'credit', 'amount' => $interestAmount], // Increases the liability to the member
                            ]);
                        });
                        $this->line("Posted \${$interestAmount} interest to account #{$account->account_number}.");
                    }
                }
            }
        }
        $this->info('Interest posting process finished.');
        return 0;
    }
}
