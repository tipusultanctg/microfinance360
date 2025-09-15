@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Create New Loan Product</h6>
            <form class="forms-sample" method="POST" action="{{ route('organization.loan-products.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="max_loan_amount" class="form-label">Maximum Loan Amount (Optional)</label>
                        <input type="number" step="0.01" class="form-control @error('max_loan_amount') is-invalid @enderror" id="max_loan_amount" name="max_loan_amount" value="{{ old('interest_rate') }}" required>
                        @error('max_loan_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="interest_rate" class="form-label">Annual Interest Rate (%)</label>
                        <input type="number" step="0.01" class="form-control @error('interest_rate') is-invalid @enderror" id="interest_rate" name="interest_rate" value="{{ old('interest_rate') }}" required>
                        @error('interest_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="interest_method" class="form-label">Interest Method</label>
                        <select class="form-select @error('interest_method') is-invalid @enderror" id="interest_method" name="interest_method" required>
                            <option value="flat">Flat Rate</option>
                            <option value="reducing">Reducing Balance</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="repayment_frequency" class="form-label">Repayment Frequency</label>
                        <select class="form-select @error('repayment_frequency') is-invalid @enderror" id="repayment_frequency" name="repayment_frequency" required>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="max_loan_term" class="form-label">Max Loan Term (in installments)</label>
                        <input type="number" class="form-control @error('max_loan_term') is-invalid @enderror" id="max_loan_term" name="max_loan_term" value="{{ old('max_loan_term') }}" required>
                        <small class="form-text text-muted">e.g., 12 for 12 months, 52 for 52 weeks</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="late_payment_fee" class="form-label">Late Payment Fee</label>
                    <input type="number" step="0.01" class="form-control @error('late_payment_fee') is-invalid @enderror" id="late_payment_fee" name="late_payment_fee" value="{{ old('late_payment_fee', 0) }}" required>
                </div>

                <button type="submit" class="btn btn-primary me-2">Create Product</button>
                <a href="{{ route('organization.loan-products.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script>
        document.getElementById('processing_fee_type').addEventListener('change', function() {
            const addon = document.getElementById('fee-addon');
            if (this.value === 'percentage') {
                addon.textContent = '%';
            } else {
                addon.textContent = '$';
            }
        });
        // Trigger change on page load for the edit form
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => document.getElementById('processing_fee_type').dispatchEvent(new Event('change')));
        } else {
            document.getElementById('processing_fee_type').dispatchEvent(new Event('change'));
        }
    </script>
@endpush
