<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ExpenseClaim;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Bill;
use App\Models\BankAccount;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Account Summary
        $totalAccounts = BankAccount::count();
        $totalBalance = BankAccount::sum('current_balance');

        // Today's Activity
        $todayDeposits = Transaction::where('type', 'credit')
            ->whereDate('transaction_date', today())
            ->sum('amount');
        $todayExpenses = Transaction::where('type', 'debit')
            ->whereDate('transaction_date', today())
            ->sum('amount');

        // This Month's Summary
        $thisMonthDeposits = Transaction::where('type', 'credit')
            ->whereYear('transaction_date', today()->year)
            ->whereMonth('transaction_date', today()->month)
            ->sum('amount');
        $thisMonthExpenses = Transaction::where('type', 'debit')
            ->whereYear('transaction_date', today()->year)
            ->whereMonth('transaction_date', today()->month)
            ->sum('amount');

        // Invoice & Bills Status
        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        $pendingBills = Bill::where('status', 'pending')->count();
        $overdueBills = Bill::where('status', 'overdue')->count();

        // Transaction History (Last 12 months)
        $transactionHistory = Transaction::select(
            DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as month'),
            DB::raw('SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) as credits'),
            DB::raw('SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) as debits')
        )
        ->whereYear('transaction_date', '>=', now()->subYear())
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Expense Categories (This Month)
        $expensesByCategory = Transaction::select('category', DB::raw('SUM(amount) as total'))
            ->where('type', 'debit')
            ->whereYear('transaction_date', today()->year)
            ->whereMonth('transaction_date', today()->month)
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Recent Transactions
        $recentTransactions = Transaction::with(['bankAccount', 'branch'])
            ->latest('transaction_date')
            ->take(5)
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->id,
                'date' => $transaction->transaction_date,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'payment_mode' => $transaction->payment_mode,
                'reference_number' => $transaction->reference_number,
                'account' => $transaction->bankAccount->name,
                'category' => $transaction->category,
            ]);

        // Pending Expense Claims
        $pendingExpenseClaims = ExpenseClaim::where('status', 'pending')
            ->count();
        $totalPendingExpenseAmount = ExpenseClaim::where('status', 'pending')
            ->sum('total_amount');

        return Inertia::render('Dashboard/Index', [
            'summary' => [
                'totalAccounts' => $totalAccounts,
                'totalBalance' => $totalBalance,
                'todayDeposits' => $todayDeposits,
                'todayExpenses' => $todayExpenses,
                'thisMonthDeposits' => $thisMonthDeposits,
                'thisMonthExpenses' => $thisMonthExpenses,
            ],
            'invoicesAndBills' => [
                'pendingInvoices' => $pendingInvoices,
                'overdueInvoices' => $overdueInvoices,
                'pendingBills' => $pendingBills,
                'overdueBills' => $overdueBills,
            ],
            'expenseClaims' => [
                'pendingCount' => $pendingExpenseClaims,
                'pendingAmount' => $totalPendingExpenseAmount,
            ],
            'transactionHistory' => $transactionHistory,
            'expensesByCategory' => $expensesByCategory,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
