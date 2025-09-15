<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationProfileController extends Controller
{
    /**
     * Show the form for editing the organization's profile.
     */
    public function edit()
    {
        // Get the current tenant for the authenticated user
        $tenant = Auth::user()->tenant;

        // An extra check to ensure a tenant exists for the user
        if (!$tenant) {
            abort(404, 'Organization not found.');
        }

        return view('organization-profile.edit', compact('tenant'));
    }

    /**
     * Update the organization's profile in storage.
     */
    public function update(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            abort(404, 'Organization not found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tenant->update($validated);

        return redirect()->route('organization.profile.edit')
            ->with('success', 'Organization profile updated successfully.');
    }
}
