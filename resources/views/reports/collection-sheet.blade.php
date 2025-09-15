@extends('layouts.master')
@push('style')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .card, .card * {
                visibility: visible;
            }
            .card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none !important;
                box-shadow: none !important;
            }
            .d-flex, .page-breadcrumb {
                display: none !important;
            }
        }
    </style>
@endpush
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Daily Collection Sheet</h4>
            <p class="text-muted">For date: {{ $today->format('d M, Y') }}</p>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0" onclick="window.print()">
                <i class="btn-icon-prepend bi-printer"></i>
                Print Report
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Member Name</th>
                        <th>Loan Account #</th>
                        <th>Due Date</th>
                        <th>Amount Due</th>
                        <th class="text-center">Collected (✓)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $totalDue = 0;
                    @endphp
                    @forelse ($dueInstallments as $installment)
                        @php
                            $totalDue += $installment->total_amount;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $installment->loanAccount->member->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('loan-accounts.show', $installment->loanAccount->id) }}">
                                    {{ $installment->loanAccount->account_number ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                {{ $installment->due_date->format('d M, Y') }}
                                @if($installment->due_date->isPast())
                                    <span class="badge bg-danger">Overdue</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">${{ number_format($installment->total_amount, 2) }}</td>
                            <td style="width: 15%;"></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No collections are due today or overdue.</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="bg-light">
                        <td colspan="4" class="text-end fw-bold">Total Expected Collection:</td>
                        <td class="text-end fw-bold fs-5">${{ number_format($totalDue, 2) }}</td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection


