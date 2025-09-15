<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{

    public function index()
    {
        // --- FIX #2: REFINE THE QUERY TO EXPLICITLY EXCLUDE SUPER ADMINS ---
        // The global TenantScope still filters by the current user's tenant_id.
        // We add whereDoesntHave to ensure that even if a Super Admin somehow shared
        // a tenant_id, they would be excluded from a "staff" list.
        $staff = User::with('branch')
            ->where('id', '!=', auth()->id())
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Super Admin');
            })
            ->latest()
            ->paginate(10);

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        $roles = Role::whereNotIn('name', ['Super Admin', 'Organization Admin'])->pluck('name', 'name');
        $branches = Branch::orderBy('name')->get(); // <-- Get branches
        return view('staff.create', compact('roles', 'branches')); // <-- Pass branches to view
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'exists:roles,name'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        // The BelongsToTenant trait auto-sets the tenant_id
        $staffUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'branch_id' => $request->branch_id,
        ]);

        $staffUser->assignRole($request->role);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    public function edit(User $staff)
    {
        // Ensure we're not editing the admin via this interface
        if ($staff->hasRole('Organization Admin')) {
            abort(403);
        }
        $roles = Role::whereNotIn('name', ['Super Admin', 'Organization Admin'])->pluck('name', 'name');
        $branches = Branch::orderBy('name')->get(); // <-- Get branches
        return view('staff.edit', compact('staff', 'roles', 'branches')); // <-- Pass branches
    }


    public function update(Request $request, User $staff)
    {
        if ($staff->hasRole('Organization Admin')) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $staff->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'exists:roles,name'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $staff->update([
            'name' => $request->name,
            'email' => $request->email,
            'branch_id' => $request->branch_id,
        ]);

        if ($request->filled('password')) {
            $staff->update(['password' => Hash::make($request->password)]);
        }

        // Sync roles to update the user's role
        $staff->syncRoles([$request->role]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $staff)
    {
        // Prevent admin from deleting themselves
        if ($staff->id === auth()->id() || $staff->hasRole('Organization Admin')) {
            return redirect()->route('staff.index')
                ->with('error', 'You cannot delete this user.');
        }

        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Staff member deleted successfully.');
    }
}
