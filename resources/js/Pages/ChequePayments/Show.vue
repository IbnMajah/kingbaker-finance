<template>
  <div>
    <Head :title="`Payment ${payment.payment_number}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/cheque-payments">Payments</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ payment.payment_number }}
      </h1>
      <p class="text-gray-600">Payment Details & Information</p>
    </div>

    <!-- Payment Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Amount</div>
        <div class="text-2xl font-bold text-gray-900">
          GMD {{ payment.amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Payment Mode</div>
        <div class="text-2xl font-bold text-gray-900">
          {{ getPaymentModeLabel(payment.payment_mode) }}
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Status</div>
        <div class="text-2xl font-bold" :class="getStatusTextClass(payment.status)">
          {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
        </div>
      </div>
    </div>

    <!-- Payment Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Payment Details</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <div class="text-sm font-medium text-gray-500">Payment Number</div>
          <div class="text-gray-900 font-mono">{{ payment.payment_number }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Payment Date</div>
          <div class="text-gray-900">{{ new Date(payment.payment_date).toLocaleDateString() }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Payment Category</div>
          <div class="text-gray-900">{{ getCategoryLabel(payment.payment_category) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Bank Account</div>
          <div class="text-gray-900">{{ payment.bank_account?.name }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Branch</div>
          <div class="text-gray-900">{{ payment.branch?.name || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Created By</div>
          <div class="text-gray-900">{{ payment.creator ? `${payment.creator.first_name} ${payment.creator.last_name}` : 'Unknown' }}</div>
        </div>
      </div>
    </div>

    <!-- Payee Information -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Payee Information</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="text-sm font-medium text-gray-500">Payee Name</div>
          <div class="text-gray-900 text-lg font-medium">{{ payment.payee }}</div>
        </div>
        <div v-if="payment.cheque_number">
          <div class="text-sm font-medium text-gray-500">Cheque Number</div>
          <div class="text-gray-900">{{ payment.cheque_number }}</div>
        </div>
        <div v-if="payment.reference_number">
          <div class="text-sm font-medium text-gray-500">Reference Number</div>
          <div class="text-gray-900">{{ payment.reference_number }}</div>
        </div>
      </div>
    </div>

    <!-- Description -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Description</h2>
      <div class="bg-gray-50 p-4 rounded-md">
        <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">
          {{ payment.description || 'No description provided.' }}
        </p>
      </div>
    </div>

    <!-- Notes -->
    <div v-if="payment.notes" class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Notes</h2>
      <div class="bg-yellow-50 p-4 rounded-md border-l-4 border-yellow-400">
        <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ payment.notes }}</p>
      </div>
    </div>

    <!-- Receipt Image -->
    <div v-if="payment.receipt_image_path" class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Receipt Image</h2>
      <div class="bg-gray-50 p-4 rounded-md">
        <img :src="`/storage/${payment.receipt_image_path}`" alt="Receipt" class="max-w-lg rounded-lg shadow-sm" />
      </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold mb-4">Actions</h2>
      <div class="flex flex-wrap gap-3">
        <Link
          :href="`/cheque-payments/${payment.id}/edit`"
          class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-md">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Edit Payment
        </Link>

        <button
          v-if="payment.status === 'pending'"
          @click="markAsIssued"
          :disabled="processing"
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md disabled:opacity-50">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
          {{ processing ? 'Processing...' : 'Mark as Issued' }}
        </button>

        <button
          v-if="payment.status === 'issued'"
          @click="markAsCleared"
          :disabled="processing"
          class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md disabled:opacity-50">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          {{ processing ? 'Processing...' : 'Mark as Cleared' }}
        </button>

        <button
          v-if="['pending', 'issued'].includes(payment.status)"
          @click="confirmCancel"
          class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          Cancel Payment
        </button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full mx-4">
        <div class="flex items-center">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.966-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
        </div>
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Cancel Payment</h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500">
              Are you sure you want to cancel payment #{{ payment.payment_number }}? This action cannot be undone.
            </p>
          </div>
        </div>
        <div class="mt-5 flex justify-center space-x-3">
          <button
            @click="showCancelModal = false"
            class="bg-white hover:bg-gray-50 text-gray-900 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium">
            No, Keep Payment
          </button>
          <button
            @click="cancelPayment"
            :disabled="processing"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50">
            {{ processing ? 'Cancelling...' : 'Yes, Cancel Payment' }}
          </button>
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
    payment: Object,
  },
  data() {
    return {
      showCancelModal: false,
      processing: false,
    }
  },
  methods: {
    getStatusTextClass(status) {
      const classes = {
        'pending': 'text-yellow-600',
        'issued': 'text-blue-600',
        'cleared': 'text-green-600',
        'cancelled': 'text-red-600',
      }
      return classes[status] || 'text-gray-600'
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
    markAsIssued() {
      this.processing = true
      router.patch(`/cheque-payments/${this.payment.id}/mark-issued`, {}, {
        onSuccess: () => {
          // Page will refresh with updated data
        },
        onError: () => {
          // Handle error
        },
        onFinish: () => {
          this.processing = false
        }
      })
    },
    markAsCleared() {
      this.processing = true
      router.patch(`/cheque-payments/${this.payment.id}/mark-cleared`, {}, {
        onSuccess: () => {
          // Page will refresh with updated data
        },
        onError: () => {
          // Handle error
        },
        onFinish: () => {
          this.processing = false
        }
      })
    },
    confirmCancel() {
      this.showCancelModal = true
    },
    cancelPayment() {
      this.processing = true
      router.patch(`/cheque-payments/${this.payment.id}/cancel`, {}, {
        onSuccess: () => {
          // Redirect will be handled by the backend
        },
        onError: () => {
          this.processing = false
          // Handle error
        },
        onFinish: () => {
          this.showCancelModal = false
        }
      })
    },
  },
}
</script>