<template>
  <div>
    <Head title="Cheque Payments" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Cheque Payments</h1>
      <p class="text-gray-600">Manage and track all cheque payment transactions</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Payments</p>
            <p class="text-2xl font-semibold text-gray-900">{{ summary.total_payments.toLocaleString() }}</p>
            <p class="text-xs text-gray-500 mt-1">All payment records</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Amount</p>
            <p class="text-2xl font-semibold text-gray-900">
                {{ $formatAmount(summary.total_amount) }}
            </p>
            <p class="text-xs text-gray-500 mt-1">All payments combined</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">This Month</p>
            <p class="text-2xl font-semibold text-gray-900">
                {{ $formatAmount(summary.this_month) }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Current month payments</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Today</p>
            <p class="text-2xl font-semibold text-gray-900">
                {{ $formatAmount(summary.today) }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Today's payments</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Filter Cheque Payments</h2>
        <Link class="btn-kingbaker" href="/cheque-payments/create">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
          </svg>
          <span>Create Cheque Payment</span>
        </Link>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="form.search"
              type="text"
              placeholder="Payee, description, reference..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            />
          </div>

          <!-- Payment Category -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select
              v-model="form.payment_category"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            >
              <option value="">All Categories</option>
              <option v-for="category in paymentCategories" :key="category.value" :value="category.value">{{ category.label }}</option>
            </select>
          </div>

          <!-- Payment Mode -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Mode</label>
            <select
              v-model="form.payment_mode"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            >
              <option value="">All Modes</option>
              <option v-for="mode in paymentModes" :key="mode.value" :value="mode.value">{{ mode.label }}</option>
            </select>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
            <input
              v-model="form.date_from"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            />
          </div>

          <!-- Date To -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
            <input
              v-model="form.date_to"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-4">
          <button
            @click="reset"
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
          >
            Reset
          </button>
          <button
            @click="search"
            type="button"
            class="px-4 py-2 text-sm font-medium text-white bg-brand-600 border border-transparent rounded-md hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
          >
            Search
          </button>
        </div>
    </div>

    <!-- Cheque Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Cheque Payments</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payee</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ payment.payment_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ payment.payee }}</div>
                <div class="text-sm text-gray-500">{{ payment.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ $formatAmount(payment.amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $formatDate(payment.payment_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                  {{ getCategoryLabel(payment.payment_category) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                  {{ getPaymentModeLabel(payment.payment_mode) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadge(payment.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ payment.bank_account?.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <Link :href="`/cheque-payments/${payment.id}`" class="text-brand-600 hover:text-brand-900">View</Link>
                  <Link :href="`/cheque-payments/${payment.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                  <button
                    v-if="payment.status === 'pending'"
                    @click="markAsIssued(payment.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Issue
                  </button>
                  <button
                    v-if="payment.status === 'issued'"
                    @click="markAsCleared(payment.id)"
                    class="text-green-600 hover:text-green-900"
                  >
                    Clear
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex-1 flex justify-between sm:hidden">
          <Link
            v-if="payments.prev_page_url"
            :href="payments.prev_page_url"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Previous
          </Link>
          <Link
            v-if="payments.next_page_url"
            :href="payments.next_page_url"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Next
          </Link>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ payments.from }}</span>
              to
              <span class="font-medium">{{ payments.to }}</span>
              of
              <span class="font-medium">{{ payments.total }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <Link
                v-if="payments.prev_page_url"
                :href="payments.prev_page_url"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <span class="sr-only">Previous</span>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </Link>

              <template v-for="(link, index) in payments.links" :key="index">
                <Link
                  v-if="!isNaN(link.label)"
                  :href="link.url"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    link.active
                      ? 'z-10 bg-brand-50 border-brand-500 text-brand-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  {{ link.label }}
                </Link>
              </template>

              <Link
                v-if="payments.next_page_url"
                :href="payments.next_page_url"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              >
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import { formatterMixin } from '@/Utils/formatters'

export default {
  components: {
    Head,
    Link,
  },
  mixins: [formatterMixin],
  layout: Layout,
  props: {
    payments: Object,
    summary: Object,
    filters: Object,
    bankAccounts: Array,
    branches: Array,
    paymentCategories: Array,
    paymentModes: Array,
    statuses: Array,
  },
  data() {
    return {
      form: {
        search: this.filters.search || '',
        payment_category: this.filters.payment_category || '',
        payment_mode: this.filters.payment_mode || '',
        bank_account_id: this.filters.bank_account_id || '',
        branch_id: this.filters.branch_id || '',
        date_from: this.filters.date_from || '',
        date_to: this.filters.date_to || '',
        status: this.filters.status || '',
      },
    }
  },
  methods: {
    search() {
      this.$inertia.get('/cheque-payments', this.form, {
        preserveState: true,
        replace: true,
      })
    },
    reset() {
      this.form = {
        search: '',
        payment_category: '',
        payment_mode: '',
        bank_account_id: '',
        branch_id: '',
        date_from: '',
        date_to: '',
        status: '',
      }
      this.search()
    },
    getStatusBadge(status) {
      const badges = {
        pending: 'bg-yellow-100 text-yellow-800',
        issued: 'bg-blue-100 text-blue-800',
        cleared: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
      }
      return badges[status] || 'bg-gray-100 text-gray-800'
    },
    getCategoryLabel(category) {
      const labels = {
        vendor_payment: 'Vendor Payment',
        recurring_bill: 'Recurring Bill',
        staff_advance: 'Staff Advance',
        loan_payment: 'Loan Payment',
        institutional_payment: 'Institutional Payment',
        other_payment: 'Other Payment',
      }
      return labels[category] || 'Unknown'
    },
    getPaymentModeLabel(mode) {
      const labels = {
        cheque: 'Cheque',
        bank_transfer: 'Bank Transfer',
        cash: 'Cash',
        nafa: 'NAFA',
        wave: 'Wave',
        other: 'Other',
      }
      return labels[mode] || 'Unknown'
    },
    markAsIssued(id) {
      this.$inertia.patch(`/cheque-payments/${id}/mark-issued`)
    },
    markAsCleared(id) {
      this.$inertia.patch(`/cheque-payments/${id}/mark-cleared`)
    },
  },
}
</script>