@extends('layouts.master')

@push('plugin-styles')
    <link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Expense Management</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <a href="{{ route('accounting.expenses.create') }}" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-plus-lg"></i>
                Record New Expense
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if (session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Recorded By</th>
                        <th class="text-end">Amount</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M, Y') }}</td>
                            <td>{{ $expense->category->name }}</td>
                            <td>{{ $expense->description }}</td>
                            <td>{{ $expense->user->name }}</td>
                            <td class="text-end fw-bold">${{ number_format($expense->amount, 2) }}</td>
                            <td>
                                <a href="{{ route('accounting.expenses.edit', $expense->id) }}" class="btn btn-xs btn-outline-primary me-2" title="Edit">
                                    <i class="bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('accounting.expenses.destroy', $expense->id) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete/Reverse">
                                        <i class="bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No expenses have been recorded.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script>
        // Standard delete confirmation script
        document.addEventListener('DOMContentLoaded', function () { /* ... */ });
    </script>
@endpush
