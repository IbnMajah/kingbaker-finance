<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bank Accounts Report</title>
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

        .summary {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-around;
        }

        .summary div {
            text-align: center;
        }

        .summary .value {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
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

        .status {
            text-align: center;
        }

        .status.active {
            color: #059669;
        }

        .status.inactive {
            color: #DC2626;
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
        <h2>Bank Accounts Report</h2>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="filters">
        <strong>Report Filters:</strong>
        @if(isset($filters['bank']) && $filters['bank'])
        <p><strong>Bank:</strong> {{ $filters['bank'] }}</p>
        @else
        <p><strong>Bank:</strong> All Banks</p>
        @endif
    </div>

    <div class="summary">
        <div>
            <div class="value">GMD {{ number_format($accounts->sum('current_balance'), 2) }}</div>
            <div>Total Balance</div>
        </div>
        <div>
            <div class="value">{{ $accounts->where('active', true)->count() }}</div>
            <div>Active Accounts</div>
        </div>
        <div>
            <div class="value">{{ count($accounts) }}</div>
            <div>Total Accounts</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Account Name</th>
                <th>Account Number</th>
                <th>Bank</th>
                <th class="amount">Opening Balance</th>
                <th class="amount">Current Balance</th>
                <th class="amount">Net Change</th>
                <th class="status">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
            <tr>
                <td>{{ $account->name }}</td>
                <td>{{ $account->account_number }}</td>
                <td>{{ $account->bank_name ?: 'N/A' }}</td>
                <td class="amount">GMD {{ number_format($account->opening_balance, 2) }}</td>
                <td class="amount">GMD {{ number_format($account->current_balance, 2) }}</td>
                <td class="amount">GMD {{ number_format($account->current_balance - $account->opening_balance, 2) }}</td>
                <td class="status {{ $account->active ? 'active' : 'inactive' }}">
                    {{ $account->active ? 'Active' : 'Inactive' }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="3">TOTALS</td>
                <td class="amount">GMD {{ number_format($accounts->sum('opening_balance'), 2) }}</td>
                <td class="amount">GMD {{ number_format($accounts->sum('current_balance'), 2) }}</td>
                <td class="amount">GMD {{ number_format($accounts->sum('current_balance') - $accounts->sum('opening_balance'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report contains {{ count($accounts) }} bank accounts.</p>
        <p>King Baker Finance Management System - Confidential</p>
    </div>
</body>

</html>