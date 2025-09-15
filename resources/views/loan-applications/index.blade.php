@extends('layouts.master')

@push('plugin-styles')
    <link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Loan Applications</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <a href="{{ route('loan-applications.create') }}" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-plus-lg"></i>
                New Application
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Member Name</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Term</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($applications as $app)
                        <tr>
                            <td>{{ $app->member->name ?? 'N/A' }}</td>
                            <td>{{ $app->loanProduct->name ?? 'N/A' }}</td>
                            <td>${{ number_format($app->requested_amount, 2) }}</td>
                            <td>{{ $app->requested_term }} installments</td>
                            <td>
                                @php
                                    $statusClass = match($app->status) {
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'disbursed' => 'success',
                                        'rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($app->status) }}</span>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('loan-applications.show', $app->id) }}" class="btn btn-sm btn-outline-info me-2">View</a>
                                    @if($app->status == 'pending')
                                        <a href="{{ route('loan-applications.edit', $app->id) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                    @endif
                                    @if($app->status == 'pending' || $app->status == 'rejected')
                                        <form action="{{ route('loan-applications.destroy', $app->id) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No loan applications found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
    {{-- Re-use the same sweet alert script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?', text: "This action cannot be undone!", icon: 'warning', showCancelButton: true,
                        confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!'
                    }).then((result) => { if (result.isConfirmed) { form.submit(); } });
                });
            });
        });
    </script>
@endpush
