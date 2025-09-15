@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Create New Account</h6>
            <form class="forms-sample" method="POST" action="{{ route('accounting.chart-of-accounts.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Account Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Account Type</label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="" disabled selected>Select a type...</option>
                        <option value="asset">Asset</option>
                        <option value="liability">Liability</option>
                        <option value="equity">Equity</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 form-check" id="is_cash_container" style="display: none;">
                    <input type="checkbox" class="form-check-input" id="is_cash_account" name="is_cash_account" value="1">
                    <label class="form-check-label" for="is_cash_account">Is this a cash or bank account?</label>
                </div>


                <button type="submit" class="btn btn-primary me-2">Create Account</button>
                <a href="{{ route('accounting.chart-of-accounts.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script>
        document.getElementById('type').addEventListener('change', function() {
            const container = document.getElementById('is_cash_container');
            if (this.value === 'asset') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
                document.getElementById('is_cash_account').checked = false;
            }
        });
    </script>
@endpush
