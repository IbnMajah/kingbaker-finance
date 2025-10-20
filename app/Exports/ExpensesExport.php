<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $expenses;

    public function __construct($expenses)
    {
        $this->expenses = $expenses;
    }

    public function collection()
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return [
            'Claim Date',
            'Title',
            'Reference ID',
            'Payee',
            'Total Amount',
            'Status',
            'Expense Type',
            'Branch',
            'Bank Account',
            'User',
            'Notes',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->claim_date?->format('Y-m-d'),
            $expense->title,
            $expense->reference_id,
            $expense->payee,
            $expense->total,
            $expense->status,
            $expense->expense_type,
            $expense->branch?->name ?? 'N/A',
            $expense->bankAccount?->name ?? 'N/A',
            $expense->user?->first_name . ' ' . $expense->user?->last_name ?? 'N/A',
            $expense->notes,
        ];
    }
}
