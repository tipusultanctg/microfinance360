<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::with('branch')->latest()->paginate(10);
        return view('members.index', compact('members'));
    }

    public function create()
    {
        // The BelongsToTenant scope automatically filters the branches
        $branches = Branch::orderBy('name')->get();
        return view('members.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,widowed,divorced',
            'spouse' => 'nullable|string|max:255',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'workplace' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:100',
            'registration_date' => 'nullable|date',
            'photo' => 'nullable|image|max:2048', // Max 2MB
            'kyc_document.*' => 'nullable|file|max:5120', // Max 5MB per file
        ]);

        // Generate a unique member ID
        $tenantId = Auth::user()->tenant_id;
        // This logic should be more robust in a real application
        $validated['member_uid'] = 'M-' . $tenantId . '-' . time();

        $member = Member::create($validated);

        // Handle Photo Upload
        if ($request->hasFile('photo')) {
            $member->addMediaFromRequest('photo')->toMediaCollection('member_photo');
        }

        // Handle KYC Document Uploads
        if ($request->hasFile('kyc_documents')) {
            foreach ($request->file('kyc_documents') as $file) {
                $member->addMedia($file)->toMediaCollection('kyc_documents');
            }
        }

        return redirect()->route('members.index')->with('success', 'Member created successfully.');
    }

    public function show(Member $member)
    {
        // Eager load relationships and media
        $member->load('branch', 'media');
        return view('members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        $branches = Branch::orderBy('name')->get();
        return view('members.edit', compact('member', 'branches'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'status' => 'required|string|in:active,inactive,closed',
            'photo' => 'nullable|image|max:2048',
            'kyc_documents.*' => 'nullable|file|max:5120',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,widowed,divorced',
            'spouse' => 'nullable|string|max:255',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'workplace' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:100',
            'registration_date' => 'nullable|date',
        ]);

        $member->update($validated);

        if ($request->hasFile('photo')) {
            // The singleFile() collection rule will automatically delete the old photo
            $member->addMediaFromRequest('photo')->toMediaCollection('member_photo');
        }

        if ($request->hasFile('kyc_documents')) {
            foreach ($request->file('kyc_documents') as $file) {
                $member->addMedia($file)->toMediaCollection('kyc_documents');
            }
        }

        return redirect()->route('members.index')->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member)
    {
        $member->delete(); // Spatie will automatically delete associated media files
        return redirect()->route('members.index')->with('success', 'Member deleted successfully.');
    }
}
