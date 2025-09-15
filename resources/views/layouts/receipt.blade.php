<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Transaction Receipt')</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            color: #333;
            background-color: #f4f4f4;
            -webkit-print-color-adjust: exact; /* Ensures background colors print in Chrome */
            color-adjust: exact; /* Standard */
        }
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .receipt-header .logo-area .brand {
            font-size: 28px;
            font-weight: bold;
            color: #0056b3;
        }
        .receipt-header .receipt-details {
            text-align: right;
        }
        .receipt-details p {
            margin: 0;
            line-height: 1.6;
        }
        .receipt-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #444;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .receipt-body table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-body th, .receipt-body td {
            border: 1px solid #eee;
            padding: 12px;
            text-align: left;
        }
        .receipt-body th {
            background-color: #f8f9fa;
            font-weight: 600;
            width: 35%;
        }
        .receipt-body .highlight-row {
            font-size: 1.4em;
            font-weight: bold;
        }
        .receipt-body .highlight-row td {
            background-color: #e9ecef;
        }
        .receipt-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .print-controls {
            padding: 20px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            outline: none;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            margin: 0 10px;
        }
        .btn-secondary {
            background-color: #6c757d;
        }

        @media print {
            body {
                background-color: #fff;
            }
            .receipt-container {
                max-width: 100%;
                margin: 0;
                box-shadow: none;
                border: none;
                padding: 0;
            }
            .print-controls {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div class="print-controls">
    @yield('print-controls')
</div>

<div class="receipt-container" id="receipt">
    @yield('content')
</div>
</body>
</html>
