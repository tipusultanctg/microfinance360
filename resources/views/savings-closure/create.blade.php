@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Savings Account Closure</h6>
            <p class="mb-4">You are about to close the savings account for <strong>{{ $savingsAccount->member->name }}</strong>. This will process a final withdrawal for the entire balance plus any final interest/profit entered.</p>

            <div class="alert alert-danger" role="alert">
                This action is final and cannot be undone. It will set the account balance to zero and change the status to "Closed".
            </div>

            <form class="forms-sample" method="POST" action="{{ route('savings-closure.store', $savingsAccount->id) }}" id="closure-form">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="card-title">Closure Calculation</h6>
                        <table class="table table-sm table-borderless">
                            <tr><th style="width: 50%;">Current Balance:</th><td class="fs-5">${{ number_format($savingsAccount->balance, 2) }}</td></tr>
                            <tr><th>Final Interest/Profit (+):</th><td class="fs-5 text-success" id="interest-display">$0.00</td></tr>
                            <tr class="border-top"><th class="fs-4">Total to Withdraw:</th><td class="fs-4 fw-bold" id="total-display">${{ number_format($savingsAccount->balance, 2) }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="card-title">Transaction Details</h6>
                        <div class="mb-3">
                            <label for="final_interest" class="form-label">Final Interest/Profit Amount (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control @error('final_interest') is-invalid @enderror" id="final_interest" name="final_interest" value="{{ old('final_interest', 0) }}">
                            </div>
                            @error('final_interest')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="cash_account_id" class="form-label">Paid Out From (Cash/Bank Account) <span class="text-danger">*</span></label>
                            <select name="cash_account_id" class="form-select @error('cash_account_id') is-invalid @enderror" required>
                                @foreach($cashAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                            @error('cash_account_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <input type="text" name="description" class="form-control" placeholder="e.g., Member request for closure">
                        </div>
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-danger me-2">Confirm and Close Account</button>
                <a href="{{ route('savings-accounts.show', $savingsAccount->id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    @push('custom-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const interestInput = document.getElementById('final_interest');
                const interestDisplay = document.getElementById('interest-display');
                const totalDisplay = document.getElementById('total-display');
                const currentBalance = {{ $savingsAccount->balance }};

                function updateTotals() {
                    const interest = parseFloat(interestInput.value) || 0;
                    const total = currentBalance + interest;

                    interestDisplay.textContent = '$' + interest.toFixed(2);
                    totalDisplay.textContent = '$' + total.toFixed(2);
                }

                interestInput.addEventListener('input', updateTotals);
            });
        </script>
    @endpush
@endsection
