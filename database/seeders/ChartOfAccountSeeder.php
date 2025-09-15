<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Can be called with a specific tenant_id to seed for that tenant.
     * If no tenant_id is provided, it seeds for all existing tenants.
     */
    public function run(int $tenantId = null): void
    {
        $accounts = [
            ['name' => 'Cash', 'type' => 'asset', 'is_locked' => true],
            ['name' => 'Loans Receivable', 'type' => 'asset', 'is_locked' => true],
            ['name' => 'Savings Liability', 'type' => 'liability', 'is_locked' => true],
            ['name' => 'Interest Income', 'type' => 'income', 'is_locked' => true],
            ['name' => 'Fee Income', 'type' => 'income', 'is_locked' => true],
            ['name' => 'Interest Expense', 'type' => 'expense', 'is_locked' => true],
            ['name' => "Owner's Investment & Capital", 'type' => 'equity', 'is_locked' => true],
        ];

        if ($tenantId) {
            $tenants = Tenant::where('id', $tenantId)->get();
            if ($tenants->isEmpty()) {
                $this->command->error("Tenant with ID {$tenantId} not found.");
                return;
            }
        } else {
            $this->command->info('Seeding default Chart of Accounts for all existing tenants...');
            $tenants = Tenant::all();
        }

        foreach ($tenants as $tenant) {
            foreach ($accounts as $account) {
                ChartOfAccount::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $account['name']],
                    ['type' => $account['type'], 'is_locked' => $account['is_locked']]
                );
            }
        }
    }
}
