@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Loan Application Details</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <a href="{{ route('loan-applications.index') }}" class="btn btn-outline-secondary btn-icon-text me-2 mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-arrow-left"></i>
                Back to List
            </a>
            @if($loanApplication->status == 'pending')
                <a href="{{ route('loan-applications.edit', $loanApplication->id) }}" class="btn btn-outline-primary btn-icon-text me-2 mb-2 mb-md-0">
                    <i class="btn-icon-prepend bi-pencil-square"></i>
                    Edit
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <strong>Application ID:</strong> #{{ $loanApplication->id }}
                </div>
                <div>
                    @php
                        $statusClass = match($loanApplication->status) {
                            'pending' => 'warning',
                            'approved' => 'info',
                            'disbursed' => 'success',
                            'rejected' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <strong>Status:</strong> <span class="badge bg-{{ $statusClass }}">{{ ucfirst($loanApplication->status) }}</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="card-title">Member & Product Details</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th style="width: 35%;">Member Name:</th>
                            <td>{{ $loanApplication->member->name }}</td>
                        </tr>
                        <tr>
                            <th>Member ID:</th>
                            <td>{{ $loanApplication->member->member_uid }}</td>
                        </tr>
                        <tr>
                            <th>Loan Product:</th>
                            <td>{{ $loanApplication->loanProduct->name }}</td>
                        </tr>
                        <tr>
                            <th>Interest Rate:</th>
                            <td>{{ $loanApplication->loanProduct->interest_rate }}% ({{ ucfirst($loanApplication->loanProduct->interest_method) }})</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="card-title">Loan Request Details</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th style="width: 35%;">Requested Amount:</th>
                            <td>${{ number_format($loanApplication->requested_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Requested Term:</th>
                            <td>{{ $loanApplication->requested_term }} {{ ucfirst($loanApplication->loanProduct->repayment_frequency) }} installments</td>
                        </tr>
                        <tr>
                            <th>Purpose:</th>
                            <td>{{ $loanApplication->purpose ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <hr>
            @if($loanApplication->status != 'pending')
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="card-title">Processing Details</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 17%;">Processed By:</th>
                                <td>{{ $loanApplication->approvedBy->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Processed At:</th>
                                <td>{{ $loanApplication->approved_at ? $loanApplication->approved_at->format('d M Y, h:i A') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif

            <hr>
            <div class="row mt-4">
                <div class="col-md-12">
                    <h6 class="card-title">Attached Documents</h6>
                    @if($loanApplication->getMedia('loan_documents')->count() > 0)
                        <ul class="list-group">
                            @foreach($loanApplication->getMedia('loan_documents') as $document)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $document->file_name }} ({{ $document->formatted_size }})
                                    <a href="{{ $document->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary">View/Download</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>No documents have been attached to this application.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS FOOTER --}}
        @if($loanApplication->status == 'pending')
            <div class="card-footer text-end">
                {{-- Add Authorization checks here, e.g., @can('approve-loan') --}}
                <form class="d-inline" method="POST" action="{{ route('loan-applications.reject', $loanApplication->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Reject Application</button>
                </form>
                <form class="d-inline" method="POST" action="{{ route('loan-applications.approve', $loanApplication->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">Approve Application</button>
                </form>
                @endif

                @if($loanApplication->status == 'approved')
                    <div class="card-footer text-end">
                        {{-- This button will link to the next step: Loan Disbursement --}}
                        <a href="{{ route('loan-disbursement.create', $loanApplication->id) }}" class="btn btn-primary">Proceed to Disbursement</a>                    </div>
                @endif
            </div>
@endsection
