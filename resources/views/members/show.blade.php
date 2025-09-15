@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Member Profile</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <a href="{{ route('members.index') }}" class="btn btn-outline-secondary btn-icon-text me-2 mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-arrow-left"></i>
                Back to List
            </a>
            <a href="{{ route('members.edit', $member->id) }}" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-pencil-square"></i>
                Edit Member
            </a>
        </div>
    </div>

    <div class="row">
        {{-- =================================== --}}
        {{-- LEFT COLUMN: PROFILE & KYC --}}
        {{-- =================================== --}}
        <div class="col-md-4 grid-margin">
            {{-- Profile Card --}}
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $member->getFirstMediaUrl('member_photo') ?: 'https://placehold.co/150' }}" alt="photo" class="wd-150 ht-150 rounded-circle mb-3 img-fluid">
                    <h5 class="card-title">{{ $member->name }}</h5>
                    <p class="text-muted">Member ID: {{ $member->member_uid }}</p>
                    <span class="badge bg-{{ $member->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($member->status) }}</span>
                </div>
            </div>

            {{-- KYC Documents Card --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">KYC Documents</h6>
                </div>
                <div class="card-body">
                    @forelse ($member->getMedia('kyc_documents') as $document)
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <span><i class="bi-file-earmark-text me-2"></i>{{ $document->file_name }}</span>
                            <a href="{{ $document->getUrl() }}" target="_blank" class="btn btn-xs btn-outline-primary">View</a>
                        </div>
                    @empty
                        <p class="text-muted">No KYC documents uploaded.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- =================================== --}}
        {{-- RIGHT COLUMN: DETAILS & ACCOUNTS --}}
        {{-- =================================== --}}
        <div class="col-md-8 grid-margin">
            {{-- Accounts Overview --}}
            <div class="card mb-3">
                <div class="card-header"><h6 class="card-title mb-0">Accounts Overview</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-baseline">
                                <i class="bi-piggy-bank fs-3 text-success me-2"></i>
                                <div>
                                    <p class="text-muted mb-0">Total Savings Balance</p>
                                    <h5 class="fw-bold">${{ number_format($member->savingsAccounts->sum('balance'), 2) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-baseline">
                                <i class="bi-cash-coin fs-3 text-danger me-2"></i>
                                <div>
                                    <p class="text-muted mb-0">Total Loan Outstanding</p>
                                    <h5 class="fw-bold">${{ number_format($member->loanAccounts->sum('balance'), 2) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Details Tabs --}}
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-line" id="myTab" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="personal-tab" data-bs-toggle="tab" href="#personal" role="tab">Personal Details</a></li>
                        <li class="nav-item"><a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab">Contact & Employment</a></li>
                        <li class="nav-item"><a class="nav-link" id="accounts-tab" data-bs-toggle="tab" href="#accounts" role="tab">Associated Accounts</a></li>
                    </ul>

                    <div class="tab-content border border-top-0 p-3" id="myTabContent">
                        {{-- Personal Details Tab --}}
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <table class="table table-sm table-borderless">
                                <tr><th style="width: 35%;">Father's Name</th><td>{{ $member->father_name ?? 'N/A' }}</td></tr>
                                <tr><th>Mother's Name</th><td>{{ $member->mother_name ?? 'N/A' }}</td></tr>
                                <tr><th>Gender</th><td>{{ ucfirst($member->gender) ?? 'N/A' }}</td></tr>
                                <tr><th>Date of Birth</th><td>{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('d M, Y') : 'N/A' }}</td></tr>
                                <tr><th>Marital Status</th><td>{{ ucfirst($member->marital_status) ?? 'N/A' }}</td></tr>
                                <tr><th>Spouse's Name</th><td>{{ $member->spouse ?? 'N/A' }}</td></tr>
                                <tr><th>Religion</th><td>{{ ucfirst($member->religion) ?? 'N/A' }}</td></tr>
                                <tr><th>Registration Date</th><td>{{ $member->registration_date ? \Carbon\Carbon::parse($member->registration_date)->format('d M, Y') : 'N/A' }}</td></tr>
                            </table>
                        </div>

                        {{-- Contact & Employment Tab --}}
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <table class="table table-sm table-borderless">
                                <tr><th style="width: 35%;">Phone Number</th><td>{{ $member->phone ?? 'N/A' }}</td></tr>
                                <tr><th>Present Address</th><td>{{ $member->present_address ?? 'N/A' }}</td></tr>
                                <tr><th>Permanent Address</th><td>{{ $member->permanent_address ?? 'N/A' }}</td></tr>
                                <tr><td colspan="2"><hr></td></tr>
                                <tr><th>Occupation</th><td>{{ $member->occupation ?? 'N/A' }}</td></tr>
                                <tr><th>Workplace</th><td>{{ $member->workplace ?? 'N/A' }}</td></tr>
                            </table>
                        </div>

                        {{-- Associated Accounts Tab --}}
                        <div class="tab-pane fade" id="accounts" role="tabpanel">
                            <h6 class="mb-3">Savings Accounts</h6>
                            @forelse($member->savingsAccounts as $account)
                                <a href="{{ route('savings-accounts.show', $account->id) }}" class="text-decoration-none text-dark">
                                    <div class="alert alert-success">{{ $account->account_number }} - Balance: ${{ number_format($account->balance, 2) }}</div>
                                </a>
                            @empty
                                <p class="text-muted">No savings accounts found.</p>
                            @endforelse

                            <h6 class="mt-4 mb-3">Loan Accounts</h6>
                            @forelse($member->loanAccounts as $account)
                                <a href="{{ route('loan-accounts.show', $account->id) }}" class="text-decoration-none text-dark">
                                    <div class="alert alert-danger">{{ $account->account_number }} - Balance: ${{ number_format($account->balance, 2) }}</div>
                                </a>
                            @empty
                                <p class="text-muted">No loan accounts found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
