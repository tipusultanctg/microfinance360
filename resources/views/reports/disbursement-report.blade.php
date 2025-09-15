@extends('layouts.master')
{{-- Print styles --}}
@push('style')
    <style>
        @media print {
            body * { visibility: hidden; }
            .card, .card * { visibility: visible; }
            .card { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
            .d-flex, .page-breadcrumb, .card.mb-4 { display: none !important; }
        }
    </style>
@endpush
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Loan Disbursement Report</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0" onclick="window.print()">
                <i class="btn-icon-prepend bi-printer"></i>
                Print Report
            </button>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title">Filter by Date Range</h6>
            <form method="GET" action="{{ route('reports.disbursement-report') }}">
                <div class="row">
                    <div class="col-md-5">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-5">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="card">
        <div class="card-header">
            Report for period: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Disbursement Date</th>
                        <th>Member Name</th>
                        <th>Account #</th>
                        <th>Loan Product</th>
                        <th class="text-end">Principal Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $totalDisbursed = 0;
                    @endphp
                    @forelse ($disbursements as $account)
                        @php
                            $totalDisbursed += $account->principal_amount;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $account->disbursement_date->format('d M, Y') }}</td>
                            <td>{{ $account->member->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('loan-accounts.show', $account->id) }}">
                                    {{ $account->account_number }}
                                </a>
                            </td>
                            <td>{{ $account->loanProduct->name ?? 'N/A' }}</td>
                            <td class="text-end fw-bold">${{ number_format($account->principal_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No loans were disbursed in this period.</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="bg-light">
                        <td colspan="5" class="text-end fw-bold">Total Disbursed:</td>
                        <td class="text-end fw-bold fs-5">${{ number_format($totalDisbursed, 2) }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

@endsection
