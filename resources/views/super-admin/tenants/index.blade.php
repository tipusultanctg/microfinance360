@extends('layouts.master')

@push('plugin-styles')
    <link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Tenant Management</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            {{-- --- NEW BUTTON --- --}}
            <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-plus-lg"></i>
                Create New Tenant
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Subscription Plan</th> {{-- <-- NEW COLUMN --}}
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($tenants as $tenant)
                                @if($tenant->id === 1) @continue @endif {{-- Don't show the HQ tenant --}}
                                <tr>
                                    <td>{{ $tenant->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($tenant->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- --- NEW DATA --- --}}
                                        {{ $tenant->subscriptionPlan->name ?? 'None' }}
                                    </td>
                                    <td>{{ $tenant->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('super-admin.tenants.edit', $tenant->id) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                            {{-- --- NEW DELETE FORM --- --}}
                                            <form action="{{ route('super-admin.tenants.destroy', $tenant->id) }}" method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No tenants found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $tenants->links() }}
                    </div>
                </div>
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
                        title: 'Are you sure?',
                        text: "This will delete the tenant and ALL their data permanently!",
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
