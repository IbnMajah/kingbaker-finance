<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\OrganizationsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillPaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ExpenseClaimController;
use App\Http\Controllers\ExpenseItemController;
use App\Http\Controllers\ChequePaymentController;
use App\Http\Controllers\MiscellaneousController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth

Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login')
    ->middleware('guest');

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->name('login.store')
    ->middleware('guest');

Route::delete('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

// Dashboard

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Miscellaneous Transactions
    Route::resource('miscellaneous', MiscellaneousController::class);
    Route::post('miscellaneous/{transaction}/remove-image', [MiscellaneousController::class, 'removeImage'])
        ->name('miscellaneous.remove-image');

    // Users
    Route::get('users', [UsersController::class, 'index'])
        ->name('users');

    Route::get('users/create', [UsersController::class, 'create'])
        ->name('users.create');

    Route::post('users', [UsersController::class, 'store'])
        ->name('users.store');

    Route::get('users/{user}', [UsersController::class, 'show'])
        ->name('users.show');

    Route::get('users/{user}/edit', [UsersController::class, 'edit'])
        ->name('users.edit');

    Route::put('users/{user}', [UsersController::class, 'update'])
        ->name('users.update');

    Route::delete('users/{user}', [UsersController::class, 'destroy'])
        ->name('users.destroy');

    Route::put('users/{user}/restore', [UsersController::class, 'restore'])
        ->name('users.restore');

    // Bank Accounts
    Route::get('bank-accounts', [BankAccountController::class, 'index'])
        ->name('bank-accounts');

    Route::get('bank-accounts/create', [BankAccountController::class, 'create'])
        ->name('bank-accounts.create');

    Route::post('bank-accounts', [BankAccountController::class, 'store'])
        ->name('bank-accounts.store');

    Route::get('bank-accounts/{bankAccount}', [BankAccountController::class, 'show'])
        ->name('bank-accounts.show');

    Route::get('bank-accounts/{bankAccount}/edit', [BankAccountController::class, 'edit'])
        ->name('bank-accounts.edit');

    Route::put('bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])
        ->name('bank-accounts.update');

    Route::delete('bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy'])
        ->name('bank-accounts.destroy');

    Route::put('bank-accounts/{bankAccount}/restore', [BankAccountController::class, 'restore'])
        ->name('bank-accounts.restore');

    // Organizations
    Route::get('organizations', [OrganizationsController::class, 'index'])
        ->name('organizations');

    Route::get('organizations/create', [OrganizationsController::class, 'create'])
        ->name('organizations.create');

    Route::post('organizations', [OrganizationsController::class, 'store'])
        ->name('organizations.store');

    Route::get('organizations/{organization}/edit', [OrganizationsController::class, 'edit'])
        ->name('organizations.edit');

    Route::put('organizations/{organization}', [OrganizationsController::class, 'update'])
        ->name('organizations.update');

    Route::delete('organizations/{organization}', [OrganizationsController::class, 'destroy'])
        ->name('organizations.destroy');

    Route::put('organizations/{organization}/restore', [OrganizationsController::class, 'restore'])
        ->name('organizations.restore');

    // Contacts
    Route::get('contacts', [ContactsController::class, 'index'])
        ->name('contacts');

    Route::get('contacts/create', [ContactsController::class, 'create'])
        ->name('contacts.create');

    Route::post('contacts', [ContactsController::class, 'store'])
        ->name('contacts.store');

    Route::get('contacts/{contact}', [ContactsController::class, 'show'])
        ->name('contacts.show');

    Route::get('contacts/{contact}/edit', [ContactsController::class, 'edit'])
        ->name('contacts.edit');

    Route::put('contacts/{contact}', [ContactsController::class, 'update'])
        ->name('contacts.update');

    Route::delete('contacts/{contact}', [ContactsController::class, 'destroy'])
        ->name('contacts.destroy');

    Route::put('contacts/{contact}/restore', [ContactsController::class, 'restore'])
        ->name('contacts.restore');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])
        ->name('reports');

    Route::get('reports/generate', [ReportController::class, 'generate'])
        ->name('reports.generate');

    Route::get('reports/chart-data', [ReportController::class, 'getChartData'])
        ->name('reports.chart-data');



    // Images
    Route::get('/img/{path}', [ImagesController::class, 'show'])
        ->where('path', '.*')
        ->name('image');

    // Branches
    Route::get('branches', [BranchesController::class, 'index'])
        ->name('branches');

    Route::get('branches/create', [BranchesController::class, 'create'])
        ->name('branches.create');

    Route::post('branches', [BranchesController::class, 'store'])
        ->name('branches.store');

    Route::get('branches/{branch}', [BranchesController::class, 'show'])
        ->name('branches.show');

    Route::get('branches/{branch}/edit', [BranchesController::class, 'edit'])
        ->name('branches.edit');

    Route::put('branches/{branch}', [BranchesController::class, 'update'])
        ->name('branches.update');

    Route::delete('branches/{branch}', [BranchesController::class, 'destroy'])
        ->name('branches.destroy');

    Route::put('branches/{branch}/restore', [BranchesController::class, 'restore'])
        ->name('branches.restore');

    // Deposits
    Route::get('deposits', [DepositController::class, 'index'])
        ->name('deposits');

    Route::get('deposits/create', [DepositController::class, 'create'])
        ->name('deposits.create');

    Route::post('deposits', [DepositController::class, 'store'])
        ->name('deposits.store');

    Route::get('deposits/{deposit}', [DepositController::class, 'show'])
        ->name('deposits.show');

    Route::get('deposits/{deposit}/edit', [DepositController::class, 'edit'])
        ->name('deposits.edit');

    Route::put('deposits/{deposit}', [DepositController::class, 'update'])
        ->name('deposits.update');

    Route::delete('deposits/{deposit}', [DepositController::class, 'destroy'])
        ->name('deposits.destroy');

    Route::put('deposits/{deposit}/restore', [DepositController::class, 'restore'])
        ->name('deposits.restore');

    // Sales
    Route::get('sales', [SalesController::class, 'index'])
        ->name('sales');

    Route::get('sales/create', [SalesController::class, 'create'])
        ->name('sales.create');

    Route::post('sales', [SalesController::class, 'store'])
        ->name('sales.store');

    Route::get('sales/{sale}', [SalesController::class, 'show'])
        ->name('sales.show');

    Route::get('sales/{sale}/edit', [SalesController::class, 'edit'])
        ->name('sales.edit');

    Route::put('sales/{sale}', [SalesController::class, 'update'])
        ->name('sales.update');

    Route::delete('sales/{sale}', [SalesController::class, 'destroy'])
        ->name('sales.destroy');

    Route::put('sales/{sale}/restore', [SalesController::class, 'restore'])
        ->name('sales.restore');

    // Transactions
    Route::get('transactions', [TransactionController::class, 'index'])
        ->name('transactions');

    Route::get('transactions/create', [TransactionController::class, 'create'])
        ->name('transactions.create');

    Route::post('transactions', [TransactionController::class, 'store'])
        ->name('transactions.store');

    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])
        ->name('transactions.show');

    Route::get('transactions/{transaction}/edit', [TransactionController::class, 'edit'])
        ->name('transactions.edit');

    Route::put('transactions/{transaction}', [TransactionController::class, 'update'])
        ->name('transactions.update');

    Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])
        ->name('transactions.destroy');

    Route::put('transactions/{transaction}/reconcile', [TransactionController::class, 'reconcile'])
        ->name('transactions.reconcile');

    Route::put('transactions/{transaction}/unreconcile', [TransactionController::class, 'unreconcile'])
        ->name('transactions.unreconcile');

    // Vendors
    Route::get('vendors', [VendorController::class, 'index'])
        ->name('vendors');

    Route::get('vendors/create', [VendorController::class, 'create'])
        ->name('vendors.create');

    Route::post('vendors', [VendorController::class, 'store'])
        ->name('vendors.store');

    Route::get('vendors/{vendor}', [VendorController::class, 'show'])
        ->name('vendors.show');

    Route::get('vendors/{vendor}/edit', [VendorController::class, 'edit'])
        ->name('vendors.edit');

    Route::put('vendors/{vendor}', [VendorController::class, 'update'])
        ->name('vendors.update');

    Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])
        ->name('vendors.destroy');

    // Bills
    Route::get('bills', [BillController::class, 'index'])
        ->name('bills');

    Route::get('bills/create', [BillController::class, 'create'])
        ->name('bills.create');

    Route::post('bills', [BillController::class, 'store'])
        ->name('bills.store');

    Route::get('bills/{bill}', [BillController::class, 'show'])
        ->name('bills.show');

    Route::get('bills/{bill}/pay', [BillController::class, 'pay'])
        ->name('bills.pay');

    Route::post('bills/{bill}/record-payment', [BillController::class, 'recordPayment'])
        ->name('bills.record-payment');

    Route::get('bills/{bill}/edit', [BillController::class, 'edit'])
        ->name('bills.edit');

    Route::put('bills/{bill}', [BillController::class, 'update'])
        ->name('bills.update');

    Route::delete('bills/{bill}', [BillController::class, 'destroy'])
        ->name('bills.destroy');

    // Bill Payments
    Route::put('bill-payments/{billPayment}', [BillPaymentController::class, 'update'])
        ->name('bill-payments.update');

    Route::delete('bill-payments/{billPayment}', [BillPaymentController::class, 'destroy'])
        ->name('bill-payments.destroy');

    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])
        ->name('invoices');

    Route::get('invoices/create', [InvoiceController::class, 'create'])
        ->name('invoices.create');

    Route::post('invoices', [InvoiceController::class, 'store'])
        ->name('invoices.store');

    // Invoice number validation (must be before parameterized routes)
    Route::get('invoices/validate-number', [InvoiceController::class, 'validateInvoiceNumber'])
        ->name('invoices.validate-number');

    // Customer search and creation for invoices (must be before parameterized routes)
    Route::get('invoices/customers/search', [InvoiceController::class, 'searchCustomers'])
        ->name('invoices.customers.search');

    Route::post('invoices/customers/create-or-find', [InvoiceController::class, 'createOrFindCustomer'])
        ->name('invoices.customers.create-or-find');

    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])
        ->name('invoices.show');

    Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])
        ->name('invoices.edit');

    Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])
        ->name('invoices.update');

    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])
        ->name('invoices.destroy');

    Route::put('invoices/{invoice}/mark-as-sent', [InvoiceController::class, 'markAsSent'])
        ->name('invoices.mark-as-sent');

    Route::put('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])
        ->name('invoices.cancel');

    // Add payment recording and status management routes
    Route::post('invoices/{invoice}/record-payment', [InvoiceController::class, 'recordPayment'])
        ->name('invoices.record-payment');

    Route::put('invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])
        ->name('invoices.mark-as-paid');

    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])
        ->name('invoices.print');

    Route::get('invoices/{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf'])
        ->name('invoices.download-pdf');

    // Expense Claims
    Route::get('expense-claims', [ExpenseClaimController::class, 'index'])
        ->name('expense-claims');

    Route::get('expense-claims/create', [ExpenseClaimController::class, 'create'])
        ->name('expense-claims.create');

    Route::post('expense-claims', [ExpenseClaimController::class, 'store'])
        ->name('expense-claims.store');

    Route::post('expense-claims/auto-save', [ExpenseClaimController::class, 'autoSave'])
        ->name('expense-claims.auto-save');

    Route::get('expense-claims/{expenseClaim}', [ExpenseClaimController::class, 'show'])
        ->name('expense-claims.show');

    Route::get('expense-claims/{expenseClaim}/edit', [ExpenseClaimController::class, 'edit'])
        ->name('expense-claims.edit');

    Route::put('expense-claims/{expenseClaim}', [ExpenseClaimController::class, 'update'])
        ->name('expense-claims.update');

    Route::delete('expense-claims/{expenseClaim}', [ExpenseClaimController::class, 'destroy'])
        ->name('expense-claims.destroy');

    Route::post('expense-claims/{expenseClaim}/submit', [ExpenseClaimController::class, 'submit'])
        ->name('expense-claims.submit');

    Route::post('expense-claims/{expenseClaim}/approve', [ExpenseClaimController::class, 'approve'])
        ->name('expense-claims.approve');

    Route::post('expense-claims/{expenseClaim}/reject', [ExpenseClaimController::class, 'reject'])
        ->name('expense-claims.reject');

    // Expense Claim Payments
    Route::get('expense-claim-payments/create', [App\Http\Controllers\ExpenseClaimPaymentController::class, 'create'])
        ->name('expense-claim-payments.create');

    Route::post('expense-claim-payments', [App\Http\Controllers\ExpenseClaimPaymentController::class, 'store'])
        ->name('expense-claim-payments.store');

    Route::get('expense-claim-payments/{expenseClaimPayment}/edit', [App\Http\Controllers\ExpenseClaimPaymentController::class, 'edit'])
        ->name('expense-claim-payments.edit');

    Route::put('expense-claim-payments/{expenseClaimPayment}', [App\Http\Controllers\ExpenseClaimPaymentController::class, 'update'])
        ->name('expense-claim-payments.update');

    Route::delete('expense-claim-payments/{expenseClaimPayment}', [App\Http\Controllers\ExpenseClaimPaymentController::class, 'destroy'])
        ->name('expense-claim-payments.destroy');







    // Cheque Payments
    Route::get('cheque-payments', [ChequePaymentController::class, 'index'])
        ->name('cheque-payments');

    Route::get('cheque-payments/create', [ChequePaymentController::class, 'create'])
        ->name('cheque-payments.create');

    Route::post('cheque-payments', [ChequePaymentController::class, 'store'])
        ->name('cheque-payments.store');

    Route::get('cheque-payments/{chequePayment}', [ChequePaymentController::class, 'show'])
        ->name('cheque-payments.show');

    Route::get('cheque-payments/{chequePayment}/edit', [ChequePaymentController::class, 'edit'])
        ->name('cheque-payments.edit');

    Route::put('cheque-payments/{chequePayment}', [ChequePaymentController::class, 'update'])
        ->name('cheque-payments.update');

    Route::delete('cheque-payments/{chequePayment}', [ChequePaymentController::class, 'destroy'])
        ->name('cheque-payments.destroy');

    Route::patch('cheque-payments/{chequePayment}/mark-issued', [ChequePaymentController::class, 'markAsIssued'])
        ->name('cheque-payments.mark-issued');

    Route::patch('cheque-payments/{chequePayment}/mark-cleared', [ChequePaymentController::class, 'markAsCleared'])
        ->name('cheque-payments.mark-cleared');

    Route::patch('cheque-payments/{chequePayment}/cancel', [ChequePaymentController::class, 'cancel'])
        ->name('cheque-payments.cancel');
});
