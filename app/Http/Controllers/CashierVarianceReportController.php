<?php

namespace App\Http\Controllers;

use App\Exports\CashierVariancesExport;
use App\Models\CashierReconciliation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class CashierVarianceReportController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse|Response
    {
        abort_unless($request->user()->hasPermission('export_cashier_variances'), 403);

        $validated = $request->validate([
            'format' => ['required', 'in:pdf,xlsx,csv'],
            'cashier' => ['nullable', 'string', 'max:50'],
            'branch' => ['nullable', 'integer', 'exists:branches,id'],
            'shift' => ['nullable', 'integer', 'exists:shifts,id'],
            'status' => ['nullable', 'in:draft,submitted,approved,rejected,reversed'],
            'classification' => ['nullable', 'in:shortage,surplus,balanced'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $rows = CashierReconciliation::query()
            ->when(
                !($request->user()->owner || $request->user()->role === 'admin'
                    || $request->user()->hasPermission('view_all_branches')),
                fn ($query) => $query->where('branch_id', $request->user()->branch_id)
            )
            ->when($validated['cashier'] ?? null, fn ($query, $value) => $query->where('cashier_employee_id', $value))
            ->when($validated['branch'] ?? null, fn ($query, $value) => $query->where('branch_id', $value))
            ->when($validated['shift'] ?? null, fn ($query, $value) => $query->where('shift_id', $value))
            ->when($validated['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($validated['classification'] ?? null, fn ($query, $value) => $query->where('classification', $value))
            ->when($validated['from'] ?? null, fn ($query, $value) => $query->whereDate('business_date', '>=', $value))
            ->when($validated['to'] ?? null, fn ($query, $value) => $query->whereDate('business_date', '<=', $value))
            ->orderBy('business_date')
            ->get();

        if ($validated['format'] === 'pdf') {
            return Pdf::loadView('reports.cashier-variances', [
                'reconciliations' => $rows,
                'filters' => $validated,
            ])->setPaper('a4', 'landscape')->download('cashier-variances.pdf');
        }

        $format = $validated['format'] === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;

        return Excel::download(
            new CashierVariancesExport($rows),
            "cashier-variances.{$validated['format']}",
            $format
        );
    }
}
