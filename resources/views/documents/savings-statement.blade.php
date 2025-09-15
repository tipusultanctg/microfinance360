<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Savings Account Statement - {{ $savingsAccount->account_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        /* ... (Use similar styles as the Loan Agreement) ... */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-end { text-align: right; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $tenant->name }}</h1>
        <p>Savings Account Statement</p>
    </div>
    <div class="section">
        <p><strong>Member:</strong> {{ $savingsAccount->member->name }}</p>
        <p><strong>Account Number:</strong> {{ $savingsAccount->account_number }}</p>
        <p><strong>Statement Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</p>
        <p><strong>Current Balance:</strong> ${{ number_format($savingsAccount->balance, 2) }}</p>
    </div>
    <div class="section">
        <div class="section-title">Transaction History</div>
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th class="text-end">Debit (-)</th>
                <th class="text-end">Credit (+)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_date->format('d M, Y') }}</td>
                    <td>{{ ucfirst($transaction->type) . ($transaction->description ? ': ' . $transaction->description : '') }}</td>
                    <td class="text-end text-danger">
                        @if($transaction->type === 'withdrawal')
                            ${{ number_format($transaction->amount, 2) }}
                        @endif
                    </td>
                    <td class="text-end text-success">
                        @if(in_array($transaction->type, ['deposit', 'interest']))
                            ${{ number_format($transaction->amount, 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
