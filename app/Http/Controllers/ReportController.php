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
use App\Exports\CashFlowExport;
use App\Exports\ExpensesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Exports\TransactionsExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Request;


class ReportController extends Controller
{
    public function index()
    {
        // Get bank accounts for filter
        $accounts = BankAccount::select('id', 'name')
            ->get()
            ->map(function ($account) {
                return ['value' => $account->id, 'label' => $account->name];
            });

        // Get categories for filter
        $categories = Transaction::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->map(function ($category) {
                return ['value' => $category, 'label' => $category];
            });

        // Get all transactions with bank account info
        $transactions = Transaction::with('bankAccount')
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'date' => $transaction->transaction_date,
                    'transaction_date' => $transaction->transaction_date,
                    'description' => $transaction->description,
                    'type' => $transaction->type,
                    'category' => $transaction->category,
                    'amount' => $transaction->amount,
                    'reference_number' => $transaction->reference_number,
                    'bank_account_id' => $transaction->bank_account_id,
                    'bank_account' => $transaction->bankAccount ? [
                        'id' => $transaction->bankAccount->id,
                        'name' => $transaction->bankAccount->name
                    ] : null
                ];
            });

        // Get summary data
        $summary = [
            'total_credits' => $transactions->where('type', 'credit')->sum('amount'),
            'total_debits' => $transactions->where('type', 'debit')->sum('amount'),
            'net_movement' => $transactions->where('type', 'credit')->sum('amount') -
                            $transactions->where('type', 'debit')->sum('amount')
        ];

        return Inertia::render('Reports/Index', [
            'accounts' => $accounts,
            'categories' => $categories,
            'transactions' => $transactions,
            'summary' => $summary
        ]);
    }

    private function getTransactionStats()
    {
        $accountId = Request::input('account_id');

        $transactions = Transaction::with('bankAccount')
            ->when($accountId, function ($query) use ($accountId) {
                $query->where('bank_account_id', $accountId);
            })
            ->get()
            ->groupBy('bankAccount.name');

        $labels = $transactions->keys()->toArray();
        $data = $transactions->map(function ($accountTransactions) {
            return $accountTransactions->sum('amount');
        })->values()->toArray();

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getExpenseStats()
    {
        $category = Request::input('category');
        $days = 30; // Last 30 days by default

        $expenses = Transaction::where('type', 'debit')
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->where('transaction_date', '>=', now()->subDays($days))
            ->orderBy('transaction_date')
            ->get()
            ->groupBy(function ($transaction) {
                return Carbon::parse($transaction->transaction_date)->format('Y-m-d');
            });

        return [
            'labels' => $expenses->keys()->toArray(),
            'data' => $expenses->map->sum('amount')->values()->toArray(),
        ];
    }

    private function getSummaryStats()
    {
        $weekly = $this->getGroupedTransactions('weekly');
        $monthly = $this->getGroupedTransactions('monthly');
        $yearly = $this->getGroupedTransactions('yearly');

        return [
            'weekly' => $weekly,
            'monthly' => $monthly,
            'yearly' => $yearly,
        ];
    }

    private function getGroupedTransactions($period)
    {
        $transactions = Transaction::orderBy('transaction_date')
            ->get()
            ->groupBy(function ($transaction) use ($period) {
                $date = Carbon::parse($transaction->transaction_date);
                switch ($period) {
                    case 'weekly':
                        return $date->format('W') . ' - ' . $date->format('M');
                    case 'monthly':
                        return $date->format('F Y');
                    case 'yearly':
                        return $date->format('Y');
                }
            });

        $netFlow = $transactions->map(function ($group) {
            $credits = $group->where('type', 'credit')->sum('amount');
            $debits = $group->where('type', 'debit')->sum('amount');
            return $credits - $debits;
        });

        return [
            'labels' => $netFlow->keys()->toArray(),
            'data' => $netFlow->values()->toArray(),
        ];
    }

    public function generate()
    {
        $type = Request::input('type');
        $format = Request::input('format');
        $accountId = Request::input('account_id');
        $category = Request::input('category');
        $summaryType = Request::input('summary_type', 'monthly');

        switch ($type) {
            case 'account-sheet':
                return $this->generateAccountSheet($format, $accountId);
            case 'expense-claims':
                return $this->generateExpenseReport($format, $category);
            case 'cash-flow':
                return $this->generateCashFlowReport($format, $summaryType);
            default:
                abort(400, 'Invalid report type');
        }
    }

    private function generateAccountSheet($format, $accountId)
    {
        $transactions = Transaction::with('bankAccount')
            ->when($accountId, function ($query) use ($accountId) {
                $query->where('bank_account_id', $accountId);
            })
            ->orderBy('transaction_date')
            ->get();

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
            $pdf = PDF::loadView('reports.account-sheet', [
                'transactions' => $transactions,
                'account' => $accountId ? BankAccount::find($accountId) : null,
            ]);

            // Set paper size and orientation
            $pdf->setPaper('a4', 'landscape');

            return $pdf->download('account-sheet.pdf');
        }

        return Excel::download(new TransactionsExport($transactions), 'account-sheet.xlsx');
    }

    private function generateExpenseReport($format, $category)
    {
        $expenses = Transaction::where('type', 'debit')
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->orderBy('transaction_date')
            ->get();

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.expenses', [
                'expenses' => $expenses,
                'category' => $category,
            ]);
            return $pdf->download('expense-report.pdf');
        }

        return Excel::download(new ExpensesExport($expenses), 'expense-report.xlsx');
    }

    private function generateCashFlowReport($format, $summaryType)
    {
        $transactions = Transaction::orderBy('transaction_date')
            ->get()
            ->groupBy(function ($transaction) use ($summaryType) {
                $date = Carbon::parse($transaction->transaction_date);
                switch ($summaryType) {
                    case 'weekly':
                        return $date->format('W') . ' - ' . $date->format('M');
                    case 'monthly':
                        return $date->format('F Y');
                    case 'yearly':
                        return $date->format('Y');
                }
            });

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.cash-flow', [
                'transactions' => $transactions,
                'summaryType' => $summaryType,
            ]);
            return $pdf->download('cash-flow-report.pdf');
        }

        return Excel::download(new CashFlowExport($transactions, $summaryType), 'cash-flow-report.xlsx');
    }

    public function adminSummary()
    {
        $this->authorize('admin');

        return Inertia::render('Reports/AdminSummary', [
            'summary' => Sale::with(['branch', 'shift'])
                ->when(Request::input('start_date') && Request::input('end_date'), function ($query) {
                    $query->whereBetween('sales_date', [
                        Request::input('start_date'),
                        Request::input('end_date'),
                    ]);
                })
                ->get()
                ->groupBy('branch.name'),
            'filters' => Request::only(['start_date', 'end_date']),
        ]);
    }
}