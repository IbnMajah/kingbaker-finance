<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CashierVariancesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $reconciliations)
    {
    }

    public function collection(): Collection
    {
        return $this->reconciliations;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Sale',
            'Cashier ID',
            'Cashier',
            'Branch',
            'Shift',
            'Expected',
            'Actual',
            'Variance',
            'Classification',
            'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->business_date->format('Y-m-d'),
            $row->sale_id ? 'SALE-'.$row->sale_id : $this->safeText($row->pos_reference),
            $this->safeText($row->cashier_employee_id),
            $this->safeText($row->cashier_name),
            $this->safeText($row->branch_name),
            $this->safeText($row->shift_name),
            $row->expected_total,
            $row->actual_total,
            $row->calculated_variance,
            $row->classification,
            $row->status,
        ];
    }

    private function safeText(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
