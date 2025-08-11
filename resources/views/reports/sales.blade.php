<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
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
        <h2>Sales Performance Report</h2>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="filters">
        <strong>Report Filters:</strong>
        @if(isset($branch))
        <p><strong>Branch:</strong> {{ $branch->name }}</p>
        @else
        <p><strong>Branch:</strong> All Branches</p>
        @endif
        @if(isset($filters['period']))
        <p><strong>Period:</strong> {{ ucfirst($filters['period']) }}</p>
        @endif
        @if(isset($filters['year']))
        <p><strong>Year:</strong> {{ $filters['year'] }}</p>
        @endif
    </div>

    <div class="summary">
        <div>
            <div class="value">GMD {{ number_format($sales->sum('amount'), 2) }}</div>
            <div>Total Sales</div>
        </div>
        <div>
            <div class="value">{{ count($sales) }}</div>
            <div>Total Transactions</div>
        </div>
        <div>
            <div class="value">GMD {{ $sales->count() > 0 ? number_format($sales->avg('amount'), 2) : '0.00' }}</div>
            <div>Average Sale</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Branch</th>
                <th>Cashier</th>
                <th>Shift</th>
                <th>Payment Mode</th>
                <th>Reference</th>
                <th class="amount">Amount</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->sales_date->format('Y-m-d') }}</td>
                <td>{{ $sale->branch ? $sale->branch->name : 'N/A' }}</td>
                <td>{{ $sale->cashier }}</td>
                <td>{{ $sale->shift ? $sale->shift->name : 'N/A' }}</td>
                <td>{{ $sale->payment_mode }}</td>
                <td>{{ $sale->reference_number }}</td>
                <td class="amount">GMD {{ number_format($sale->amount, 2) }}</td>
                <td>{{ $sale->notes }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report contains {{ count($sales) }} sales transactions.</p>
        <p>King Baker Finance Management System - Confidential</p>
    </div>
</body>

</html>