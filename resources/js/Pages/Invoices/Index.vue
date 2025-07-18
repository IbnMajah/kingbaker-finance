<template>
  <div>
    <Head title="Invoices" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Invoices</h1>
      <p class="text-gray-600">Generate and track customer invoices for bulk sales, credit customers, and monthly billing</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Invoices</p>
            <p class="text-2xl font-semibold text-gray-900">{{ summary.total_invoices?.toLocaleString() || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Amount</p>
            <p class="text-2xl font-semibold text-gray-900">
                {{ $formatAmount(summary.total_amount) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Amount Paid</p>
            <p class="text-2xl font-semibold text-gray-900">
                {{ $formatAmount(summary.total_paid) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Pending Amount</p>
            <p class="text-2xl font-semibold text-gray-900">
                {{ $formatAmount(summary.pending_amount) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold">Filters</h3>
      </div>
      <div class="p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="form.search"
              type="text"
              placeholder="Invoice #, customer name, email..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            />
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="form.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            >
              <option value="">All Statuses</option>
              <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </div>

          <!-- Invoice Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Type</label>
            <select
              v-model="form.invoice_type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            >
              <option value="">All Types</option>
              <option v-for="type in invoiceTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
          </div>

          <!-- Customer Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Type</label>
            <select
              v-model="form.customer_type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            >
              <option value="">All Customer Types</option>
              <option v-for="type in customerTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Bank Account -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label>
            <select
              v-model="form.bank_account_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            >
              <option value="">All Accounts</option>
              <option v-for="account in bankAccounts" :key="account.id" :value="account.id">{{ account.name }}</option>
            </select>
          </div>

          <!-- Branch -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
            <select
              v-model="form.branch_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            >
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>

          <!-- Date From -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
            <input
              v-model="form.date_from"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            />
          </div>

          <!-- Date To -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
            <input
              v-model="form.date_to"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
          <button
            @click="clearFilters"
            class="text-gray-600 hover:text-gray-800"
          >
            Clear Filters
          </button>
          <Link href="/invoices/create" class="btn-kingbaker">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Create Invoice
          </Link>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading" class="flex items-center justify-center p-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500"></div>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
              <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</div>
                  <div class="text-sm text-gray-500">{{ $formatDate(invoice.invoice_date) }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ invoice.customer_name }}</div>
                  <div class="text-sm text-gray-500">{{ invoice.customer_email || invoice.customer_phone || 'No contact' }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ getInvoiceTypeLabel(invoice.invoice_type) }}</div>
                  <div class="text-sm text-gray-500">{{ getCustomerTypeLabel(invoice.customer_type) }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ $formatAmount(invoice.amount) }}
                  </div>
                  <div v-if="invoice.amount_paid > 0" class="text-sm text-gray-500">
                    Paid: {{ $formatAmount(invoice.amount_paid) }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ invoice.due_date ? $formatDate(invoice.due_date) : 'No due date' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(invoice.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getStatusLabel(invoice.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ invoice.bank_account?.name || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <!-- View -->
                  <Link
                    :href="`/invoices/${invoice.id}`"
                    class="text-gray-600 hover:text-blue-600 p-1 rounded-md hover:bg-blue-50"
                    title="View Details"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </Link>

                  <!-- Edit -->
                  <Link
                    :href="`/invoices/${invoice.id}/edit`"
                    class="text-gray-600 hover:text-green-600 p-1 rounded-md hover:bg-green-50"
                    title="Edit Invoice"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </Link>

                  <!-- Mark as Sent (only for draft) -->
                  <button
                    v-if="invoice.status === 'draft'"
                    @click="markAsSent(invoice)"
                    class="text-gray-600 hover:text-blue-600 p-1 rounded-md hover:bg-blue-50"
                    title="Mark as Sent"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                  </button>

                  <!-- Record Payment (for sent/partially paid/overdue) -->
                  <button
                    v-if="['sent', 'partially_paid', 'overdue'].includes(invoice.status)"
                    @click="openPaymentModal(invoice)"
                    class="text-gray-600 hover:text-green-600 p-1 rounded-md hover:bg-green-50"
                    title="Record Payment"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                  </button>

                  <!-- Mark as Paid (for sent/partially paid/overdue) -->
                  <button
                    v-if="['sent', 'partially_paid', 'overdue'].includes(invoice.status)"
                    @click="markAsPaid(invoice)"
                    class="text-gray-600 hover:text-purple-600 p-1 rounded-md hover:bg-purple-50"
                    title="Mark as Paid"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </button>

                  <!-- Print -->
                  <Link
                    :href="`/invoices/${invoice.id}/print`"
                    target="_blank"
                    class="text-gray-600 hover:text-gray-800 p-1 rounded-md hover:bg-gray-100"
                    title="Print Invoice"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                  </Link>

                  <!-- Download PDF -->
                  <Link
                    :href="`/invoices/${invoice.id}/download-pdf`"
                    class="text-gray-600 hover:text-indigo-600 p-1 rounded-md hover:bg-indigo-50"
                    title="Download PDF"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </Link>

                  <!-- Cancel (only for draft/sent with no payments) -->
                  <button
                    v-if="['draft', 'sent'].includes(invoice.status) && invoice.amount_paid === 0"
                    @click="cancelInvoice(invoice)"
                    class="text-gray-600 hover:text-red-600 p-1 rounded-md hover:bg-red-50"
                    title="Cancel Invoice"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!invoices.data?.length">
              <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M34 40H14a4 4 0 01-4-4V12a4 4 0 014-4h11.172a4 4 0 012.828 1.172L39.828 21a4 4 0 011.172 2.828V36a4 4 0 01-4 4z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                  <path d="M24 32h8M24 24h8M16 32h4M16 24h4M16 16h4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <p class="mt-4 text-lg">No invoices found</p>
                <p class="text-gray-400">Get started by creating your first invoice.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="invoices.data?.length" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <Link
              v-if="invoices.prev_page_url"
              :href="invoices.prev_page_url"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Previous
            </Link>
            <Link
              v-if="invoices.next_page_url"
              :href="invoices.next_page_url"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Next
            </Link>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ invoices.from || 0 }}</span> to <span class="font-medium">{{ invoices.to || 0 }}</span> of <span class="font-medium">{{ invoices.total || 0 }}</span> results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <Link
                  v-if="invoices.prev_page_url"
                  :href="invoices.prev_page_url"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                  <span class="sr-only">Previous</span>
                  <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                </Link>
                <span v-for="page in invoices.links?.slice(1, -1)" :key="page.label">
                  <Link
                    v-if="page.url"
                    :href="page.url"
                    :class="page.active ? 'bg-brand-50 border-brand-500 text-brand-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                  >
                    {{ page.label }}
                  </Link>
                  <span
                    v-else
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                  >
                    {{ page.label }}
                  </span>
                </span>
                <Link
                  v-if="invoices.next_page_url"
                  :href="invoices.next_page_url"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                  <span class="sr-only">Next</span>
                  <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                  </svg>
                </Link>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Record Payment</h3>
          <div v-if="selectedInvoice" class="mb-4 p-3 bg-gray-50 rounded-md">
            <p class="text-sm text-gray-600">Invoice: {{ selectedInvoice.invoice_number }}</p>
            <p class="text-sm text-gray-600">Customer: {{ selectedInvoice.customer_name }}</p>
            <p class="text-sm text-gray-600">Remaining: GMD {{ selectedInvoice.remaining_amount?.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
          </div>

          <form @submit.prevent="submitPayment">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount</label>
              <input
                v-model="paymentForm.amount"
                type="number"
                step="0.01"
                :max="selectedInvoice?.remaining_amount"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
              <select
                v-model="paymentForm.payment_method"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              >
                <option value="">Select method</option>
                <option value="cash">Cash</option>
                <option value="cheque">Cheque</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="mobile_money">Mobile Money</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Reference</label>
              <input
                v-model="paymentForm.payment_reference"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                placeholder="Check #, Transfer ID, etc."
              />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
              <input
                v-model="paymentForm.payment_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              />
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea
                v-model="paymentForm.notes"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                rows="2"
                placeholder="Optional notes..."
              ></textarea>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closePaymentModal"
                class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="paymentForm.processing"
                class="px-4 py-2 bg-brand-600 text-white rounded-md hover:bg-brand-700 disabled:opacity-50"
              >
                {{ paymentForm.processing ? 'Recording...' : 'Record Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import { formatterMixin } from '@/Utils/formatters'
import throttle from 'lodash/throttle'
import pickBy from 'lodash/pickBy'
import mapValues from 'lodash/mapValues'

export default {
  components: {
    Head,
    Link,
  },
  mixins: [formatterMixin],
  layout: Layout,
  props: {
    filters: {
      type: Object,
      default: () => ({})
    },
    invoices: {
      type: Object,
      default: () => ({ data: [] })
    },
    summary: {
      type: Object,
      default: () => ({})
    },
    bankAccounts: Array,
    branches: Array,
    invoiceTypes: Array,
    customerTypes: Array,
    statuses: Array,
  },
  data() {
    return {
      loading: false,
      form: {
        search: this.filters.search || '',
        status: this.filters.status || '',
        invoice_type: this.filters.invoice_type || '',
        customer_type: this.filters.customer_type || '',
        bank_account_id: this.filters.bank_account_id || '',
        branch_id: this.filters.branch_id || '',
        date_from: this.filters.date_from || '',
        date_to: this.filters.date_to || '',
      },
      showPaymentModal: false,
      selectedInvoice: null,
      paymentForm: {
        amount: '',
        payment_method: '',
        payment_reference: '',
        payment_date: '',
        notes: '',
        processing: false,
      },
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.loading = true
        this.$inertia.get('/invoices', pickBy(this.form), {
          preserveState: true,
          onFinish: () => {
            this.loading = false
          }
        })
      }, 150),
    },
  },
  methods: {
    clearFilters() {
      this.form = mapValues(this.form, () => '')
    },
    getStatusBadgeClass(status) {
      const classes = {
        'draft': 'bg-gray-100 text-gray-800',
        'sent': 'bg-blue-100 text-blue-800',
        'paid': 'bg-green-100 text-green-800',
        'partially_paid': 'bg-yellow-100 text-yellow-800',
        'overdue': 'bg-red-100 text-red-800',
        'cancelled': 'bg-gray-100 text-gray-800',
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },
    getStatusLabel(status) {
      const labels = {
        'draft': 'Draft',
        'sent': 'Sent',
        'paid': 'Paid',
        'partially_paid': 'Partially Paid',
        'overdue': 'Overdue',
        'cancelled': 'Cancelled',
      }
      return labels[status] || status
    },
    getInvoiceTypeLabel(type) {
      const invoice = this.invoiceTypes?.find(t => t.value === type)
      return invoice?.label || type
    },
    getCustomerTypeLabel(type) {
      const customer = this.customerTypes?.find(t => t.value === type)
      return customer?.label || type
    },
    markAsSent(invoice) {
      this.$inertia.put(`/invoices/${invoice.id}/mark-as-sent`, {}, {
        onSuccess: () => {
          this.$page.props.flash?.success &&
          console.log('Invoice marked as sent successfully')
        },
        onError: (errors) => {
          console.error('Error marking invoice as sent:', errors)
          alert('Error marking invoice as sent. Please try again.')
        }
      })
    },
    openPaymentModal(invoice) {
      this.selectedInvoice = invoice
      this.showPaymentModal = true
      // Set default payment date to today
      this.paymentForm.payment_date = new Date().toISOString().split('T')[0]
      // Set default amount to remaining amount
      this.paymentForm.amount = invoice.remaining_amount || (invoice.amount - invoice.amount_paid)
    },
    markAsPaid(invoice) {
      if (confirm(`Are you sure you want to mark invoice ${invoice.invoice_number} as fully paid?`)) {
        this.$inertia.patch(`/invoices/${invoice.id}/mark-as-paid`, {}, {
          onSuccess: () => {
            console.log('Invoice marked as paid successfully')
          },
          onError: (errors) => {
            console.error('Error marking invoice as paid:', errors)
            alert('Error marking invoice as paid. Please try again.')
          }
        })
      }
    },
    cancelInvoice(invoice) {
      if (confirm(`Are you sure you want to cancel invoice ${invoice.invoice_number}? This action cannot be undone.`)) {
        this.$inertia.put(`/invoices/${invoice.id}/cancel`, {}, {
          onSuccess: () => {
            console.log('Invoice cancelled successfully')
          },
          onError: (errors) => {
            console.error('Error cancelling invoice:', errors)
            alert('Error cancelling invoice. Please try again.')
          }
        })
      }
    },
    submitPayment() {
      this.paymentForm.processing = true

      this.$inertia.post(`/invoices/${this.selectedInvoice.id}/record-payment`, {
        amount: parseFloat(this.paymentForm.amount),
        payment_method: this.paymentForm.payment_method,
        payment_reference: this.paymentForm.payment_reference,
        payment_date: this.paymentForm.payment_date,
        notes: this.paymentForm.notes,
      }, {
        onSuccess: () => {
          this.closePaymentModal()
          this.resetPaymentForm()
          console.log('Payment recorded successfully')
        },
        onError: (errors) => {
          console.error('Error recording payment:', errors)
          alert('Error recording payment. Please check the form and try again.')
          this.paymentForm.processing = false
        },
        onFinish: () => {
          this.paymentForm.processing = false
        }
      })
    },
    closePaymentModal() {
      this.showPaymentModal = false
      this.selectedInvoice = null
      this.resetPaymentForm()
    },
    resetPaymentForm() {
      this.paymentForm = {
        amount: '',
        payment_method: '',
        payment_reference: '',
        payment_date: '',
        notes: '',
        processing: false,
      }
    },
  },
}
</script>