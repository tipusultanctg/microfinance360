<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>No-Dues Certificate - {{ $loanAccount->account_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 14px; color: #333; }
        .container { width: 100%; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { margin: 0; font-size: 28px; color: #000; letter-spacing: 1px;}
        .header p { margin-top: 5px; font-size: 16px; }
        .content { margin-top: 50px; line-height: 1.8; font-size: 16px; text-align: justify; }
        .highlight { font-weight: bold; }
        .signature-section { margin-top: 80px; text-align: right; }
        .signature-line { border-bottom: 1px solid #000; width: 250px; margin-top: 60px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $tenant->name }}</h1>
        <p>Official No-Dues Certificate</p>
    </div>

    <div class="content">
        <p><strong>Date:</strong> {{ now()->format('F jS, Y') }}</p>
        <br>
        <p>This is to certify that Mr./Ms. <span class="highlight">{{ $loanAccount->member->name }}</span>, holding Member ID <span class="highlight">{{ $loanAccount->member->member_uid }}</span>, has successfully paid all dues in full for the following loan:</p>
        <ul>
            <li><strong>Loan Account Number:</strong> <span class="highlight">{{ $loanAccount->account_number }}</span></li>
            <li><strong>Loan Amount:</strong> <span class="highlight">${{ number_format($loanAccount->principal_amount, 2) }}</span></li>
            <li><strong>Disbursement Date:</strong> <span class="highlight">{{ $loanAccount->disbursement_date->format('d M, Y') }}</span></li>
        </ul>
        <p>As of the date of this certificate, there is no outstanding balance on this account. The account is considered <span class="highlight">CLOSED</span> and fully settled.</p>
        <p>This certificate is issued at the request of the member.</p>
    </div>

    <div class="signature-section">
        <div class="signature-line"></div>
        <p>Authorized Signatory</p>
        <p><strong>For {{ $tenant->name }}</strong></p>
    </div>
</div>



</body>
</html>
