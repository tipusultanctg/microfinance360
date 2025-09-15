@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Edit Capital Investment #{{ $capitalInvestment->id }}</h6>
            <form class="forms-sample" method="POST" action="{{ route('accounting.capital-investments.update', $capitalInvestment->id) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="investment_date" class="form-label">Date of Investment</label>
                        <input type="date" name="investment_date" class="form-control" value="{{ old('investment_date', $capitalInvestment->investment_date->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" name="amount" value="{{ old('amount', $capitalInvestment->amount) }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="asset_account_id" class="form-label">Capital Deposited To (Asset Account)</label>
                        <select class="form-select" name="asset_account_id" required>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}" @selected(old('asset_account_id', $capitalInvestment->asset_account_id) == $account->id)>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="equity_account_id" class="form-label">Source of Capital (Equity Account)</label>
                        <select class="form-select" name="equity_account_id" required>
                            @foreach($equityAccounts as $account)
                                <option value="{{ $account->id }}" @selected(old('equity_account_id', $capitalInvestment->equity_account_id) == $account->id)>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description / Memo</label>
                    <input type="text" class="form-control" name="description" value="{{ old('description', $capitalInvestment->description) }}">
                </div>

                <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                <a href="{{ route('accounting.capital-investments.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
