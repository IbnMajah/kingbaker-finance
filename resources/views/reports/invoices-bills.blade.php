<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoices & Bills Report</title>
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
            font-size: 16px;
            font-weight: bold;
        }

        .summary .invoices .value {
            color: #8B5CF6;
        }

        .summary .bills .value {
            color: #F59E0B;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h3 {
            margin-bottom: 15px;
            color: #374151;
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
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
        }

        .status.pending {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status.paid {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status.overdue {
            background-color: #FEE2E2;
            color: #991B1B;
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
        <h2>Invoices & Bills Report</h2>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="filters">
        <strong>Report Filters:</strong>
        @if(isset($filters['type']) && $filters['type'])
        <p><strong>Type:</strong> {{ ucfirst($filters['type']) }}</p>
        @else
        <p><strong>Type:</strong> Both Invoices and Bills</p>
        @endif
        @if(isset($filters['status']) && $filters['status'])
        <p><strong>Status:</strong> {{ ucfirst($filters['status']) }}</p>
        @endif
    </div>

    <div class="summary">
        <div class="invoices">
            <div class="value">GMD {{ number_format($invoices->sum('amount'), 2) }}</div>
            <div>Total Invoices ({{ count($invoices) }})</div>
        </div>
        <div class="bills">
            <div class="value">GMD {{ number_format($bills->sum('amount'), 2) }}</div>
            <div>Total Bills ({{ count($bills) }})</div>
        </div>
        <div>
            <div class="value">GMD {{ number_format($invoices->sum('amount') - $bills->sum('amount'), 2) }}</div>
            <div>Net (Receivables - Payables)</div>
        </div>
    </div>

    @if(count($invoices) > 0)
    <div class="section">
        <h3>Invoices</h3>
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th class="amount">Amount</th>
                    <th class="amount">Paid</th>
                    <th class="amount">Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->customer_name }}</td>
                    <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                    <td>{{ $invoice->due_date->format('Y-m-d') }}</td>
                    <td class="amount">GMD {{ number_format($invoice->amount, 2) }}</td>
                    <td class="amount">GMD {{ number_format($invoice->amount_paid, 2) }}</td>
                    <td class="amount">GMD {{ number_format($invoice->amount - $invoice->amount_paid, 2) }}</td>
                    <td class="status {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(count($bills) > 0)
    <div class="section">
        <h3>Bills</h3>
        <table>
            <thead>
                <tr>
                    <th>Bill #</th>
                    <th>Vendor</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th class="amount">Amount</th>
                    <th class="amount">Paid</th>
                    <th class="amount">Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bills as $bill)
                <tr>
                    <td>{{ $bill->bill_number }}</td>
                    <td>{{ $bill->vendor ? $bill->vendor->name : 'N/A' }}</td>
                    <td>{{ $bill->bill_date->format('Y-m-d') }}</td>
                    <td>{{ $bill->due_date->format('Y-m-d') }}</td>
                    <td class="amount">GMD {{ number_format($bill->amount, 2) }}</td>
                    <td class="amount">GMD {{ number_format($bill->amount_paid, 2) }}</td>
                    <td class="amount">GMD {{ number_format($bill->amount - $bill->amount_paid, 2) }}</td>
                    <td class="status {{ $bill->status }}">{{ ucfirst($bill->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>This report contains {{ count($invoices) }} invoices and {{ count($bills) }} bills.</p>
        <p>King Baker Finance Management System - Confidential</p>
    </div>
</body>

</html>