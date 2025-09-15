@extends('layouts.master')

@push('plugin-styles')
    <link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Members</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <a href="{{ route('members.create') }}" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                <i class="btn-icon-prepend bi-plus-lg"></i>
                Add New Member
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Member ID</th>
                        <th>Name</th>
                        <th>Branch</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>
                                <img src="{{ $member->getFirstMediaUrl('member_photo', 'thumb') ?: 'https://placehold.co/50' }}" alt="photo" class="wd-50 ht-50 rounded-circle">
                            </td>
                            <td>{{ $member->member_uid }}</td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->branch->name ?? 'N/A' }}</td>
                            <td>{{ $member->phone ?? 'N/A' }}</td>
                            <td><span class="badge bg-success">{{ ucfirst($member->status) }}</span></td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('members.show', $member->id) }}" class="btn btn-sm btn-outline-info me-2">View</a>
                                    <a href="{{ route('members.edit', $member->id) }}" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                    <form action="{{ route('members.destroy', $member->id) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No members found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $members->links() }}
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

@endpush

@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
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
