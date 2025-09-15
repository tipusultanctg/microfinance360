<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class TenantController extends Controller
{
    public function index()
    {
        // Use with('subscriptionPlan') for eager loading to prevent N+1 query problems
        $tenants = Tenant::withoutGlobalScopes()->with('subscriptionPlan')->latest()->paginate(10);
        return view('super-admin.tenants.index', compact('tenants'));
    }

    /**
     * --- NEW METHOD ---
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        $plans = SubscriptionPlan::all();
        return view('super--admin.tenants.create', compact('plans'));
    }

    /**
     * --- NEW METHOD ---
     * Store a newly created tenant and its admin user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class.',email'],
            'admin_password' => ['required', 'confirmed', Rules\Password::defaults()],
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'status' => ['required', 'string', 'in:active,suspended'],
        ]);

        // Use a transaction to ensure atomicity
        DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name' => $request->organization_name,
                'status' => $request->status,
                'subscription_plan_id' => $request->subscription_plan_id,
            ]);

            $adminUser = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'tenant_id' => $tenant->id,
            ]);

            $orgAdminRole = Role::findByName('Organization Admin');
            $adminUser->assignRole($orgAdminRole);
        });

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant and Admin User created successfully.');
    }


    /**
     * --- UPDATED METHOD ---
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        // We must bypass the global scope to fetch the correct tenant
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenant->id);
        $plans = SubscriptionPlan::all();
        return view('super-admin.tenants.edit', compact('tenant', 'plans'));
    }

    /**
     * --- UPDATED METHOD ---
     * Update the specified tenant in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        // Bypass scope to ensure we are updating the correct tenant
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenant->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:active,suspended',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
        ]);

        $tenant->update([
            'name' => $request->name,
            'status' => $request->status,
            'subscription_plan_id' => $request->subscription_plan_id,
        ]);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    /**
     * --- NEW METHOD ---
     * Remove the specified tenant and all its data from storage.
     */
    public function destroy(Tenant $tenant)
    {
        // Bypass scope to ensure we are deleting the correct tenant
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenant->id);

        // The onDelete('cascade') on the users table will handle deleting associated users.
        // The onDelete('cascade') on other tables (branches, etc.) will handle their data.
        $tenant->delete();

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant and all associated data deleted successfully.');
    }
}
