<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CashFlowExport implements FromCollection, WithHeadings, WithMapping
{
    protected $transactions;
    protected $summaryType;

    public function __construct($transactions, $summaryType)
    {
        $this->transactions = $transactions;
        $this->summaryType = $summaryType;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Period',
            'Credits',
            'Debits',
            'Net Flow',
        ];
    }

    public function map($group): array
    {
        $credits = $group->where('type', 'credit')->sum('amount');
        $debits = $group->where('type', 'debit')->sum('amount');
        $netFlow = $credits - $debits;

        return [
            $group->first()->transaction_date, // This will be formatted in the period format
            $credits,
            $debits,
            $netFlow,
        ];
    }
}