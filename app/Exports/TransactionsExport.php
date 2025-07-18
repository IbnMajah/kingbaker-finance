<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Description',
            'Type',
            'Category',
            'Reference',
            'Amount',
            'Balance',
            'Account',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->transaction_date,
            $transaction->description,
            $transaction->type,
            $transaction->category,
            $transaction->reference_number,
            $transaction->amount,
            $transaction->running_balance,
            $transaction->bankAccount->name,
        ];
    }
}