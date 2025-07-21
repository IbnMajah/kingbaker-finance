<template>
  <div>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/invoices">Invoices</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ invoice.invoice_number }}
      </h1>
      <p class="text-gray-600">Invoice Details & Information</p>
    </div>

    <!-- Invoice Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Invoice Amount</div>
        <div class="text-2xl font-bold text-gray-900">
          {{ $formatAmount(invoice.amount) }}
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Amount Paid</div>
        <div class="text-2xl font-bold text-green-600">
          {{ $formatAmount(invoice.amount_paid) }}
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Balance Due</div>
        <div class="text-2xl font-bold" :class="balanceDue > 0 ? 'text-red-600' : 'text-green-600'">
          {{ $formatAmount(balanceDue) }}
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Status</div>
        <div class="text-2xl font-bold" :class="getStatusTextClass(invoice.status)">
          {{ getStatusLabel(invoice.status) }}
        </div>
      </div>
    </div>

    <!-- Invoice Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Invoice Details</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <div class="text-sm font-medium text-gray-500">Invoice Number</div>
          <div class="text-gray-900 font-mono">{{ invoice.invoice_number }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Invoice Date</div>
          <div class="text-gray-900">{{ $formatDate(invoice.invoice_date) }}</div>
        </div>
        <div v-if="invoice.due_date">
          <div class="text-sm font-medium text-gray-500">Due Date</div>
          <div class="text-gray-900">{{ $formatDate(invoice.due_date) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Invoice Type</div>
          <div class="text-gray-900">{{ getInvoiceTypeLabel(invoice.invoice_type) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Customer Type</div>
          <div class="text-gray-900">{{ getCustomerTypeLabel(invoice.customer_type) }}</div>
        </div>
        <div v-if="invoice.billing_period">
          <div class="text-sm font-medium text-gray-500">Billing Period</div>
          <div class="text-gray-900">{{ invoice.billing_period }}</div>
        </div>
        <div v-if="invoice.bank_account">
          <div class="text-sm font-medium text-gray-500">Bank Account</div>
          <div class="text-gray-900">{{ invoice.bank_account.name }}</div>
        </div>
        <div v-if="invoice.branch">
          <div class="text-sm font-medium text-gray-500">Branch</div>
          <div class="text-gray-900">{{ invoice.branch.name }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Created By</div>
          <div class="text-gray-900">{{ invoice.creator ? `${invoice.creator.first_name} ${invoice.creator.last_name}` : 'Unknown' }}</div>
        </div>
      </div>
    </div>

    <!-- Customer Information -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="text-sm font-medium text-gray-500">Customer Name</div>
          <div class="text-gray-900 text-lg font-medium">{{ invoice.customer_name }}</div>
        </div>
        <div v-if="invoice.customer_email">
          <div class="text-sm font-medium text-gray-500">Email</div>
          <div class="text-gray-900">
            <a :href="`mailto:${invoice.customer_email}`" class="text-brand-600 hover:text-brand-500">
              {{ invoice.customer_email }}
            </a>
          </div>
        </div>
        <div v-if="invoice.customer_phone">
          <div class="text-sm font-medium text-gray-500">Phone</div>
          <div class="text-gray-900">
            <a :href="`tel:${invoice.customer_phone}`" class="text-brand-600 hover:text-brand-500">
              {{ invoice.customer_phone }}
            </a>
          </div>
        </div>
        <div v-if="invoice.customer_address">
          <div class="text-sm font-medium text-gray-500">Address</div>
          <div class="text-gray-900">{{ invoice.customer_address }}</div>
        </div>
      </div>
    </div>

    <!-- Description -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Description</h2>
      <div class="bg-gray-50 p-4 rounded-md">
        <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">
          {{ invoice.description || 'No description provided.' }}
        </p>
      </div>
    </div>

    <!-- Invoice Items -->
    <div v-if="invoice.items && invoice.items.length > 0" class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Invoice Items</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Description
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Unit Price
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Quantity
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Total
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in invoice.items" :key="item.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.description }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $formatAmount(item.unit_price) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.quantity }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ $formatAmount(item.total) }}
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                Total Amount:
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                {{ $formatAmount(invoice.amount) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Notes -->
    <div v-if="invoice.notes" class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Notes</h2>
      <div class="bg-yellow-50 p-4 rounded-md border-l-4 border-yellow-400">
        <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ invoice.notes }}</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold mb-4">Actions</h2>
      <div class="flex flex-wrap gap-3">
        <Link
          :href="`/invoices/${invoice.id}/print`"
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          Print Invoice
        </Link>

        <Link
          :href="`/invoices/${invoice.id}/edit`"
          class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-md">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Edit Invoice
        </Link>

        <button
          v-if="balanceDue > 0"
          @click="showPaymentModal = true"
          class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
          </svg>
          Record Payment
        </button>

        <button
          v-if="invoice.status !== 'paid'"
          @click="markAsPaid"
          :disabled="processing"
          class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md disabled:opacity-50">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          {{ processing ? 'Processing...' : 'Mark as Paid' }}
        </button>

        <button
          @click="confirmDelete"
          class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
          Delete Invoice
        </button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full mx-4">
        <div class="flex items-center">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.966-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
        </div>
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Invoice</h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500">
              Are you sure you want to delete invoice #{{ invoice.invoice_number }}? This action cannot be undone.
            </p>
          </div>
        </div>
        <div class="mt-5 flex justify-center space-x-3">
          <button
            @click="showDeleteModal = false"
            class="bg-white hover:bg-gray-50 text-gray-900 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium">
            Cancel
          </button>
          <button
            @click="deleteInvoice"
            :disabled="processing"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50">
            {{ processing ? 'Deleting...' : 'Delete Invoice' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Record Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Record Payment</h3>
          <form @submit.prevent="recordPayment">
            <div class="space-y-4">
              <!-- Amount -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount *</label>
                <div class="relative">
                  <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                  <input
                    v-model.number="paymentForm.amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    :max="balanceDue"
                    class="w-full pl-12 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    :class="paymentForm.errors.amount ? 'border-red-300' : 'border-gray-300'"
                    placeholder="0.00"
                    required
                  />
                </div>
                <p class="mt-1 text-sm text-gray-500">Maximum: {{ $formatAmount(balanceDue) }}</p>
                <div v-if="paymentForm.errors.amount" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.amount }}</div>
              </div>

              <!-- Payment Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                <input
                  v-model="paymentForm.payment_date"
                  type="date"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="paymentForm.errors.payment_date ? 'border-red-300' : 'border-gray-300'"
                  required
                />
                <div v-if="paymentForm.errors.payment_date" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.payment_date }}</div>
              </div>

              <!-- Payment Method -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                <select
                  v-model="paymentForm.payment_method"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="paymentForm.errors.payment_method ? 'border-red-300' : 'border-gray-300'"
                  required
                >
                  <option value="">Select Method</option>
                  <option value="cash">Cash</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cheque">Cheque</option>
                  <option value="mobile_money">Mobile Money</option>
                  <option value="other">Other</option>
                </select>
                <div v-if="paymentForm.errors.payment_method" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.payment_method }}</div>
              </div>

              <!-- Payment Reference -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                <input
                  v-model="paymentForm.payment_reference"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="paymentForm.errors.payment_reference ? 'border-red-300' : 'border-gray-300'"
                  placeholder="Transaction reference, cheque number, etc."
                />
                <div v-if="paymentForm.errors.payment_reference" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.payment_reference }}</div>
              </div>

              <!-- Notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea
                  v-model="paymentForm.notes"
                  rows="3"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="paymentForm.errors.notes ? 'border-red-300' : 'border-gray-300'"
                  placeholder="Additional payment notes..."
                ></textarea>
                <div v-if="paymentForm.errors.notes" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.notes }}</div>
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button
                type="button"
                @click="showPaymentModal = false"
                class="bg-white hover:bg-gray-50 text-gray-900 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium">
                Cancel
              </button>
              <button
                type="submit"
                :disabled="paymentForm.processing"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50">
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
import { router } from '@inertiajs/vue3'

