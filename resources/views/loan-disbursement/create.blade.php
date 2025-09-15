@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Disburse Loan</h6>
            <p class="mb-4">You are about to disburse a loan for <strong>{{ $loanApplication->member->name }}</strong>. This will create an active loan account and generate the full repayment schedule. This action cannot be undone.</p>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="card-title">Loan Details</h6>
                    <table class="table table-sm table-borderless">
                        <tr><th style="width: 40%;">Principal Amount:</th><td>${{ number_format($loanApplication->requested_amount, 2) }}</td></tr>
                        <tr><th>Loan Product:</th><td>{{ $loanApplication->loanProduct->name }}</td></tr>
                        <tr><th>Interest Rate:</th><td>{{ $loanApplication->loanProduct->interest_rate }}% ({{ ucfirst($loanApplication->loanProduct->interest_method) }})</td></tr>
                        <tr><th>Term:</th><td>{{ $loanApplication->requested_term }} {{ ucfirst($loanApplication->loanProduct->repayment_frequency) }} installments</td></tr>
                    </table>
                </div>
            </div>

            <hr>

            <form class="forms-sample" method="POST" action="{{ route('loan-disbursement.store', $loanApplication->id) }}">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="disbursement_date" class="form-label">Disbursement Date</label>
                        <input type="date" class="form-control @error('disbursement_date') is-invalid @enderror" id="disbursement_date" name="disbursement_date" value="{{ old('disbursement_date', now()->format('Y-m-d')) }}" required>
                        @error('disbursement_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="processing_fee" class="form-label">Processing Fee Collected (if any)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control @error('processing_fee') is-invalid @enderror" id="processing_fee" name="processing_fee" value="{{ old('processing_fee', 0) }}">
                            </div>
                            @error('processing_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">Enter the fee amount collected separately from the member.</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="cash_account_id" class="form-label">Cash/Bank Account</label>
                        <select name="cash_account_id" class="form-select" required>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>



                <button type="submit" class="btn btn-primary me-2">Confirm and Disburse Loan</button>
                <a href="{{ route('loan-applications.show', $loanApplication->id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
