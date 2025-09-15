@extends('layouts.receipt')

@section('title', 'Transaction Receipt #' . $savingsTransaction->id)

@section('print-controls')
    <a href="{{ route('savings-accounts.show', $savingsTransaction->savingsAccount->id) }}" class="btn btn-secondary">Back to Account</a>
    <button onclick="window.print()" class="btn">Print Receipt</button>
@endsection

@section('content')
    <div class="receipt-header">
        <div class="logo-area">
            <div class="brand">{{ auth()->user()->tenant->name }}</div>
            <p>Official Transaction Receipt</p>
        </div>
        <div class="receipt-details">
            <p><strong>Transaction ID:</strong> #{{ $savingsTransaction->id }}</p>
            <p><strong>Date & Time:</strong> {{ $savingsTransaction->transaction_date->format('d M, Y h:i A') }}</p>
            <p><strong>Processed By:</strong> {{ $savingsTransaction->user->name ?? 'System' }}</p>
        </div>
    </div>

    <div class="receipt-title">
        {{ $savingsTransaction->type }} Receipt
    </div>

    <div class="receipt-body">
        <table>
            <tr>
                <th>Member Name</th>
                <td>{{ $savingsTransaction->savingsAccount->member->name }}</td>
            </tr>
            <tr>
                <th>Account Number</th>
                <td>{{ $savingsTransaction->savingsAccount->account_number }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $savingsTransaction->description ?? 'N/A' }}</td>
            </tr>
            <tr class="highlight-row">
                <td>Amount</td>
                <td>${{ number_format($savingsTransaction->amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="receipt-footer">
        <p>Thank you for your transaction. Please keep this receipt for your records.</p>
        <p>&copy; {{ date('Y') }} {{ auth()->user()->tenant->name }}. All rights reserved.</p>
    </div>
@endsection
