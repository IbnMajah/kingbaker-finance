<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cashier Variance Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { margin: 0 0 12px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .number { text-align: right; }
    </style>
</head>
<body>
    <h1>Cashier Variance Report</h1>
    <p>Generated {{ now()->format('d M Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Cashier</th>
                <th>Branch</th>
                <th>Shift</th>
                <th class="number">Expected</th>
                <th class="number">Actual</th>
                <th class="number">Variance</th>
                <th>Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reconciliations as $row)
                <tr>
                    <td>{{ $row->business_date->format('d/m/Y') }}</td>
                    <td>{{ $row->sale_id ? 'SALE-'.$row->sale_id : $row->pos_reference }}</td>
                    <td>{{ $row->cashier_name }} ({{ $row->cashier_employee_id }})</td>
                    <td>{{ $row->branch_name }}</td>
                    <td>{{ $row->shift_name }}</td>
                    <td class="number">{{ number_format($row->expected_total, 2) }}</td>
                    <td class="number">{{ number_format($row->actual_total, 2) }}</td>
                    <td class="number">{{ number_format($row->calculated_variance, 2) }}</td>
                    <td>{{ ucfirst($row->classification) }}</td>
                    <td>{{ ucfirst($row->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
