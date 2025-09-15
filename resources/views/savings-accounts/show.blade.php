@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Account Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('savings-accounts.index') }}">Savings Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $savingsAccount->account_number }}</li>
                </ol>
            </nav>
        </div>
        @if($savingsAccount->status === 'active')
            <div class="d-flex justify-content-end align-items-center flex-wrap text-nowrap">
                <a href="{{ route('savings-closure.create', $savingsAccount->id) }}" class="btn btn-danger btn-icon-text">
                    <i class="btn-icon-prepend bi-door-closed"></i>
                    Close Account
                </a>
            </div>
        @elseif($savingsAccount->status === 'closed')
            @php
                // This query is fine for a view. It finds the closure record.
                $closureRecord = \App\Models\SavingsClosure::where('savings_account_id', $savingsAccount->id)->first();
            @endphp
            @if($closureRecord)
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <span>This account was closed on {{ $closureRecord->closure_date->format('d M, Y') }}.</span>

                    {{-- This form correctly points to our new route --}}
                    <form method="POST" action="{{ route('savings-closure.destroy', $closureRecord->id) }}" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning btn-sm">Revert Closure</button>
                    </form>
                </div>
            @endif
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        {{-- Account Summary --}}
        <div class="col-md-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="card-title mb-0">Account Summary</h6>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-muted">Member:</p>
                            <p class="font-weight-bold">{{ $savingsAccount->member->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted">Account Number:</p>
                            <p class="font-weight-bold">{{ $savingsAccount->account_number }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted">Product:</p>
                            <p class="font-weight-bold">{{ $savingsAccount->savingsProduct->name }}</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <p class="text-muted">Current Balance:</p>
                            <h4 class="font-weight-bold">${{ number_format($savingsAccount->balance, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <form method="GET" action="{{ route('documents.savings-statement', $savingsAccount->id) }}" target="_blank">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Statement Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $savingsAccount->created_at->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">Statement End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-info btn-icon-text">
                            <i class="btn-icon-prepend bi-file-earmark-pdf"></i>
                            Generate Statement
                        </button>
                    </div>
                </div>
            </form>

        </div>

        {{-- Transaction Forms --}}
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="deposit-tab" data-bs-toggle="tab" href="#deposit" role="tab" aria-controls="deposit" aria-selected="true">Deposit</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="withdrawal-tab" data-bs-toggle="tab" href="#withdrawal" role="tab" aria-controls="withdrawal" aria-selected="false">Withdrawal</a>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 p-3" id="myTabContent">
                        {{-- Deposit Form --}}
                        <div class="tab-pane fade show active" id="deposit" role="tabpanel" aria-labelledby="deposit-tab">
                            <h6 class="mb-3">Make a Deposit</h6>
                            <form method="POST" action="{{ route('savings-transactions.deposit', $savingsAccount->id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="deposit-amount" class="form-label">Amount</label>
                                    <input type="number" step="0.01" name="amount" class="form-control @error('amount', 'deposit') is-invalid @enderror" required>
                                    @error('amount', 'deposit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="cash_account_id" class="form-label">Cash/Bank Account</label>
                                    <select name="cash_account_id" class="form-select" required>
                                        @foreach($cashAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="deposit-description" class="form-label">Description (Optional)</label>
                                    <input type="text" name="description" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Deposit</button>
                            </form>
                        </div>
                        {{-- Withdrawal Form --}}
                        <div class="tab-pane fade" id="withdrawal" role="tabpanel" aria-labelledby="withdrawal-tab">
                            <h6 class="mb-3">Make a Withdrawal</h6>
                            <form method="POST" action="{{ route('savings-transactions.withdrawal', $savingsAccount->id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="withdrawal-amount" class="form-label">Amount</label>
                                    <input type="number" step="0.01" name="amount" class="form-control @error('amount', 'withdrawal') is-invalid @enderror" required>
                                    @error('amount', 'withdrawal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="cash_account_id" class="form-label">Cash/Bank Account</label>
                                    <select name="cash_account_id" class="form-select" required>
                                        @foreach($cashAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="withdrawal-description" class="form-label">Description (Optional)</label>
                                    <input type="text" name="description" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-warning">Submit Withdrawal</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaction History (Ledger) --}}
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Transaction History</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Processed By</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($savingsAccount->transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_date->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($transaction->type === 'deposit')
                                            <span class="badge bg-success">Deposit</span>
                                        @elseif($transaction->type === 'withdrawal')
                                            <span class="badge bg-danger">Withdrawal</span>
                                        @else
                                            <span class="badge bg-info">Interest</span>
                                        @endif
                                    </td>
                                    <td>${{ number_format($transaction->amount, 2) }}</td>
                                    <td>{{ $transaction->user->name ?? 'System' }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('savings-transactions.receipt', $transaction->id) }}" class="btn btn-xs btn-outline-info me-2" title="View Receipt">
                                                <i class="bi-receipt"></i>
                                            </a>

                                            @role('Organization Admin')
                                            <form action="{{ route('organization.savings-transactions.destroy', $transaction->id) }}" method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger btn-icon" title="Reverse Transaction">
                                                    <i data-lucide="x"></i>
                                                </button>
                                            </form>
                                            @endrole
                                        </div>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No transactions found for this account.</td>
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
