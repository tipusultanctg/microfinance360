@extends('layouts.master')
@push('plugin-styles')
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Unified Collection Center</h4>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row">
        {{-- ======================================================= --}}
        {{-- MEMBER SNAPSHOT COLUMN --}}
        {{-- ======================================================= --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <label for="member_id" class="form-label">Select Member</label>
                    <select id="member_id" name="member_id" class="form-select w-100" data-placeholder="Select Member" data-allow-clear="on">
                        <option value="">Search for a member...</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->member_uid }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- The Member Snapshot card will be injected here by JavaScript --}}
            <div id="member-snapshot-container" class="mt-3"></div>
        </div>
        {{-- ======================================================= --}}
        {{-- TRANSACTION FORM COLUMN --}}
        {{-- ======================================================= --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form id="collection-form" method="POST" action="{{ route('collection-center.store') }}">
                        @csrf
                        {{-- Hidden input to store the selected member ID for the form submission --}}
                        <input type="hidden" id="form_member_id" name="member_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Transaction Date</label>
                                <input type="date" name="date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cash_account_id" class="form-label">Cash/Bank Account</label>
                                <select name="cash_account_id" class="form-select" required>
                                    @foreach($cashAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        {{-- Savings Section --}}
                        <div class="border p-3 mb-3" id="savings-section" style="display: none;">
                            <h5>Savings Transactions</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="savings_account_id" class="form-label">Savings Account</label>
                                    <select id="savings_account_id" name="savings_account_id" class="form-select"></select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="savings_deposit_amount" class="form-label">Deposit Amount</label>
                                    <input type="number" step="0.01" name="savings_deposit_amount" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="savings_withdrawal_amount" class="form-label">Withdrawal Amount</label>
                                    <input type="number" step="0.01" name="savings_withdrawal_amount" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div id="no-savings-msg" class="text-muted text-center p-3">Select a member to see savings options.</div>

                        {{-- Loan Section --}}
                        <div class="border p-3 mb-3" id="loan-section" style="display: none;">
                            <h5>Loan Repayment</h5>
                            <div class="mb-3">
                                <label for="loan_account_id" class="form-label">Loan Account</label>
                                <select id="loan_account_id" name="loan_account_id" class="form-select"></select>
                            </div>
                            <div class="mb-3">
                                <label for="loan_repayment_amount" class="form-label">Repayment Amount</label>
                                <input type="number" step="0.01" name="loan_repayment_amount" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                        <div id="no-loan-msg" class="text-muted text-center p-3">Select a member to see loan options.</div>

                        <hr>
                        <div class="mb-3">
                            <label for="note" class="form-label">Note (Optional)</label>
                            <input type="text" name="note" class="form-control" placeholder="Add a note for all transactions...">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Process Transactions</button>
                    </form>
                </div>
            </div>
        </div>



    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h6 class="card-title">Today's Collections ({{ now()->format('d M, Y') }})</h6>

            {{-- --- NEW TABS --- --}}
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="savings-tab" data-bs-toggle="tab" href="#savingsTransaction" role="tab"
                       aria-controls="savings" aria-selected="true">
                        Savings Transactions ({{ $todaysSavingsTransactions->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="loans-tab" data-bs-toggle="tab" href="#loansTransaction" role="tab"
                       aria-controls="loans" aria-selected="false">
                        Loan Repayments ({{ $todaysLoanRepayments->count() }})
                    </a>
                </li>
            </ul>

            <div class="tab-content border border-top-0 p-3" id="myTabContent">
                {{-- Savings Tab Content --}}
                <div class="tab-pane fade show active" id="savingsTransaction" role="tabpanel" aria-labelledby="savings-tab">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Time</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Collected By</th>
                                <th class="text-end">Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($todaysSavingsTransactions as $txn)
                                <tr>
                                    <td>{{ $txn->transaction_date->format('h:i A') }}</td>
                                    <td>{{ $txn->savingsAccount->member->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($txn->type === 'deposit')
                                            <span class="badge bg-success">Deposit</span>
                                        @else
                                            <span class="badge bg-danger">Withdrawal</span>
                                        @endif
                                    </td>
                                    <td>{{ $txn->user->name ?? 'N/A' }}</td>
                                    <td class="text-end">${{ number_format($txn->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No savings transactions recorded today.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Loan Repayments Tab Content --}}
                <div class="tab-pane fade" id="loansTransaction" role="tabpanel" aria-labelledby="loans-tab">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Time</th>
                                <th>Member</th>
                                <th>Collected By</th>
                                <th class="text-end">Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($todaysLoanRepayments as $repayment)
                                <tr>
                                    <td>{{ $repayment->payment_date->format('h:i A') }}</td>
                                    <td>{{ $repayment->loanAccount->member->name ?? 'N/A' }}</td>
                                    <td>{{ $repayment->user->name ?? 'N/A' }}</td>
                                    <td class="text-end">${{ number_format($repayment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No loan repayments recorded today.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
        $(document).ready(function() {
            const memberSelect = $('#member_id');
            const formMemberIdInput = $('#form_member_id');
            const snapshotContainer = $('#member-snapshot-container');

            // Initialize Select2 on the member dropdown
            memberSelect.select2({
                placeholder: "Search for a member...",
                width: '100%'
            });

            // Event listener for when a member is selected
            memberSelect.on('change', function() {
                const memberId = $(this).val();

                // Update the hidden input in the form
                formMemberIdInput.val(memberId);

                // --- Reset the entire UI ---
                snapshotContainer.empty();
                $('#savings-section').hide();
                $('#loan-section').hide();
                $('#no-savings-msg').show();
                $('#no-loan-msg').show();
                // Clear form inputs
                $('#collection-form')[0].reset();
                // Restore hidden member ID and date
                formMemberIdInput.val(memberId);
                $('input[name="date"]').val('{{ now()->format("Y-m-d") }}');


                if (!memberId) {
                    return;
                }

                // --- Make a single API call to get all member data ---
                $.ajax({
                    url: `/api/members/${memberId}/accounts`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {

                        // --- 1. Dynamically Build and Inject Snapshot Card ---
                        const snapshot = response.snapshot;
                        if (snapshot) {
                            const snapshotHtml = `
                        <div class="card" style="display: none;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <img src="${snapshot.image_url}" class="wd-100 ht-100 rounded-circle me-3" alt="Member Photo">
                                    <div>
                                        <h5 class="mb-1">${snapshot.name}</h5>
                                        <p class="text-muted mb-1"><i class="bi-phone me-1"></i><span>${snapshot.phone || 'N/A'}</span></p>
                                        <p class="text-muted mb-0"><i class="bi-geo-alt me-1"></i><span>${snapshot.address || 'N/A'}</span></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-4 border-end">
                                        <p class="text-muted mb-0">Total Savings</p>
                                        <h5 class="fw-bold">$${snapshot.total_savings}</h5>
                                    </div>
                                    <div class="col-4 border-end">
                                        <p class="text-muted mb-0">Total Loan Due</p>
                                        <h5 class="fw-bold">$${snapshot.total_loan_due}</h5>
                                    </div>
                                    <div class="col-4">
                                        <p class="text-muted mb-0">Last Transaction</p>
                                        <h5>${snapshot.last_transaction_date}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                            snapshotContainer.html(snapshotHtml);
                            snapshotContainer.find('.card').slideDown();
                        }

                        // --- 2. Populate Account Dropdowns ---
                        const accounts = response.accounts;

                        // Populate Savings Dropdown
                        const savingsSelect = $('#savings_account_id');
                        savingsSelect.empty().append(new Option('Select savings account...', ''));
                        if (accounts.savings && accounts.savings.length > 0) {
                            accounts.savings.forEach(function(acc) {
                                savingsSelect.append(new Option(`${acc.account_number} (Bal: $${acc.balance})`, acc.id));
                            });
                            $('#savings-section').show();
                            $('#no-savings-msg').hide();
                        } else {
                            $('#savings-section').hide();
                            $('#no-savings-msg').show().html('<em>No active savings accounts found.</em>');
                        }

                        // Populate Loan Dropdown
                        const loanSelect = $('#loan_account_id');
                        loanSelect.empty().append(new Option('Select loan account...', ''));
                        if (accounts.loans && accounts.loans.length > 0) {
                            accounts.loans.forEach(function(acc) {
                                loanSelect.append(new Option(`${acc.account_number} (Bal: $${acc.balance})`, acc.id));
                            });
                            $('#loan-section').show();
                            $('#no-loan-msg').hide();
                        } else {
                            $('#loan-section').hide();
                            $('#no-loan-msg').show().html('<em>No active loan accounts found.</em>');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // Instead of a blocking alert, show a clean Toastr notification
                        toastr.error('Failed to load member data. Please check the network connection and try again.');
                        console.error("AJAX Error: " + textStatus, errorThrown); // Keep the console log for debugging
                    }
                });
            });
        });
    </script>
@endpush
