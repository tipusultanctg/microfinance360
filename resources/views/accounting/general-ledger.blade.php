@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">General Ledger</h4>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title">Filter Ledger Entries</h6>
            <form method="GET" action="{{ route('accounting.general-ledger') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="account_id" class="form-label">Account</label>
                        <select class="form-select" name="account_id" id="account_id">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" @selected(isset($filters['account_id']) && $filters['account_id'] == $account->id)>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" name="type" id="type">
                            <option value="">Debit & Credit</option>
                            <option value="debit" @selected(isset($filters['type']) && $filters['type'] == 'debit')>Debit</option>
                            <option value="credit" @selected(isset($filters['type']) && $filters['type'] == 'credit')>Credit</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('accounting.general-ledger') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="card">
        <div class="card-header">
            Displaying entries from <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Account</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                    </tr>
                    </thead>

                    {{-- Initialize totals for the page --}}
                    @php
                        $pageTotalDebit = 0;
                        $pageTotalCredit = 0;
                    @endphp

                    @forelse ($journals as $journal)
                        <tbody class="border-top border-2">
                        @foreach ($journal->ledgerEntries as $index => $entry)

                            {{-- Add amounts to page totals --}}
                            @php
                                if ($entry->type == 'debit') $pageTotalDebit += $entry->amount;
                                if ($entry->type == 'credit') $pageTotalCredit += $entry->amount;
                            @endphp

                            <tr>
                                @if ($index === 0)
                                    <td rowspan="{{ $journal->ledgerEntries->count() }}" class="align-middle">
                                        <strong>{{ $journal->date->format('d M, Y') }}</strong>
                                    </td>
                                @endif
                                    @if ($index === 0)
                                        <td rowspan="{{ $journal->ledgerEntries->count() }}" class="align-middle">
                                            {{ $journal->description }}
                                        </td>
                                    @endif
                                <td>{{ $entry->account->name }}</td>



                                <td class="text-end">
                                    @if($entry->type == 'debit')
                                        ${{ number_format($entry->amount, 2) }}
                                    @endif
                                </td>

                                <td class="text-end">
                                    @if($entry->type == 'credit')
                                        ${{ number_format($entry->amount, 2) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    @empty
                        <tbody>
                        <tr>
                            <td colspan="5" class="text-center">No journal entries found for the selected filters.</td>
                        </tr>
                        </tbody>
                    @endforelse

                    {{-- --- NEW TABLE FOOTER WITH PAGE TOTALS --- --}}
                    <tfoot>
                    <tr class="bg-light fw-bold fs-5">
                        <td colspan="3" class="text-end">Page Totals:</td>
                        <td class="text-end">${{ number_format($pageTotalDebit, 2) }}</td>
                        <td class="text-end">${{ number_format($pageTotalCredit, 2) }}</td>
                    </tr>
                    </tfoot>

                </table>
            </div>
            <div class="mt-3">
                {{ $journals->links() }}
            </div>
        </div>
    </div>
@endsection
