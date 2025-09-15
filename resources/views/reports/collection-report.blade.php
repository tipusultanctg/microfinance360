@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Collection Report</h4>
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
            <h6 class="card-title">Filter Report</h6>
            <form method="GET" action="{{ route('reports.collection-report') }}">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="user_id" class="form-label">Collected By (Staff)</label>
                        <select class="form-select" name="user_id" id="user_id">
                            <option value="">All Staff</option>
                            @foreach($staff as $user)
                                <option value="{{ $user->id }}" @selected($selectedStaff == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
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
                        <th>Date</th>
                        <th>Member Name</th>
                        <th>Account #</th>
                        <th>Type</th>
                        <th>Collected By</th>
                        <th class="text-end">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $totalCollected = 0;
                    @endphp
                    @forelse ($collections as $collection)
                        @php
                            $totalCollected += $collection->amount;
                        @endphp
                        <tr>
                            <td>{{ $collection->date->format('d M, Y') }}</td>
                            <td>{{ $collection->member_name }}</td>
                            <td>{{ $collection->account_number }}</td>
                            <td>
                                @if($collection->type === 'Loan Repayment')
                                    <span class="badge bg-primary">{{ $collection->type }}</span>
                                @else
                                    <span class="badge bg-success">{{ $collection->type }}</span>
                                @endif
                            </td>
                            <td>{{ $collection->collected_by }}</td>
                            <td class="text-end fw-bold">${{ number_format($collection->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No collections were found in this period.</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="bg-light">
                        <td colspan="5" class="text-end fw-bold">Total Collected:</td>
                        <td class="text-end fw-bold fs-5">${{ number_format($totalCollected, 2) }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
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
