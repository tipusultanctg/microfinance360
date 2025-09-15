@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Edit Account: {{ $chartOfAccount->name }}</h6>

            @if($chartOfAccount->is_locked)
                <div class="alert alert-warning">
                    This is a locked system account. You can only change its name. The type cannot be modified.
                </div>
            @endif

            <form class="forms-sample" method="POST" action="{{ route('accounting.chart-of-accounts.update', $chartOfAccount->id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Account Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $chartOfAccount->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Account Type</label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required @if($chartOfAccount->is_locked) disabled @endif>
                        <option value="asset" @selected(old('type', $chartOfAccount->type) == 'asset')>Asset</option>
                        <option value="liability" @selected(old('type', $chartOfAccount->type) == 'liability')>Liability</option>
                        <option value="equity" @selected(old('type', $chartOfAccount->type) == 'equity')>Equity</option>
                        <option value="income" @selected(old('type', $chartOfAccount->type) == 'income')>Income</option>
                        <option value="expense" @selected(old('type', $chartOfAccount->type) == 'expense')>Expense</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary me-2" @if($chartOfAccount->is_locked && auth()->user()->getRoleNames()->first() !== 'Super Admin') disabled @endif>Save Changes</button>
                <a href="{{ route('accounting.chart-of-accounts.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
