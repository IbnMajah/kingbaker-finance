<?php

namespace App\Policies;

use App\Models\User;

class FinancePolicy
{
    /**
     * Check if user can access finance module
     */
    public function viewFinance(User $user): bool
    {
        return $user->hasPermission('view_finance');
    }

    /**
     * Bank Account permissions
     */
    public function createBankAccounts(User $user): bool
    {
        return $user->hasPermission('create_bank_accounts');
    }

    public function editBankAccounts(User $user): bool
    {
        return $user->hasPermission('edit_bank_accounts');
    }

    public function viewBankAccounts(User $user): bool
    {
        return $user->hasPermission('view_bank_accounts');
    }

    /**
     * Deposit permissions
     */
    public function createDeposits(User $user): bool
    {
        return $user->hasPermission('create_deposits');
    }

    public function editDeposits(User $user): bool
    {
        return $user->hasPermission('edit_deposits');
    }

    public function viewDeposits(User $user): bool
    {
        return $user->hasPermission('view_deposits');
    }

    /**
     * Cheque Payment permissions
     */
    public function createChequePayments(User $user): bool
    {
        return $user->hasPermission('create_cheque_payments');
    }

    public function editChequePayments(User $user): bool
    {
        return $user->hasPermission('edit_cheque_payments');
    }

    public function viewChequePayments(User $user): bool
    {
        return $user->hasPermission('view_cheque_payments');
    }

    /**
     * Bills permissions
     */
    public function createBills(User $user): bool
    {
        return $user->hasPermission('create_bills');
    }

    public function editBills(User $user): bool
    {
        return $user->hasPermission('edit_bills');
    }

    public function viewBills(User $user): bool
    {
        return $user->hasPermission('view_bills');
    }

    public function createRecurringBills(User $user): bool
    {
        return $user->hasPermission('create_recurring_bills');
    }

    public function viewRecurringBills(User $user): bool
    {
        return $user->hasPermission('view_recurring_bills');
    }

    /**
     * Expense permissions
     */
    public function createExpenses(User $user): bool
    {
        return $user->hasPermission('create_expenses');
    }

    public function editExpenses(User $user): bool
    {
        return $user->hasPermission('edit_expenses');
    }

    public function viewExpenses(User $user): bool
    {
        return $user->hasPermission('view_expenses');
    }

    public function approveExpenses(User $user): bool
    {
        return $user->hasPermission('approve_expenses');
    }

    /**
     * Invoice permissions
     */
    public function createInvoices(User $user): bool
    {
        return $user->hasPermission('create_invoices');
    }

    public function editInvoices(User $user): bool
    {
        return $user->hasPermission('edit_invoices');
    }

    public function viewInvoices(User $user): bool
    {
        return $user->hasPermission('view_invoices');
    }

    /**
     * Vendor permissions
     */
    public function createVendors(User $user): bool
    {
        return $user->hasPermission('create_vendors');
    }

    public function editVendors(User $user): bool
    {
        return $user->hasPermission('edit_vendors');
    }

    public function viewVendors(User $user): bool
    {
        return $user->hasPermission('view_vendors');
    }

    /**
     * Transaction permissions
     */
    public function reconcileTransactions(User $user): bool
    {
        return $user->hasPermission('reconcile_transactions');
    }

    public function approveTransactions(User $user): bool
    {
        return $user->hasPermission('approve_transactions');
    }

    public function viewTransactions(User $user): bool
    {
        return $user->hasPermission('view_transactions');
    }

    /**
     * Report permissions
     */
    public function printReceipts(User $user): bool
    {
        return $user->hasPermission('print_receipts');
    }

    public function viewReports(User $user): bool
    {
        return $user->hasPermission('view_reports');
    }

    public function generateReports(User $user): bool
    {
        return $user->hasPermission('generate_reports');
    }
}