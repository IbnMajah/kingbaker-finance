<template>
  <div>
    <Head title="Invoices" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Invoices</h1>
      <p class="text-gray-600">Generate and track customer invoices for bulk sales, credit customers, and monthly billing</p>
    </div>

    <!-- Admin Summary Cards -->
    <div v-if="isAdmin" class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Invoices</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ summary.total_invoices?.toLocaleString() || 0 }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Amount</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ $formatAmount(summary.total_amount) }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Amount Paid</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ $formatAmount(summary.total_paid) }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Pending Amount</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ $formatAmount(summary.pending_amount) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Filter Invoices</h2>
        <Link v-if="canCreateInvoices" class="btn-kingbaker" href="/invoices/create">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
          </svg>
          <span>Create Invoice</span>
        </Link>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="form.search"
            type="text"
            placeholder="Invoice #, customer name, email..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Statuses</option>
            <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Type</label>
          <select v-model="form.invoice_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Types</option>
            <option v-for="type in invoiceTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Customer Type</label>
          <select v-model="form.customer_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Customer Types</option>
            <option v-for="type in customerTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label>
          <select v-model="form.bank_account_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Accounts</option>
            <option v-for="account in bankAccounts" :key="account.id" :value="account.id">{{ account.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
          <select v-model="form.branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
          <input
            v-model="form.date_from"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
          <input
            v-model="form.date_to"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
      </div>
      <div class="flex items-center mt-4 space-x-4">
        <button @click="clearFilters" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
          Clear Filters
        </button>
        <div class="text-sm text-gray-500">
          Showing {{ invoices.data?.length || 0 }} of {{ invoices.total || 0 }} invoices
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Invoices</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="hidden md:table-cell px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="w-24 px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
              <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="hidden lg:table-cell px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
              <th class="w-20 px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
              <td class="px-3 py-3 whitespace-nowrap">
                <div>
                  <div class="text-xs font-medium text-gray-900">{{ invoice.invoice_number }}</div>
                  <div class="text-xs text-gray-500">{{ $formatDate(invoice.invoice_date) }}</div>
                </div>
              </td>
              <td class="px-3 py-3">
                <div class="max-w-32">
                  <div class="text-xs font-medium text-gray-900 truncate">{{ invoice.customer_name }}</div>
                  <div class="text-xs text-gray-500 truncate">{{ invoice.customer_email || invoice.customer_phone || 'No contact' }}</div>
                </div>
              </td>
              <td class="hidden md:table-cell px-3 py-3">
                <div class="max-w-28">
                  <div class="text-xs font-medium text-gray-900 truncate">{{ getInvoiceTypeLabel(invoice.invoice_type) }}</div>
                  <div class="text-xs text-gray-500 truncate">{{ getCustomerTypeLabel(invoice.customer_type) }}</div>
                </div>
              </td>
              <td class="px-3 py-3 whitespace-nowrap">
                <div>
                  <div class="text-xs font-medium text-gray-900">
                    {{ $formatAmount(invoice.amount) }}
                  </div>
                  <div v-if="invoice.amount_paid > 0" class="text-xs text-gray-500">
                    {{ $formatAmount(invoice.amount_paid) }}
                  </div>
                </div>
              </td>
              <td class="px-3 py-3 whitespace-nowrap">
                <div class="text-xs text-gray-900">{{ $formatDate(invoice.due_date) }}</div>
              </td>
              <td class="px-3 py-3 whitespace-nowrap text-center">
                <span v-if="invoice.status === 'paid'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Paid
                </span>
                <span v-else-if="invoice.status === 'pending'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                  Pending
                </span>
                <span v-else-if="invoice.status === 'overdue' && invoice.status !== 'paid'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  Overdue
                </span>
                <span v-else-if="invoice.status === 'partially_paid'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  Part. Paid
                </span>
                <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  {{ invoice.status || 'Unknown' }}
                </span>
              </td>
              <td class="hidden lg:table-cell px-3 py-3">
                <div class="text-xs text-gray-900 max-w-24 truncate">{{ invoice.bank_account?.name || '-' }}</div>
              </td>
              <td class="px-3 py-3 whitespace-nowrap text-center">
                <div class="flex items-center justify-center space-x-1">
                  <!-- View Button -->
                  <Link v-if="canViewInvoices" :href="`/invoices/${invoice.id}`" class="text-blue-600 hover:text-blue-900" title="View Invoice">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </Link>

                  <!-- Edit Button -->
                  <Link v-if="canEditInvoices" :href="`/invoices/${invoice.id}/edit`" class="text-brand-600 hover:text-brand-900" title="Edit Invoice">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                  </Link>

                  <!-- Print Button -->
                  <Link :href="`/invoices/${invoice.id}/print`" class="hidden sm:inline-block text-purple-600 hover:text-purple-900" title="Print Invoice">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                  </Link>

                  <!-- Delete Button -->
                  <button @click="destroy(invoice)" class="hidden sm:inline-block text-red-600 hover:text-red-900" title="Delete Invoice">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!invoices?.data?.length">
              <td colspan="8" class="px-3 py-12 text-center text-gray-500">
                No invoices found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div v-if="invoices?.data?.length" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ invoices.from || 0 }} to {{ invoices.to || 0 }} of {{ invoices.total || 0 }} results
          </div>
          <Pagination v-if="invoices.links" :links="invoices.links" />
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
    const { canCreateInvoices, canViewInvoices, canEditInvoices, isAdmin } = usePermissions()
    return { canCreateInvoices, canViewInvoices, canEditInvoices, isAdmin }
  },
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
    destroy(invoice) {
      if (confirm(`Are you sure you want to delete invoice ${invoice.invoice_number}?`)) {
        this.$inertia.delete(`/invoices/${invoice.id}`, {
          onSuccess: () => {
            console.log('Invoice deleted successfully')
          },
          onError: (errors) => {
            console.error('Error deleting invoice:', errors)
            alert('Error deleting invoice. Please try again.')
          }
        })
      }
    },
  },
}
</script>