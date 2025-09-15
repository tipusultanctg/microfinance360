<?php

namespace App\Http\Controllers;

use App\Models\LoanAccount;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // --- SUPER ADMIN DASHBOARD ---
        if ($user->hasRole('Super Admin')) {
            $stats = [
                'total_tenants' => Tenant::withoutGlobalScopes()->count(),
                'active_tenants' => Tenant::withoutGlobalScopes()->where('status', 'active')->count(),
                // In a real multi-tenant app, aggregating this data would be complex.
                // These are placeholders for system-wide stats.
                'system_total_members' => Member::withoutGlobalScopes()->count(),
                'system_total_loans' => LoanAccount::withoutGlobalScopes()->count(),
            ];
            return view('dashboard.super-admin', compact('stats'));
        }

        // --- ORGANIZATION ADMIN / STAFF DASHBOARD ---
        if ($user->tenant_id) {
            $stats = [
                'total_members' => Member::count(),
                'active_loans_count' => LoanAccount::where('status', 'active')->count(),
                'total_loan_portfolio' => LoanAccount::where('status', 'active')->sum('balance'),
                'total_savings_balance' => SavingsAccount::where('status', 'active')->sum('balance'),
            ];
            return view('dashboard.organization', compact('stats'));
        }

        // Fallback for any user without a role or tenant
        return view('dashboard.default');
    }
}
