<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BillsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $bills;

    public function __construct($bills)
    {
        $this->bills = $bills;
    }

    public function collection()
    {
        return $this->bills;
    }

    public function headings(): array
    {
        return [
            'Bill Number',
            'Vendor',
            'Bill Date',
            'Due Date',
            'Amount',
            'Amount Paid',
            'Balance',
            'Status',
            'Description',
            'Created At'
        ];
    }

    public function map($bill): array
    {
        $balance = $bill->amount - $bill->amount_paid;

        return [
            $bill->bill_number,
            $bill->vendor ? $bill->vendor->name : '',
            $bill->bill_date ? $bill->bill_date->format('Y-m-d') : '',
            $bill->due_date ? $bill->due_date->format('Y-m-d') : '',
            $bill->amount,
            $bill->amount_paid,
            $balance,
            ucfirst($bill->status),
            $bill->description,
            $bill->created_at ? $bill->created_at->format('Y-m-d H:i:s') : ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
