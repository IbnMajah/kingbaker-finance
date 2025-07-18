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
            'Date',
            'Description',
            'Category',
            'Reference',
            'Amount',
            'Account',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->transaction_date,
            $expense->description,
            $expense->category,
            $expense->reference_number,
            $expense->amount,
            $expense->bankAccount->name,
        ];
    }
}