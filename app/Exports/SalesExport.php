<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $sales;

    public function __construct($sales)
    {
        $this->sales = $sales;
    }

    public function collection()
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Branch',
            'Amount',
            'Cashier',
            'Shift',
            'Payment Mode',
            'Reference',
            'Notes',
            'Created At'
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->sales_date ? $sale->sales_date->format('Y-m-d') : '',
            $sale->branch ? $sale->branch->name : '',
            $sale->amount,
            $sale->cashier,
            $sale->shift ? $sale->shift->name : '',
            $sale->payment_mode,
            $sale->reference_number,
            $sale->notes,
            $sale->created_at ? $sale->created_at->format('Y-m-d H:i:s') : ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
