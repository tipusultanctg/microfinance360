@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Portfolio at Risk (PAR) Report</h4>
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
            <h6 class="card-title">Filter by Date</h6>
            <form method="GET" action="{{ route('reports.par-report') }}">
                <div class="row">
                    <div class="col-md-5">
                        <label for="as_of_date" class="form-label">As of Date</label>
                        <input type="date" name="as_of_date" id="as_of_date" class="form-control" value="{{ $asOfDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Generate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Table --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">PAR Summary as of {{ $asOfDate->format('d M, Y') }}</h6>
            <p class="text-muted">Total Portfolio (Outstanding Balance): <strong>${{ number_format($totalPortfolio, 2) }}</strong></p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                    <tr>
                        <th>Risk Category (Days Overdue)</th>
                        <th># of Loans</th>
                        <th>Amount at Risk</th>
                        <th>PAR %</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $totalRiskAmount = 0; @endphp
                    @foreach($parBuckets as $bucketName => $bucketData)
                        @php $totalRiskAmount += $bucketData['amount']; @endphp
                        <tr>
                            <td>PAR {{ $bucketName }}</td>
                            <td>{{ $bucketData['count'] }}</td>
                            <td>${{ number_format($bucketData['amount'], 2) }}</td>
                            <td class="fw-bold">
                                {{ $totalPortfolio > 0 ? number_format(($bucketData['amount'] / $totalPortfolio) * 100, 2) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="bg-light">
                        <td class="fw-bold">Total Portfolio at Risk</td>
                        <td class="fw-bold">{{ array_sum(array_column($parBuckets, 'count')) }}</td>
                        <td class="fw-bold">${{ number_format($totalRiskAmount, 2) }}</td>
                        <td class="fw-bold fs-5">
                            {{ $totalPortfolio > 0 ? number_format(($totalRiskAmount / $totalPortfolio) * 100, 2) : 0 }}%
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Detailed List of Overdue Loans --}}
    <div class="card">
        <div class="card-header"><h6 class="card-title mb-0">Detailed List of Loans at Risk</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                    <tr>
                        <th>Member Name</th>
                        <th>Account #</th>
                        <th>Days Overdue</th>
                        <th class="text-end">Outstanding Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($loansAtRisk as $loan)
                        <tr>
                            <td>{{ $loan->member->name }}</td>
                            <td><a href="{{ route('loan-accounts.show', $loan->id) }}">{{ $loan->account_number }}</a></td>
                            <td><span class="badge bg-danger">{{ $loan->days_overdue }} days</span></td>
                            <td class="text-end fw-bold">${{ number_format($loan->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-success">Congratulations! No loans are currently at risk.</td>
                        </tr>
                    @endforelse
                    </tbody>
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
                .d-flex, .page-breadcrumb, .card.mb-4:first-of-type { display: none !important; }
            }
        </style>
    @endpush
