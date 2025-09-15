@extends('layouts.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Welcome to your Dashboard</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-0">Total Members</h6>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <h3 class="mb-2">{{ number_format($stats['total_members']) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-0">Active Loans</h6>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <h3 class="mb-2">{{ number_format($stats['active_loans_count']) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-0">Total Loan Portfolio</h6>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <h3 class="mb-2">${{ number_format($stats['total_loan_portfolio'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-0">Total Savings Balance</h6>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <h3 class="mb-2">${{ number_format($stats['total_savings_balance'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Chart.js or ApexCharts here in the future for graphical reports -->
@endsection
