@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Trial Balance</h4>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title">Filter Ledger Entries</h6>
            <form method="GET" action="{{ route('accounting.general-ledger') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('accounting.trial-balance') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>


    <div class="card">
        <div class="card-header">As of: <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</strong></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                    <tr>
                        <th rowspan="2" class="align-middle">Account</th>
                        <th colspan="2" class="text-center">Balance</th>
                    </tr>
                    <tr>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $totalDebits = 0;
                        $totalCredits = 0;
                    @endphp
                    @foreach($trialBalanceData as $row)
                        @php
                            $totalDebits += $row->final_balance_debit;
                            $totalCredits += $row->final_balance_credit;
                        @endphp
                        <tr>
                            <td>{{ $row->account_name }}</td>
                            <td class="text-end">${{ number_format($row->final_balance_debit, 2) }}</td>
                            <td class="text-end">${{ number_format($row->final_balance_credit, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="bg-light fw-bold fs-5">
                        <td>Totals:</td>
                        <td class="text-end">${{ number_format($totalDebits, 2) }}</td>
                        <td class="text-end">${{ number_format($totalCredits, 2) }}</td>
                    </tr>
                    @if(round($totalDebits, 2) !== round($totalCredits, 2))
                        <tr><td colspan="3" class="text-center text-danger">Warning: Totals do not balance!</td></tr>
                    @endif
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
