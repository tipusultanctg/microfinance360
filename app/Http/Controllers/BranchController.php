<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BranchController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // The BelongsToTenant scope automatically filters branches.
        $branches = Branch::latest()->paginate(10);
        return view('branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Simple authorization check
        if (!auth()->user()->hasRole('Organization Admin')) {
            abort(403, 'You are not authorized to perform this action.');
        }
        return view('branches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Organization Admin')) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        // The BelongsToTenant trait automatically adds the tenant_id
        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        if (!auth()->user()->hasRole('Organization Admin')) {
            abort(403, 'You are not authorized to perform this action.');
        }
        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        if (!auth()->user()->hasRole('Organization Admin')) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        if (!auth()->user()->hasRole('Organization Admin')) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}
