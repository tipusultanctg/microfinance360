<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Organization Admin']);
        Role::create(['name' => 'Branch Manager']);
        Role::create(['name' => 'Field Officer']);
        Role::create(['name' => 'Accountant']);
    }
}
