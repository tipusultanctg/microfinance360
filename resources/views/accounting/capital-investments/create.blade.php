@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Record New Capital / Investment</h6>
            <form class="forms-sample" method="POST" action="{{ route('accounting.capital-investments.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="investment_date" class="form-label">Date of Investment</label>
                        <input type="date" name="investment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" name="amount" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="asset_account_id" class="form-label">Capital Deposited To (Asset Account)</label>
                        <select class="form-select" name="asset_account_id" required>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="equity_account_id" class="form-label">Source of Capital (Equity Account)</label>
                        <select class="form-select" name="equity_account_id" required>
                            @foreach($equityAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description / Memo</label>
                    <input type="text" class="form-control" name="description" placeholder="e.g., Initial investment from founder">
                </div>

                <button type="submit" class="btn btn-primary me-2">Record Investment</button>
                <a href="{{ route('accounting.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
