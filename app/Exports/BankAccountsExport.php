<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BankAccountsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $accounts;

    public function __construct($accounts)
    {
        $this->accounts = $accounts;
    }

    public function collection()
    {
        return $this->accounts;
    }

    public function headings(): array
    {
        return [
            'Account Name',
            'Account Number',
            'Bank Name',
            'Current Balance',
            'Opening Balance',
            'Status',
            'Created At',
            'Last Updated'
        ];
    }

    public function map($account): array
    {
        return [
            $account->name,
            $account->account_number,
            $account->bank_name,
            $account->current_balance,
            $account->opening_balance,
            $account->active ? 'Active' : 'Inactive',
            $account->created_at ? $account->created_at->format('Y-m-d H:i:s') : '',
            $account->updated_at ? $account->updated_at->format('Y-m-d H:i:s') : ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
