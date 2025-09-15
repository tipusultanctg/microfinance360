@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Account Ledger: {{ $chartOfAccount->name }}</h4>
            <p class="text-muted">Account Type: {{ ucfirst($chartOfAccount->type) }}</p>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <a href="{{ route('accounting.chart-of-accounts.index') }}" class="btn btn-outline-secondary btn-icon-text me-2 mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-arrow-left"></i>
                Back to Chart of Accounts
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        // Note: A true running balance requires querying all historical entries,
                        // which can be slow on paginated results. This displays a "balance change" per page.
                        // For a real-time running balance, a more advanced query would be needed before the loop.
                        // Let's start with a simple balance calculation for now.
                        $balance = 0;
                        // To get the opening balance for the current page, you'd query the sum before the first item's date.
                        // For simplicity, we'll calculate running balance from the start of time here.
                        $allEntries = $chartOfAccount->ledgerEntries()->orderBy('id', 'asc')->get();
                        $runningBalance = 0;
                    @endphp

                    {{-- Displaying a running balance with pagination is complex. We will show all entries for now. --}}
                    @forelse ($chartOfAccount->ledgerEntries()->with('generalLedger')->orderBy('id', 'asc')->get() as $entry)
                        @php
                            if ($entry->type == 'debit') {
                                if (in_array($chartOfAccount->type, ['asset', 'expense'])) {
                                    $runningBalance += $entry->amount;
                                } else {
                                    $runningBalance -= $entry->amount;
                                }
                            } else { // credit
                                if (in_array($chartOfAccount->type, ['asset', 'expense'])) {
                                    $runningBalance -= $entry->amount;
                                } else {
                                    $runningBalance += $entry->amount;
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $entry->generalLedger->date->format('d M, Y') }}</td>
                            <td>{{ $entry->generalLedger->description }}</td>
                            <td class="text-end text-success">
                                @if($entry->type == 'debit')
                                    ${{ number_format($entry->amount, 2) }}
                                @endif
                            </td>
                            <td class="text-end text-danger">
                                @if($entry->type == 'credit')
                                    ${{ number_format($entry->amount, 2) }}
                                @endif
                            </td>
                            <td class="text-end fw-bold">${{ number_format($runningBalance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No entries found for this account.</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="bg-light fw-bold fs-5">
                        <td colspan="4" class="text-end">Final Balance:</td>
                        <td class="text-end">${{ number_format($runningBalance, 2) }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
