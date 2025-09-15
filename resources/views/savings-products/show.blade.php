@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">{{ $savingsProduct->name }}</h6>
            <p class="card-text">{{ $savingsProduct->description }}</p>
            <hr>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                    <tr><th>Interest Rate</th><td>{{ $savingsProduct->interest_rate }}% per annum</td></tr>
                    <tr><th>Posting Frequency</th><td>{{ ucfirst(str_replace('_', '-', $savingsProduct->interest_posting_frequency)) }}</td></tr>
                    <tr><th>Minimum Balance for Interest</th><td>${{ number_format($savingsProduct->min_balance_for_interest, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
            <a href="{{ route('organization.savings-products.index') }}" class="btn btn-secondary mt-3">Back to List</a>
        </div>
    </div>
@endsection
