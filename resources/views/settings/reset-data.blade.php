@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Reset Organization Data</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading"><i class="bi-exclamation-triangle-fill"></i> Warning: Destructive Action</h4>
                <p>You are about to perform an irreversible action. Proceeding will **permanently delete** all of the following data associated with your organization:</p>
                <hr>
                <ul>
                    <li>All **Members** and their associated documents.</li>
                    <li>All **Savings Accounts** and their complete transaction histories.</li>
                    <li>All **Loan Applications** and **Active Loans**, including their repayment schedules and histories.</li>
                    <li>All **Expense** and **Capital Investment** records.</li>
                    <li>The entire **General Ledger** and all accounting entries.</li>
                </ul>
                <p class="mb-0">The following data will **NOT** be affected: your user account, other staff accounts, branch details, created financial products, and your chart of accounts.</p>
            </div>

            <h6 class="card-title mt-4">Confirm Your Intent</h6>
            <p>To confirm that you wish to proceed, please enter your current password.</p>

            <form method="POST" action="{{ route('settings.reset.confirm') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">Your Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger btn-lg">I understand the consequences, Reset All Data</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
