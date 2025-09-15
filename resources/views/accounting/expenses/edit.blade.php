@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Edit Expense #{{ $expense->id }}</h6>
            <form class="forms-sample" method="POST" action="{{ route('accounting.expenses.update', $expense->id) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="chart_of_account_id" class="form-label">Expense Category</label>
                        <select class="form-select @error('chart_of_account_id') is-invalid @enderror" id="chart_of_account_id" name="chart_of_account_id" required>
                            <option value="" disabled>Select a category...</option>
                            @foreach ($expenseCategories as $category)
                                <option value="{{ $category->id }}" @selected(old('chart_of_account_id', $expense->chart_of_account_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('chart_of_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $expense->amount) }}" required>
                        </div>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="expense_date" class="form-label">Date of Expense</label>
                        <input type="date" class="form-control @error('expense_date') is-invalid @enderror" id="expense_date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                        @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <input type="text" class="form-control" name="description" id="description" value="{{ old('description', $expense->description) }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                <a href="{{ route('accounting.expenses.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
