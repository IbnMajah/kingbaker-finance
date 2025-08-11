<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Transactions Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            color: #9B672A;
        }

        .header h2 {
            margin: 5px 0;
            color: #666;
        }

        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
        }

        .filters p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .amount {
            text-align: right;
        }

        .credit {
            color: #059669;
        }

        .debit {
            color: #DC2626;
        }

        .running-balance {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>King Baker Finance</h1>
        <h2>Transactions Report</h2>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="filters">
        <strong>Report Filters:</strong>
        @if(isset($account))
        <p><strong>Account:</strong> {{ $account->name }}</p>
        @else
        <p><strong>Account:</strong> All Accounts</p>
        @endif
        @if(isset($filters['fromDate']) && isset($filters['toDate']))
        <p><strong>Date Range:</strong> {{ $filters['fromDate'] }} to {{ $filters['toDate'] }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Account</th>
                <th>Type</th>
                <th>Category</th>
                <th class="amount">Amount</th>
                <th class="amount">Running Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                <td>{{ $transaction->reference_number }}</td>
                <td>{{ $transaction->description }}</td>
                <td>{{ $transaction->bankAccount ? $transaction->bankAccount->name : 'N/A' }}</td>
                <td class="{{ $transaction->type }}">{{ ucfirst($transaction->type) }}</td>
                <td>{{ $transaction->category }}</td>
                <td class="amount {{ $transaction->type }}">
                    {{ $transaction->type === 'credit' ? '+' : '-' }}GMD {{ number_format($transaction->amount, 2) }}
                </td>
                <td class="amount running-balance">GMD {{ number_format($transaction->running_balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report contains {{ count($transactions) }} transactions.</p>
        <p>King Baker Finance Management System - Confidential</p>
    </div>
</body>

</html>