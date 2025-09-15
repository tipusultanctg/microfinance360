@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Create New Subscription Plan</h6>
                    <form class="forms-sample" method="POST" action="{{ route('super-admin.subscription-plans.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Plan Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (per month)</label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="member_limit" class="form-label">Member Limit</label>
                            <input type="number" class="form-control @error('member_limit') is-invalid @enderror" id="member_limit" name="member_limit" value="{{ old('member_limit') }}">
                            <small class="form-text text-muted">Leave blank for unlimited members.</small>
                        </div>
                        <div class="mb-3">
                            <label for="loan_limit" class="form-label">Loan Limit (Monthly)</label>
                            <input type="number" class="form-control @error('loan_limit') is-invalid @enderror" id="loan_limit" name="loan_limit" value="{{ old('loan_limit') }}">
                            <small class="form-text text-muted">Leave blank for unlimited loans.</small>
                        </div>
                        <div class="mb-3">
                            <label for="features" class="form-label">Features</label>
                            <textarea class="form-control" id="features" name="features" rows="5">{{ old('features') }}</textarea>
                            <small class="form-text text-muted">Enter each feature on a new line.</small>
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Create Plan</button>
                        <a href="{{ route('super-admin.subscription-plans.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
