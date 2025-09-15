@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Create New Savings Product</h6>
            <form class="forms-sample" method="POST" action="{{ route('organization.savings-products.store') }}">
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
                        <label for="interest_rate" class="form-label">Annual Interest Rate (%)</label>
                        <input type="number" step="0.01" class="form-control @error('interest_rate') is-invalid @enderror" id="interest_rate" name="interest_rate" value="{{ old('interest_rate') }}" required>
                        @error('interest_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="interest_posting_frequency" class="form-label">Interest Posting Frequency</label>
                        <select class="form-select @error('interest_posting_frequency') is-invalid @enderror" id="interest_posting_frequency" name="interest_posting_frequency" required>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="semi_annually">Semi-Annually</option>
                            <option value="annually">Annually</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="min_balance_for_interest" class="form-label">Minimum Balance to Earn Interest</label>
                    <input type="number" step="0.01" class="form-control @error('min_balance_for_interest') is-invalid @enderror" id="min_balance_for_interest" name="min_balance_for_interest" value="{{ old('min_balance_for_interest', 0) }}" required>
                    @error('min_balance_for_interest')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary me-2">Create Product</button>
                <a href="{{ route('organization.savings-products.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
