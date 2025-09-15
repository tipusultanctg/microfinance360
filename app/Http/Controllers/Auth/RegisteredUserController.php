<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'organization_name' => ['required', 'string', 'max:255'], // <-- Add validation
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Use a database transaction to ensure both models are created or neither are.
        $user = DB::transaction(function () use ($request) {
            // 1. Create the Tenant
            $tenant = Tenant::create([
                'name' => $request->organization_name
            ]);

            // 2. Create the User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tenant_id' => $tenant->id,
            ]);

            // 3. Assign the 'Organization Admin' role
            $orgAdminRole = Role::findByName('Organization Admin');
            $user->assignRole($orgAdminRole);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);
        session(['tenant_id' => $user->tenant_id]);

        return redirect('/dashboard');
    }
}
