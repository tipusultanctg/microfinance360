@extends('layouts.master')

@push('plugin-styles')
    <link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Capital & Investments</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <a href="{{ route('accounting.capital-investments.create') }}" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-plus-lg"></i>
                Record New Investment
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Asset Account</th>
                        <th>Equity Account</th>
                        <th>Recorded By</th>
                        <th class="text-end">Amount</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($investments as $investment)
                        <tr>
                            <td>{{ $investment->investment_date->format('d M, Y') }}</td>
                            <td>{{ $investment->description }}</td>
                            <td>{{ $investment->assetAccount->name }}</td>
                            <td>{{ $investment->equityAccount->name }}</td>
                            <td>{{ $investment->user->name }}</td>
                            <td class="text-end fw-bold">${{ number_format($investment->amount, 2) }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('accounting.capital-investments.edit', $investment->id) }}" class="btn btn-xs btn-outline-primary me-2" title="Edit">
                                        <i class="bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('accounting.capital-investments.destroy', $investment->id) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No capital investments have been recorded.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $investments->links() }}
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
        document.addEventListener('DOMContentLoaded', function () {
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will permanently delete this record and its journal entry!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
