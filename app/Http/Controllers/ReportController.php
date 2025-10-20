<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Sale;
use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\ExpenseClaim;
use App\Models\Branch;
use App\Models\Deposit;
use App\Models\ChequePayment;
use App\Exports\CashFlowExport;
use App\Exports\ExpensesExport;
use App\Exports\SalesExport;
use App\Exports\InvoicesExport;
use App\Exports\BillsExport;
use App\Exports\BankAccountsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Exports\TransactionsExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function index()
    {
        // Overview statistics for quick stats cards
        $overview = $this->getOverviewStats();

        // Get filter options
        $accounts = BankAccount::select('id', 'name', 'bank_name')
            ->orderBy('name')
            ->get();

        $branches = Branch::select('id', 'name', 'location')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $expenseCategories = ExpenseClaim::with('items')
            ->get()
            ->pluck('items')
            ->flatten()
            ->pluck('category')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Get chart data for initial load
        $chartData = $this->getInitialChartData();

        return Inertia::render('Reports/Index', [
            'overview' => $overview,
            'accounts' => $accounts,
            'branches' => $branches,
            'expenseCategories' => $expenseCategories,
            'chartData' => $chartData,
        ]);
    }

    private function getOverviewStats()
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Calculate total revenue (sales + invoice payments + deposits)
        $salesRevenue = Sale::whereYear('sales_date', $currentYear)->sum('amount');
        $invoiceRevenue = Invoice::where('status', 'paid')
            ->whereYear('created_at', $currentYear)
            ->sum('amount_paid');
        $depositRevenue = Deposit::whereYear('deposit_date', $currentYear)->sum('amount');
        $totalRevenue = $salesRevenue + $invoiceRevenue + $depositRevenue;

        // Calculate total expenses (expense claims + bills + cheque payments)
        $expenseClaims = ExpenseClaim::where('status', 'active')
            ->whereYear('created_at', $currentYear)
            ->sum('total');
        $billsExpenses = Bill::where('status', 'paid')
            ->whereYear('created_at', $currentYear)
            ->sum('amount');
        $chequeExpenses = ChequePayment::where('status', 'paid')
            ->whereYear('created_at', $currentYear)
            ->sum('amount');
        $totalExpenses = $expenseClaims + $billsExpenses + $chequeExpenses;

        // Calculate net profit
        $netProfit = $totalRevenue - $totalExpenses;

        // Count pending items across all modules
        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $pendingBills = Bill::where('status', 'pending')->count();
        $draftExpenses = ExpenseClaim::where('status', 'draft')->count();
        $pendingCheques = ChequePayment::where('status', 'pending')->count();
        $pendingItems = $pendingInvoices + $pendingBills + $draftExpenses + $pendingCheques;

        return [
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'pendingItems' => $pendingItems,
        ];
    }

    private function getInitialChartData()
    {
        $data = [
            'transactions' => $this->getTransactionChartData(),
            'sales' => $this->getSalesChartData(),
            'expenses' => $this->getExpensesChartData(),
            'invoicesBills' => $this->getInvoicesBillsChartData(),
            'bankAccounts' => $this->getBankAccountsChartData(),
            'cashFlow' => $this->getCashFlowChartData(),
        ];



        return $data;
    }

    private function getTransactionChartData($accountId = null, $fromDate = null, $toDate = null)
    {
        $query = Transaction::query();

        if ($accountId) {
            $query->where('bank_account_id', $accountId);
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('transaction_date', [$fromDate, $toDate]);
        } else {
            // Default to last 30 days
            $query->where('transaction_date', '>=', now()->subDays(30));
        }

        $transactions = $query->orderBy('transaction_date')
            ->get()
            ->groupBy(function ($transaction) {
                return Carbon::parse($transaction->transaction_date)->format('M d');
            });

        $labels = $transactions->keys()->toArray();
        $credits = $transactions->map(function ($group) {
            return $group->where('type', 'credit')->sum('amount');
        })->values()->toArray();
        $debits = $transactions->map(function ($group) {
            return $group->where('type', 'debit')->sum('amount');
        })->values()->toArray();

        return compact('labels', 'credits', 'debits');
    }

    private function getSalesChartData($branchId = null, $period = 'monthly', $year = null)
    {
        $year = $year ?: now()->year;
        $query = Sale::whereYear('sales_date', $year);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->orderBy('sales_date')->get();

        if ($sales->isEmpty()) {
            return ['labels' => [], 'values' => []];
        }

        $groupedSales = $sales->groupBy(function ($sale) use ($period) {
            $date = Carbon::parse($sale->sales_date);
            switch ($period) {
                case 'daily':
                    return $date->format('M d');
                case 'weekly':
                    return 'Week ' . $date->week . ' - ' . $date->format('M');
                case 'monthly':
                    return $date->format('M Y');
                case 'yearly':
                    return $date->format('Y');
                default:
                    return $date->format('M Y');
            }
        });

        $labels = $groupedSales->keys()->toArray();
        $values = $groupedSales->map(function ($group) {
            return $group->sum('amount');
        })->values()->toArray();



        return compact('labels', 'values');
    }

    private function getExpensesChartData($category = null, $status = null)
    {
        $query = ExpenseClaim::with('items');

        if ($status) {
            $query->where('status', $status);
        }

        // Get expenses for current year
        $expenses = $query->whereYear('created_at', now()->year)->get();

        // Filter by category if specified
        if ($category) {
            $expenses = $expenses->filter(function ($expense) use ($category) {
                return $expense->items->contains('category', $category);
            });
        }

        // Group by category from expense items
        $categoryTotals = [];
        foreach ($expenses as $expense) {
            foreach ($expense->items as $item) {
                if ($item->category) {
                    if (!isset($categoryTotals[$item->category])) {
                        $categoryTotals[$item->category] = 0;
                    }
                    $categoryTotals[$item->category] += $item->unit_price * $item->quantity;
                }
            }
        }

        if (empty($categoryTotals)) {
            return ['labels' => [], 'values' => []];
        }

        // Sort by total descending
        arsort($categoryTotals);

        $labels = array_keys($categoryTotals);
        $values = array_values($categoryTotals);

        return compact('labels', 'values');
    }

    private function getInvoicesBillsChartData($type = null, $status = null)
    {
        $labels = [];
        $invoices = [];
        $bills = [];

        // Get data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthYear = $date->format('M Y');
            $labels[] = $monthYear;

            $invoiceQuery = Invoice::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);
            $billQuery = Bill::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            if ($status) {
                $invoiceQuery->where('status', $status);
                $billQuery->where('status', $status);
            }

            // Always get both values to show comparison
            $invoiceAmount = $invoiceQuery->sum('amount') ?? 0;
            $billAmount = $billQuery->sum('amount') ?? 0;

            if (!$type || $type === 'invoices' || $type === '') {
                $invoices[] = $invoiceAmount;
            } else {
                $invoices[] = 0;
            }

            if (!$type || $type === 'bills' || $type === '') {
                $bills[] = $billAmount;
            } else {
                $bills[] = 0;
            }
        }

        return compact('labels', 'invoices', 'bills');
    }

    private function getBankAccountsChartData($bank = null)
    {
        $query = BankAccount::query();

        if ($bank) {
            $query->where('bank_name', $bank);
        }

        $accounts = $query->select('name', 'current_balance')
            ->where('active', true)
            ->orderByDesc('current_balance')
            ->get();

        $labels = $accounts->pluck('name')->toArray();
        $values = $accounts->pluck('current_balance')->toArray();

        return compact('labels', 'values');
    }

    private function getCashFlowChartData($period = 'monthly', $year = null)
    {
        $year = $year ?: now()->year;
        $labels = [];
        $inflow = [];
        $outflow = [];
        $net = [];

        switch ($period) {
            case 'monthly':
                for ($month = 1; $month <= 12; $month++) {
                    $date = Carbon::create($year, $month, 1);
                    $labels[] = $date->format('M Y');

                    // Calculate inflow (sales + deposits + invoice payments)
                    $monthInflow = Sale::whereYear('sales_date', $year)
                        ->whereMonth('sales_date', $month)
                        ->sum('amount');

                    $monthInflow += Deposit::whereYear('deposit_date', $year)
                        ->whereMonth('deposit_date', $month)
                        ->sum('amount');

                    $monthInflow += Transaction::where('type', 'credit')
                        ->whereYear('transaction_date', $year)
                        ->whereMonth('transaction_date', $month)
                        ->sum('amount');

                    // Calculate outflow (expenses + bills + cheque payments)
                    $monthOutflow = ExpenseClaim::where('status', 'active')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->sum('total');

                    $monthOutflow += Bill::where('status', 'paid')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->sum('amount');

                    $monthOutflow += ChequePayment::where('status', 'paid')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->sum('amount');

                    $inflow[] = $monthInflow;
                    $outflow[] = $monthOutflow;
                    $net[] = $monthInflow - $monthOutflow;
                }
                break;

            case 'quarterly':
                for ($quarter = 1; $quarter <= 4; $quarter++) {
                    $labels[] = "Q{$quarter} {$year}";
                    $startMonth = ($quarter - 1) * 3 + 1;
                    $endMonth = $quarter * 3;

                    $quarterInflow = Sale::whereYear('sales_date', $year)
                        ->whereBetween(DB::raw('MONTH(sales_date)'), [$startMonth, $endMonth])
                        ->sum('amount');

                    $quarterOutflow = ExpenseClaim::where('status', 'active')
                        ->whereYear('created_at', $year)
                        ->whereBetween(DB::raw('MONTH(created_at)'), [$startMonth, $endMonth])
                        ->sum('total');

                    $inflow[] = $quarterInflow;
                    $outflow[] = $quarterOutflow;
                    $net[] = $quarterInflow - $quarterOutflow;
                }
                break;

            case 'yearly':
                for ($y = $year - 4; $y <= $year; $y++) {
                    $labels[] = (string) $y;

                    $yearInflow = Sale::whereYear('sales_date', $y)->sum('amount');
                    $yearOutflow = ExpenseClaim::where('status', 'active')
                        ->whereYear('created_at', $y)
                        ->sum('total');

                    $inflow[] = $yearInflow;
                    $outflow[] = $yearOutflow;
                    $net[] = $yearInflow - $yearOutflow;
                }
                break;
        }

        return compact('labels', 'inflow', 'outflow', 'net');
    }

    public function generate()
    {
        $type = Request::input('type');
        $format = Request::input('format');
        $filters = json_decode(Request::input('filters', '{}'), true);

        switch ($type) {
            case 'transactions':
                return $this->generateTransactionsReport($format, $filters);
            case 'sales':
                return $this->generateSalesReport($format, $filters);
            case 'expenses':
                return $this->generateExpensesReport($format, $filters);
            case 'invoices-bills':
                return $this->generateInvoicesBillsReport($format, $filters);
            case 'bank-accounts':
                return $this->generateBankAccountsReport($format, $filters);
            case 'cash-flow':
                return $this->generateCashFlowReport($format, $filters);
            default:
                abort(400, 'Invalid report type');
        }
    }

    private function generateTransactionsReport($format, $filters)
    {
        $query = Transaction::with(['bankAccount', 'branch', 'creator']);

        if (!empty($filters['accountId'])) {
            $query->where('bank_account_id', $filters['accountId']);
        }

        if (!empty($filters['fromDate']) && !empty($filters['toDate'])) {
            $query->whereBetween('transaction_date', [$filters['fromDate'], $filters['toDate']]);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        // Calculate running balance
        $balance = 0;
        $transactions->each(function ($transaction) use (&$balance) {
            if ($transaction->type === 'credit') {
                $balance += $transaction->amount;
            } else {
                $balance -= $transaction->amount;
            }
            $transaction->running_balance = $balance;
        });

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.transactions', [
                'transactions' => $transactions,
                'filters' => $filters,
                'account' => !empty($filters['accountId']) ? BankAccount::find($filters['accountId']) : null,
            ]);

            $pdf->setPaper('a4', 'landscape');
            return $pdf->download('transactions-report.pdf');
        }

        return Excel::download(new TransactionsExport($transactions), 'transactions-report.xlsx');
    }

    private function generateSalesReport($format, $filters)
    {
        $query = Sale::with(['branch']);

        if (!empty($filters['branchId'])) {
            $query->where('branch_id', $filters['branchId']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('sales_date', $filters['year']);
        }

        $sales = $query->orderBy('sales_date', 'desc')->get();

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.sales', [
                'sales' => $sales,
                'filters' => $filters,
                'branch' => !empty($filters['branchId']) ? Branch::find($filters['branchId']) : null,
            ]);

            return $pdf->download('sales-report.pdf');
        }

        return Excel::download(new SalesExport($sales), 'sales-report.xlsx');
    }

    private function generateExpensesReport($format, $filters)
    {
        $query = ExpenseClaim::with(['user', 'branch', 'bankAccount', 'items']);

        if (!empty($filters['category'])) {
            $query->whereHas('items', function ($q) use ($filters) {
                $q->where('category', $filters['category']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['branchId'])) {
            $query->where('branch_id', $filters['branchId']);
        }

        if (!empty($filters['fromDate'])) {
            $query->where('claim_date', '>=', $filters['fromDate']);
        }

        if (!empty($filters['toDate'])) {
            $query->where('claim_date', '<=', $filters['toDate']);
        }

        $expenses = $query->orderBy('claim_date', 'desc')->get();

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.expenses', [
                'expenses' => $expenses,
                'filters' => $filters,
            ]);

            return $pdf->download('expense-claims-report.pdf');
        }

        return Excel::download(new ExpensesExport($expenses), 'expense-claims-report.xlsx');
    }

    private function generateInvoicesBillsReport($format, $filters)
    {
        $invoices = collect();
        $bills = collect();

        if (empty($filters['type']) || $filters['type'] === 'invoices') {
            $invoiceQuery = Invoice::with(['bankAccount', 'branch']);
            if (!empty($filters['status'])) {
                $invoiceQuery->where('status', $filters['status']);
            }
            $invoices = $invoiceQuery->orderBy('created_at', 'desc')->get();
        }

        if (empty($filters['type']) || $filters['type'] === 'bills') {
            $billQuery = Bill::with(['vendor', 'creator']);
            if (!empty($filters['status'])) {
                $billQuery->where('status', $filters['status']);
            }
            $bills = $billQuery->orderBy('created_at', 'desc')->get();
        }

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.invoices-bills', [
                'invoices' => $invoices,
                'bills' => $bills,
                'filters' => $filters,
            ]);

            $pdf->setPaper('a4', 'landscape');
            return $pdf->download('invoices-bills-report.pdf');
        }

        return Excel::download(new InvoicesExport($invoices, $bills), 'invoices-bills-report.xlsx');
    }

    private function generateBankAccountsReport($format, $filters)
    {
        $query = BankAccount::with(['transactions']);

        if (!empty($filters['bank'])) {
            $query->where('bank_name', $filters['bank']);
        }

        $accounts = $query->orderBy('name')->get();

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.bank-accounts', [
                'accounts' => $accounts,
                'filters' => $filters,
            ]);

            return $pdf->download('bank-accounts-report.pdf');
        }

        return Excel::download(new BankAccountsExport($accounts), 'bank-accounts-report.xlsx');
    }

    private function generateCashFlowReport($format, $filters)
    {
        $period = $filters['period'] ?? 'monthly';
        $year = $filters['year'] ?? now()->year;

        $data = $this->getCashFlowChartData($period, $year);

        if ($format === 'pdf') {
            // Transform data for PDF view
            $transactions = collect();
            $summaryType = $period;

            for ($i = 0; $i < count($data['labels']); $i++) {
                $periodLabel = $data['labels'][$i];
                $periodData = collect([
                    (object)['type' => 'credit', 'amount' => $data['inflow'][$i]],
                    (object)['type' => 'debit', 'amount' => $data['outflow'][$i]]
                ]);
                $transactions->put($periodLabel, $periodData);
            }

            $pdf = PDF::loadView('reports.cash-flow', [
                'transactions' => $transactions,
                'summaryType' => $summaryType,
                'filters' => $filters,
            ]);

            return $pdf->download('cash-flow-report.pdf');
        }

        return Excel::download(new CashFlowExport($data), 'cash-flow-report.xlsx');
    }

    public function getChartData(): JsonResponse
    {
        $type = Request::input('type');
        $filters = Request::input('filters', []);

        $data = match ($type) {
            'transactions' => $this->getTransactionChartData(
                $filters['accountId'] ?? null,
                $filters['fromDate'] ?? null,
                $filters['toDate'] ?? null
            ),
            'sales' => $this->getSalesChartData(
                $filters['branchId'] ?? null,
                $filters['period'] ?? 'monthly',
                $filters['year'] ?? null
            ),
            'expenses' => $this->getExpensesChartData(
                $filters['category'] ?? null,
                $filters['status'] ?? null
            ),
            'invoicesBills' => $this->getInvoicesBillsChartData(
                $filters['type'] ?? null,
                $filters['status'] ?? null
            ),
            'bankAccounts' => $this->getBankAccountsChartData(
                $filters['bank'] ?? null
            ),
            'cashFlow' => $this->getCashFlowChartData(
                $filters['period'] ?? 'monthly',
                $filters['year'] ?? null
            ),
            default => []
        };

        return response()->json($data);
    }
}
