<template>
  <div>
    <Head title="Payments" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Payments</h1>
      <p class="text-gray-600">Manage and track all payment transactions</p>
    </div>

    <!-- Admin Summary Cards -->
    <div v-if="isAdmin" class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-8">
      <!-- <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Payments</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ summary.total_payments.toLocaleString() }}</p>
            <p class="text-xs text-gray-500 mt-1 hidden lg:block">All payment records</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Amount</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">
                {{ $formatAmount(summary.total_amount) }}
            </p>
            <p class="text-xs text-gray-500 mt-1 hidden lg:block">All payments combined</p>
          </div>
        </div>
      </div> -->

      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">This Month</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">
                {{ $formatAmount(summary.this_month) }}
            </p>
            <p class="text-xs text-gray-500 mt-1 hidden lg:block">Current month payments</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Today</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">
                {{ $formatAmount(summary.today) }}
            </p>
            <p class="text-xs text-gray-500 mt-1 hidden lg:block">Today's payments</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-3 md:p-6 mb-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
        <h2 class="text-lg font-semibold">Filter Payments</h2>
        <Link v-if="canCreateChequePayments" class="btn-kingbaker whitespace-nowrap" href="/cheque-payments/create">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
          </svg>
          <span class="hidden sm:inline">Create A Payment</span>
          <span class="sm:hidden">Create</span>
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

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4 gap-3">
          <div v-if="selectedPayments.length > 0" class="text-sm text-gray-600">
            {{ selectedPayments.length }} payment(s) selected
            <button
              @click="clearSelection"
              type="button"
              class="ml-2 text-brand-600 hover:text-brand-800 underline"
            >
              Clear selection
            </button>
          </div>
          <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 gap-2">
            <button
              @click="reset"
              class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 whitespace-nowrap"
            >
              Clear Filters
            </button>
            <div class="text-sm text-gray-500 whitespace-nowrap">
              Showing {{ payments.data?.length || 0 }} of {{ payments.total || 0 }} payments
            </div>
          </div>
        </div>
    </div>

    <!-- Cheque Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-3 md:px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Payments</h2>
      </div>
      <div class="overflow-x-auto -mx-3 md:mx-0">
        <div class="inline-block min-w-full align-middle">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-2 md:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Payment</th>
                <th class="px-2 md:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">Payee</th>
                <th class="px-2 md:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Amount</th>
                <th class="px-2 md:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Category</th>
                <th class="px-2 md:px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                <th class="px-2 md:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="payment in payments.data"
                :key="payment.id"
                :class="[
                  'hover:bg-gray-50 cursor-pointer',
                  selectedPayments.includes(payment.id) ? 'bg-blue-50 border-l-4 border-blue-500' : ''
                ]"
                @click="toggleSelection(payment.id)"
              >
                <td class="px-2 md:px-3 py-3 whitespace-nowrap">
                  <div>
                    <div class="text-xs font-medium text-gray-900">{{ payment.payment_number }}</div>
                    <div class="text-xs text-gray-500">{{ $formatDate(payment.payment_date) }}</div>
                  </div>
                </td>
                <td class="px-2 md:px-3 py-3">
                  <div class="max-w-32">
                    <div class="text-xs font-medium text-gray-900 truncate" :title="payment.payee">{{ payment.payee }}</div>
                    <div class="text-xs text-gray-500 truncate" :title="payment.description">
                      {{ payment.description ? payment.description.substring(0, 30) + (payment.description.length > 30 ? '...' : '') : '' }}
                    </div>
                  </div>
                </td>
                <td class="px-2 md:px-3 py-3 whitespace-nowrap">
                  <div>
                    <div class="text-xs font-medium text-gray-900">
                      {{ $formatAmount(payment.amount) }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ getPaymentModeLabel(payment.payment_mode) }}
                    </div>
                  </div>
                </td>
                <td class="px-2 md:px-3 py-3">
                  <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 truncate">
                    {{ getCategoryLabel(payment.payment_category) }}
                  </span>
                </td>
                <td class="px-2 md:px-3 py-3">
                  <div>
                    <div class="mb-1">
                      <span :class="getStatusBadge(payment.status)" class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full truncate">
                        {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                      </span>
                    </div>
                    <div class="text-xs text-gray-500 truncate max-w-24" :title="payment.bank_account?.name">
                      {{ payment.bank_account?.name || '-' }}
                    </div>
                  </div>
                </td>
                <td class="px-2 md:px-3 py-3 whitespace-nowrap text-center">
                  <div class="flex items-center justify-center space-x-1">
                    <Link v-if="canViewChequePayments" :href="`/cheque-payments/${payment.id}`" class="text-brand-600 hover:text-brand-900" title="View Payment">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </Link>
                    <Link v-if="canEditChequePayments" :href="`/cheque-payments/${payment.id}/edit`" class="text-indigo-600 hover:text-indigo-900" title="Edit Payment">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                      </svg>
                    </Link>
                    <button
                      v-if="payment.status === 'pending'"
                      @click.stop="markAsIssued(payment.id)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Mark as Issued"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </button>
                    <button
                      v-if="payment.status === 'issued'"
                      @click.stop="markAsCleared(payment.id)"
                      class="text-green-600 hover:text-green-900"
                      title="Mark as Cleared"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Pagination -->
      <div class="px-3 md:px-6 py-4 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="text-sm text-gray-700 whitespace-nowrap">
            Showing {{ payments.from || 0 }} to {{ payments.to || 0 }} of {{ payments.total || 0 }} results
          </div>
          <Pagination v-if="payments.links" :links="payments.links" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Pagination from '@/Shared/Pagination.vue'
import { usePermissions } from '@/composables/usePermissions.js'
import { formatterMixin } from '@/Utils/formatters'
import throttle from 'lodash/throttle'
import pickBy from 'lodash/pickBy'
import mapValues from 'lodash/mapValues'

export default {
  components: {
    Head,
    Link,
    Pagination,
  },
  mixins: [formatterMixin],
  layout: Layout,
  setup() {
    const { canCreateChequePayments, canViewChequePayments, canEditChequePayments, isAdmin } = usePermissions()
    return { canCreateChequePayments, canViewChequePayments, canEditChequePayments, isAdmin }
  },
  props: {
    payments: Object,
    summary: Object,
    filters: {
      type: Object,
      default: () => ({})
    },
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
      selectedPayments: [],
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/cheque-payments', pickBy(this.form), {
          preserveState: true,
          preserveScroll: true,
        })
      }, 300),
    },
  },
  methods: {
    search() {
      this.$inertia.get('/cheque-payments', pickBy(this.form), {
        preserveState: true,
        preserveScroll: true,
      })
    },
    reset() {
      this.form = mapValues(this.form, () => '')
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
        bill: 'Bill',
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
    toggleSelection(paymentId) {
      const index = this.selectedPayments.indexOf(paymentId)
      if (index > -1) {
        this.selectedPayments.splice(index, 1)
      } else {
        this.selectedPayments.push(paymentId)
      }
    },
    clearSelection() {
      this.selectedPayments = []
    },
  },
}
</script>