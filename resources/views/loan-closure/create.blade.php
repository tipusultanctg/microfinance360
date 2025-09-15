@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Loan Foreclosure Confirmation</h6>
            <p class="mb-4">You are about to close the loan for <strong>{{ $loanAccount->member->name }}</strong>. This will record a final payment for the entire outstanding balance.</p>

            <div class="alert alert-warning" role="alert">
                Please confirm that you have collected the full closing amount before proceeding. This action cannot be undone.
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="card-title">Closure Details</h6>
                    <table class="table table-sm table-borderless">
                        <tr><th style="width: 50%;">Account Number:</th><td>{{ $loanAccount->account_number }}</td></tr>
                        <tr><th style="width: 50%;">Current Outstanding Balance:</th><td class="fs-4 fw-bold">${{ number_format($closingAmount, 2) }}</td></tr>
                    </table>
                </div>
            </div>

            <hr>

            <form class="forms-sample" method="POST" action="{{ route('loan-closure.store', $loanAccount->id) }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="payment_date" class="form-label">Final Payment Date</label>
                        <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary me-2">Confirm and Close Loan</button>
                <a href="{{ route('loan-accounts.show', $loanAccount->id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
