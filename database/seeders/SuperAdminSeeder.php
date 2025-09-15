<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the main Tenant for the SaaS platform itself
        $mainTenant = Tenant::create([
            'name' => 'MicroFinance 360 HQ',
        ]);

        // Create the Super Admin User
        $superAdmin = User::create([
            'tenant_id' => $mainTenant->id,
            'name' => 'Super Admin',
            'email' => 'superadmin@mf360.com',
            'password' => Hash::make('password'),
            'is_superadmin' => true,
        ]);

        // Assign the 'Super Admin' role
        $superAdmin->assignRole('Super Admin');
    }
}
