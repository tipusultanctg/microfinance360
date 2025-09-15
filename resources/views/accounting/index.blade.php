@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Accounting Dashboard</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Common Actions</h6>
                    <p class="text-muted mb-4">Use these shortcuts for frequent accounting tasks.</p>

                    <a href="{{ route('accounting.manual-entries.create') }}" class="btn btn-lg btn-outline-primary me-2 mb-2">
                        <i class="btn-icon-prepend bi-journal-plus"></i>
                        Create Manual Journal Entry
                    </a>

                    {{-- --- THE NEW, GUIDED ACTION BUTTON --- --}}
                    <a href="{{ route('accounting.capital-investments.create') }}" class="btn btn-lg btn-success me-2 mb-2">
                        <i class="btn-icon-prepend bi-cash-stack"></i>
                        Add Capital / Investment
                    </a>

                    <a href="{{ route('accounting.expenses.create') }}" class="btn btn-lg btn-outline-danger me-2 mb-2">
                        <i class="btn-icon-prepend bi-wallet2"></i>
                        Record an Expense
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Financial Statements & Ledgers</h6>
                    <a href="{{ route('accounting.general-ledger') }}" class="btn btn-outline-secondary me-2 mb-2">General Ledger</a>
                    <a href="{{ route('accounting.trial-balance') }}" class="btn btn-outline-secondary me-2 mb-2">Trial Balance</a>
                    <a href="{{ route('accounting.income-statement') }}" class="btn btn-outline-secondary me-2 mb-2">Income Statement</a>
                    <a href="{{ route('accounting.balance-sheet') }}" class="btn btn-outline-secondary me-2 mb-2">Balance Sheet</a>
                </div>
            </div>
        </div>
    </div>
@endsection
