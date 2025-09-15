@extends('layouts.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}">
@endpush
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Loan Account Details</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <a href="{{ route('loan-accounts.index') }}" class="btn btn-outline-secondary btn-icon-text me-2 mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-arrow-left"></i>
                Back to List
            </a>
            <a href="{{ route('documents.loan-agreement', $loanAccount->id) }}" target="_blank" class="btn btn-info btn-icon-text me-2 mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-file-earmark-pdf"></i>
                Download Agreement
            </a>
            @if(in_array($loanAccount->status, ['paid', 'closed']))
                <a href="{{ route('documents.no-dues-certificate', $loanAccount->id) }}" target="_blank" class="btn btn-success btn-icon-text me-2 mb-2 mb-md-0">
                    <i class="btn-icon-prepend bi-patch-check-fill"></i>
                    No-Dues Certificate
                </a>
            @endif
            @if($loanAccount->status === 'active')
                <a href="{{ route('loan-closure.create', $loanAccount->id) }}" class="btn btn-warning btn-icon-text me-2 mb-2 mb-md-0">
                    <i class="btn-icon-prepend bi-lock"></i>
                    Close Loan (Foreclosure)
                </a>
            @endif
            {{-- --- ADD THIS DELETE FORM --- --}}
            @role('Organization Admin')
            <form action="{{ route('loan-accounts.destroy', $loanAccount->id) }}" method="POST" class="delete-form d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-icon-text mb-2 mb-md-0">
                    <i class="btn-icon-prepend bi-trash"></i>
                    Delete Loan
                </button>
            </form>
            @endrole
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- Loan Summary --}}
        <div class="col-md-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Loan Summary for {{ $loanAccount->member->name }}
                        ({{ $loanAccount->account_number }})</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Principal:</strong><br>${{ number_format($loanAccount->principal_amount, 2) }}</div>
                        <div class="col-md-3"><strong>Total
                                Interest:</strong><br>${{ number_format($loanAccount->total_interest, 2) }}</div>
                        <div class="col-md-3"><strong>Total
                                Payable:</strong><br>${{ number_format($loanAccount->total_payable, 2) }}</div>
                        <div class="col-md-3"><strong>Status:</strong><br><span
                                class="badge bg-success">{{ ucfirst($loanAccount->status) }}</span></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3"><strong>Amount
                                Paid:</strong><br>${{ number_format($loanAccount->amount_paid, 2) }}</div>
                        <div class="col-md-3"><strong>Remaining Balance:</strong><br><h4>
                                ${{ number_format($loanAccount->balance, 2) }}</h4></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Repayment Form --}}
        @if($loanAccount->status === 'active')
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Collect Repayment</h6>
                        <form method="POST" action="{{ route('loan-repayments.store', $loanAccount->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="amount" class="form-label">Payment Amount</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control"
                                       value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="cash_account_id" class="form-label">Cash/Bank Account</label>
                                <select name="cash_account_id" class="form-select" required>
                                    @foreach($cashAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Repayment Schedule --}}
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Repayment Schedule</h6>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        {{--<table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Due Date</th>
                                <th>Principal</th>
                                <th>Interest</th>
                                <th>Total Due</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($loanAccount->schedule as $installment)
                                <tr>
                                    <td>{{ $installment->due_date->format('d M, Y') }}</td>
                                    <td>${{ number_format($installment->principal_amount, 2) }}</td>
                                    <td>${{ number_format($installment->interest_amount, 2) }}</td>
                                    <td><strong>${{ number_format($installment->total_amount, 2) }}</strong></td>
                                    <td>
                                    <span
                                        class="badge bg-{{ $installment->status === 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($installment->status) }}
                                    </span>
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>--}}
                        <table class="table table-sm">
                            <thead class="bg-light">
                            <tr>
                                <th>Due Date</th>
                                <th>Total Due</th>
                                <th>Amount Paid</th>
                                <th>Outstanding</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($loanAccount->schedule as $installment)
                                @php
                                    // Determine row class based on status and due date for better visibility
                                    $rowClass = '';
                                    if ($installment->status == 'pending' && $installment->due_date->isPast()) {
                                        $rowClass = 'table-danger'; // Overdue
                                    } elseif ($installment->status == 'paid') {
                                        $rowClass = 'table-success'; // Fully paid
                                    } elseif ($installment->amount_paid > 0) {
                                        $rowClass = 'table-info'; // Partially paid
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $installment->due_date->format('d M, Y') }}</td>
                                    <td>${{ number_format($installment->total_amount, 2) }}</td>
                                    <td>${{ number_format($installment->amount_paid, 2) }}</td>
                                    <td class="fw-bold">${{ number_format($installment->balance, 2) }}</td>
                                    <td>
                                        @if($installment->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($installment->amount_paid > 0)
                                            <span class="badge bg-info">Partial</span>
                                        @elseif($installment->due_date->isPast())
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment History --}}
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Payment History</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Payment Date</th>
                                <th>Amount Paid</th>
                                <th>Collected By</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($loanAccount->repayments as $repayment)
                                <tr>
                                    <td>{{ $repayment->payment_date->format('d M Y, h:i A') }}</td>
                                    <td>${{ number_format($repayment->amount, 2) }}</td>
                                    <td>{{ $repayment->user->name ?? 'N/A' }}</td>
                                    <td>
                                        @role('Organization Admin')
                                        <form action="{{ route('organization.loan-repayments.destroy', $repayment->id) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger btn-icon" title="Reverse Repayment">
                                                <i data-lucide="x"></i>
                                            </button>
                                        </form>
                                        @endrole
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No payments have been recorded yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will permanently delete the loan account and ALL its repayment data!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush


