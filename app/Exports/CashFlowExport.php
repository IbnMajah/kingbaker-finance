<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashFlowExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $collection = collect();

        for ($i = 0; $i < count($this->data['labels']); $i++) {
            $collection->push((object) [
                'period' => $this->data['labels'][$i] ?? '',
                'inflow' => $this->data['inflow'][$i] ?? 0,
                'outflow' => $this->data['outflow'][$i] ?? 0,
                'net_flow' => $this->data['net'][$i] ?? 0,
            ]);
        }

        return $collection;
    }

    public function headings(): array
    {
        return [
            'Period',
            'Cash Inflow',
            'Cash Outflow',
            'Net Cash Flow',
        ];
    }

    public function map($row): array
    {
        return [
            $row->period,
            $row->inflow,
            $row->outflow,
            $row->net_flow,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
