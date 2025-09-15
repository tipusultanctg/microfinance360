@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Income Statement</h4>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title">Filter Income Statement</h6>
            <form method="GET" action="{{ route('accounting.income-statement') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('accounting.income-statement') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>


    <div class="card">
        <div class="card-header text-center">
            <h5>{{ auth()->user()->tenant->name }}</h5>
            <h6>Income Statement</h6>
            <p class="text-muted">For the period from {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</p>
        </div>
        <div class="card-body">
            {{-- Income Section --}}
            <h6 class="mt-4">Operating Income</h6>
            <table class="table table-sm">
                @foreach($incomeAccounts as $account)
                    @php
                        $balance = $account->ledgerEntries->where('type', 'credit')->sum('amount') - $account->ledgerEntries->where('type', 'debit')->sum('amount');
                    @endphp
                    <tr>
                        <td>{{ $account->name }}</td>
                        <td class="text-end">${{ number_format($balance, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="fw-bold border-top">
                    <td>Total Income</td>
                    <td class="text-end">${{ number_format($totalIncome, 2) }}</td>
                </tr>
            </table>

            {{-- Expenses Section --}}
            <h6 class="mt-4">Operating Expenses</h6>
            <table class="table table-sm">
                @foreach($expenseAccounts as $account)
                    @php
                        $balance = $account->ledgerEntries->where('type', 'debit')->sum('amount') - $account->ledgerEntries->where('type', 'credit')->sum('amount');
                    @endphp
                    <tr>
                        <td>{{ $account->name }}</td>
                        <td class="text-end">(${{ number_format($balance, 2) }})</td>
                    </tr>
                @endforeach
                <tr class="fw-bold border-top">
                    <td>Total Expenses</td>
                    <td class="text-end">(${{ number_format($totalExpenses, 2) }})</td>
                </tr>
            </table>

            {{-- Net Profit/Loss --}}
            <table class="table mt-4">
                <tr class="fw-bold fs-5 bg-light">
                    <td>Net Profit / (Loss)</td>
                    <td class="text-end">${{ number_format($netProfit, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