export default {
  components: {
    Head,
    Link,
  },
  layout: Layout,
  props: {
    invoice: Object,
    remaining_amount: Number,
  },
  data() {
    return {
      showDeleteModal: false,
      showPaymentModal: false,
      processing: false,
      paymentForm: this.$inertia.form({
        amount: '',
        payment_date: new Date().toISOString().split('T')[0],
        payment_method: '',
        payment_reference: '',
        notes: '',
      }),
    }
  },
  computed: {
    balanceDue() {
      return (this.invoice.amount || 0) - (this.invoice.amount_paid || 0)
    },
  },
  methods: {
    getStatusTextClass(status) {
      const classes = {
        'draft': 'text-gray-600',
        'sent': 'text-blue-600',
        'paid': 'text-green-600',
        'partially_paid': 'text-yellow-600',
        'overdue': 'text-red-600',
        'cancelled': 'text-gray-600',
      }
      return classes[status] || 'text-gray-600'
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
      const types = {
        'bulk_sales': 'Bulk Sales',
        'credit_customer': 'Credit Customer',
        'loans': 'Loans',
        'daily_supply': 'Daily Supply',
        'partner_billing': 'Partner Billing',
        'other': 'Other',
      }
      return types[type] || type
    },
    getCustomerTypeLabel(type) {
      const types = {
        'individual': 'Individual',
        'shop': 'Shop',
        'partner': 'Partner',
        'branch': 'Branch',
        'hotel': 'Hotel',
        'other': 'Other',
      }
      return types[type] || type
    },
    confirmDelete() {
      this.showDeleteModal = true
    },
    deleteInvoice() {
      this.processing = true
      router.delete(`/invoices/${this.invoice.id}`, {
        onSuccess: () => {
          // Redirect will be handled by the backend
        },
        onError: () => {
          this.processing = false
          // Handle error
        },
        onFinish: () => {
          this.showDeleteModal = false
        }
      })
    },
    markAsPaid() {
      this.processing = true
      router.put(`/invoices/${this.invoice.id}/mark-as-paid`, {}, {
        onSuccess: () => {
          // Page will refresh with updated data
        },
        onError: (errors) => {
          console.error('Error marking invoice as paid:', errors)
          alert('Error marking invoice as paid. Please try again.')
        },
        onFinish: () => {
          this.processing = false
        }
      })
    },
    recordPayment() {
      this.paymentForm.post(`/invoices/${this.invoice.id}/record-payment`, {
        onSuccess: () => {
          this.showPaymentModal = false
          this.paymentForm.reset()
          // Page will refresh with updated data
        },
        onError: (errors) => {
          console.error('Error recording payment:', errors)
        }
      })
    },
  },
}
</script>