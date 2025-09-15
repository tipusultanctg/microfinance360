@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Loan Accounts</h4>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title">Filter Loans</h6>
            <form method="GET" action="{{ route('loan-accounts.index') }}">
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search by Account # or Member Name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-5">
                        <select name="status" class="form-select">
                            <option value="all">All Statuses</option>
                            <option value="active" @selected(request('status') == 'active')>Active</option>
                            <option value="paid" @selected(request('status') == 'paid')>Paid</option>
                            <option value="overdue" @selected(request('status') == 'overdue')>Overdue</option>
                            <option value="closed" @selected(request('status') == 'closed')>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Account #</th>
                        <th>Member Name</th>
                        <th>Principal</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($accounts as $account)
                        <tr>
                            <td>{{ $account->account_number }}</td>
                            <td>{{ $account->member->name ?? 'N/A' }}</td>
                            <td>${{ number_format($account->principal_amount, 2) }}</td>
                            <td>${{ number_format($account->amount_paid, 2) }}</td>
                            <td>${{ number_format($account->balance, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $account->status === 'active' ? 'success' : 'info' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('loan-accounts.show', $account->id) }}" class="btn btn-sm btn-outline-info">View Details</a>
                                @role('Organization Admin')
                                <form action="{{ route('loan-accounts.destroy', $account->id) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                                @endrole
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No active loans found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $accounts->links() }}
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
                        text: "This will permanently delete the loan account and ALL its repayment data!",
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


