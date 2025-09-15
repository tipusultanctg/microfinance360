@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Balance Sheet</h4>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title">Filter Balance Sheet</h6>
            <form method="GET" action="{{ route('accounting.balance-sheet') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="end_date" class="form-label">As of Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $asOfDate }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('accounting.balance-sheet') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header text-center">
            <h5>{{ auth()->user()->tenant->name }}</h5>
            <h6>Balance Sheet</h6>
            <p class="text-muted">As of {{ \Carbon\Carbon::parse($asOfDate)->format('d M, Y') }}</p>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Assets Section --}}
                <div class="col-md-6">
                    <h5 class="mb-3">Assets</h5>
                    <table class="table table-sm">
                        @php $totalAssets = 0; @endphp
                        @foreach($assets as $account)
                            @php
                                $balance = $account->ledgerEntries->where('type', 'debit')->sum('amount') - $account->ledgerEntries->where('type', 'credit')->sum('amount');
                                $totalAssets += $balance;
                            @endphp
                            <tr>
                                <td>{{ $account->name }}</td>
                                <td class="text-end">${{ number_format($balance, 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                    <table class="table">
                        <tr class="fw-bold fs-5 border-top border-2">
                            <td>Total Assets</td>
                            <td class="text-end">${{ number_format($totalAssets, 2) }}</td>
                        </tr>
                    </table>
                </div>

                {{-- Liabilities & Equity Section --}}
                <div class="col-md-6">
                    <h5 class="mb-3">Liabilities & Equity</h5>

                    {{-- LIABILITIES --}}
                    <h6 class="mt-4">Liabilities</h6>
                    <table class="table table-sm">
                        @php $totalLiabilities = 0; @endphp
                        @foreach($liabilities as $account)
                            @php
                                $balance = $account->ledgerEntries->where('type', 'credit')->sum('amount') - $account->ledgerEntries->where('type', 'debit')->sum('amount');
                                $totalLiabilities += $balance;
                            @endphp
                            <tr>
                                <td>{{ $account->name }}</td>
                                <td class="text-end">${{ number_format($balance, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="fw-bold border-top">
                            <td>Total Liabilities</td>
                            <td class="text-end">${{ number_format($totalLiabilities, 2) }}</td>
                        </tr>
                    </table>

                    {{-- EQUITY --}}
                    <h6 class="mt-4">Equity</h6>
                    <table class="table table-sm">
                        @php $totalEquity = 0; @endphp
                        @foreach($equityAccounts as $account)
                            @php
                                $balance = $account->ledgerEntries->where('type', 'credit')->sum('amount') - $account->ledgerEntries->where('type', 'debit')->sum('amount');
                                $totalEquity += $balance;
                            @endphp
                            <tr>
                                <td>{{ $account->name }}</td>
                                <td class="text-end">${{ number_format($balance, 2) }}</td>
                            </tr>
                        @endforeach

                        {{-- --- THE CRITICAL NEW ROW --- --}}
                        <tr>
                            <td>Retained Earnings (Net Profit/Loss)</td>
                            <td class="text-end">${{ number_format($netProfitOrLoss, 2) }}</td>
                        </tr>

                        @php
                            // Add the net profit to the total equity
                            $totalEquity += $netProfitOrLoss;
                        @endphp

                        <tr class="fw-bold border-top">
                            <td>Total Equity</td>
                            <td class="text-end">${{ number_format($totalEquity, 2) }}</td>
                        </tr>
                    </table>

                    <table class="table mt-4">
                        <tr class="fw-bold fs-5 border-top border-2">
                            <td>Total Liabilities & Equity</td>
                            <td class="text-end">${{ number_format($totalLiabilities + $totalEquity, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
