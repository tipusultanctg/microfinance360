<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Loan Agreement - {{ $loanAccount->account_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; color: #000; }
        .header p { margin: 0; font-size: 14px; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .summary-table th { width: 40%; }
        .schedule-table { font-size: 10px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #777; }
        .signature-section { margin-top: 60px; }
        .signature-line { border-bottom: 1px solid #000; width: 250px; margin-top: 40px; }
        .signature-box { display: inline-block; width: 45%; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $tenant->name }}</h1>
        <p>Loan Agreement</p>
    </div>

    <div class="section">
        <div class="section-title">1. Parties Involved</div>
        <p>This Loan Agreement ("Agreement") is made on {{ $loanAccount->disbursement_date->format('F jS, Y') }} between:</p>
        <ul>
            <li><strong>The Lender:</strong> {{ $tenant->name }}</li>
            <li><strong>The Borrower:</strong> {{ $loanAccount->member->name }} (Member ID: {{ $loanAccount->member->member_uid }})</li>
        </ul>
    </div>

    <div class="section">
        <div class="section-title">2. Loan Terms</div>
        <table class="summary-table">
            <tr>
                <th>Loan Account Number</th>
                <td>{{ $loanAccount->account_number }}</td>
            </tr>
            <tr>
                <th>Principal Amount</th>
                <td>${{ number_format($loanAccount->principal_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Processing Fee Paid</th>
                <td>${{ number_format($loanAccount->processing_fee, 2) }}</td>
            </tr>
            <tr>
                <th>Annual Interest Rate</th>
                <td>{{ $loanAccount->loanProduct->interest_rate }}% ({{ ucfirst($loanAccount->loanProduct->interest_method) }} Rate)</td>
            </tr>
            <tr>
                <th>Total Interest Payable</th>
                <td>${{ number_format($loanAccount->total_interest, 2) }}</td>
            </tr>
            <tr style="background-color: #eee; font-weight: bold;">
                <th>Total Amount Repayable</th>
                <td>${{ number_format($loanAccount->total_payable, 2) }}</td>
            </tr>
            <tr>
                <th>Loan Term</th>
                <td>{{ $loanAccount->term }} {{ ucfirst($loanAccount->loanProduct->repayment_frequency) }} Installments</td>
            </tr>
            <tr>
                <th>Disbursement Date</th>
                <td>{{ $loanAccount->disbursement_date->format('d M, Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">3. Repayment Schedule</div>
        <table class="schedule-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Principal</th>
                <th>Interest</th>
                <th>Total Due</th>
            </tr>
            </thead>
            <tbody>
            @foreach($loanAccount->schedule as $installment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $installment->due_date->format('d M, Y') }}</td>
                    <td>${{ number_format($installment->principal_amount, 2) }}</td>
                    <td>${{ number_format($installment->interest_amount, 2) }}</td>
                    <td>${{ number_format($installment->total_amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">4. Terms and Conditions</div>
        <p>The borrower agrees to repay the total amount as per the schedule above. A late payment fee of ${{ number_format($loanAccount->loanProduct->late_payment_fee, 2) }} will be applied for each missed installment. This agreement is governed by the laws of the land.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Signature of Borrower</p>
            <p>({{ $loanAccount->member->name }})</p>
        </div>
        <div class="signature-box" style="float: right;">
            <div class="signature-line"></div>
            <p>Signature of Authorized Officer</p>
            <p> (For {{ $tenant->name }})</p>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document.</p>
    </div>
</div>
</body>
</html>
