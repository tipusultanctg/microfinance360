<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::latest()->paginate(10);
        return view('super-admin.subscription-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('super-admin.subscription-plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans',
            'price' => 'required|numeric|min:0',
            'member_limit' => 'nullable|integer|min:0',
            'loan_limit' => 'nullable|integer|min:0',
            'features' => 'nullable|string',
        ]);

        // Convert features textarea to an array
        if (!empty($validated['features'])) {
            $validated['features'] = array_map('trim', explode("\n", $validated['features']));
        }

        SubscriptionPlan::create($validated);

        return redirect()->route('super-admin.subscription-plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('super-admin.subscription-plans.edit', compact('subscriptionPlan'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans,name,' . $subscriptionPlan->id,
            'price' => 'required|numeric|min:0',
            'member_limit' => 'nullable|integer|min:0',
            'loan_limit' => 'nullable|integer|min:0',
            'features' => 'nullable|string',
        ]);

        if (!empty($validated['features'])) {
            $validated['features'] = array_map('trim', explode("\n", $validated['features']));
        } else {
            $validated['features'] = [];
        }

        $subscriptionPlan->update($validated);

        return redirect()->route('super-admin.subscription-plans.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        // Prevent deletion if the plan is in use
        if ($subscriptionPlan->tenants()->count() > 0) {
            return redirect()->route('super-admin.subscription-plans.index')
                ->with('error', 'Cannot delete plan. It is currently assigned to one or more tenants.');
        }

        $subscriptionPlan->delete();

        return redirect()->route('super-admin.subscription-plans.index')
            ->with('success', 'Subscription plan deleted successfully.');
    }
}
