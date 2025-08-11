<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $invoices;
    protected $bills;

    public function __construct($invoices, $bills = null)
    {
        $this->invoices = $invoices;
        $this->bills = $bills ?? collect();
    }

    public function collection()
    {
        // Combine invoices and bills with type indicator
        $combined = collect();

        $this->invoices->each(function ($invoice) use ($combined) {
            $combined->push((object) [
                'type' => 'Invoice',
                'number' => $invoice->invoice_number,
                'date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'customer_vendor' => $invoice->customer_name,
                'amount' => $invoice->amount,
                'amount_paid' => $invoice->amount_paid,
                'status' => $invoice->status,
                'created_at' => $invoice->created_at,
            ]);
        });

        $this->bills->each(function ($bill) use ($combined) {
            $combined->push((object) [
                'type' => 'Bill',
                'number' => $bill->bill_number,
                'date' => $bill->bill_date,
                'due_date' => $bill->due_date,
                'customer_vendor' => $bill->vendor ? $bill->vendor->name : '',
                'amount' => $bill->amount,
                'amount_paid' => $bill->amount_paid,
                'status' => $bill->status,
                'created_at' => $bill->created_at,
            ]);
        });

        return $combined->sortByDesc('date');
    }

    public function headings(): array
    {
        return [
            'Type',
            'Number',
            'Date',
            'Due Date',
            'Customer/Vendor',
            'Amount',
            'Amount Paid',
            'Balance',
            'Status',
            'Created At'
        ];
    }

    public function map($item): array
    {
        $balance = $item->amount - $item->amount_paid;

        return [
            $item->type,
            $item->number,
            $item->date ? $item->date->format('Y-m-d') : '',
            $item->due_date ? $item->due_date->format('Y-m-d') : '',
            $item->customer_vendor,
            $item->amount,
            $item->amount_paid,
            $balance,
            ucfirst($item->status),
            $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Invoices & Bills';
    }
}
