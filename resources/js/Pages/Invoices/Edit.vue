<template>
  <div>
    <Head :title="`Edit Invoice ${invoice.invoice_number}`" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center space-x-4 mb-2">
        <Link href="/invoices" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          </svg>
        </Link>
        <h1 class="text-3xl font-bold">Edit Invoice</h1>
      </div>
      <p class="text-gray-600">Update invoice details and information</p>
    </div>

    <!-- Invoice Summary Card -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold mb-2">Invoice Summary</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
              <span class="text-gray-500">Number:</span>
              <div class="font-medium">{{ invoice.invoice_number }}</div>
            </div>
            <div>
              <span class="text-gray-500">Date:</span>
              <div class="font-medium">{{ $formatDate(invoice.invoice_date) }}</div>
            </div>
            <div>
              <span class="text-gray-500">Customer:</span>
              <div class="font-medium">{{ invoice.customer_name }}</div>
            </div>
            <div>
              <span class="text-gray-500">Amount:</span>
              <div class="font-medium text-green-600">{{ $formatAmount(invoice.amount) }}</div>
            </div>
          </div>
        </div>
        <div class="text-right">
          <div class="text-sm text-gray-500">Last Updated</div>
          <div class="text-sm font-medium">{{ $formatDate(invoice.updated_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Deleted Invoice Notice -->
    <div v-if="invoice.deleted_at" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <div>
          <h3 class="text-sm font-medium text-red-800">This invoice has been deleted</h3>
          <p class="text-sm text-red-700 mt-1">
            You can restore this invoice or permanently delete it.
          </p>
        </div>
        <button @click="restore" class="ml-auto px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
          Restore
        </button>
      </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="update">
        <!-- Form Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold">Invoice Information</h2>
        </div>
        <!-- Form Body -->
        <div class="p-6 space-y-6">
          <!-- Invoice Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Invoice Number -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
              <input
                v-model="form.invoice_number"
                type="text"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.invoice_number ? 'border-red-300' : 'border-gray-300'"
                placeholder="Auto-generated if empty"
              />
              <div v-if="form.errors.invoice_number" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_number }}</div>
            </div>
            <!-- Invoice Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date *</label>
              <input
                v-model="form.invoice_date"
                type="date"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.invoice_date ? 'border-red-300' : 'border-gray-300'"
              />
              <div v-if="form.errors.invoice_date" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_date }}</div>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Invoice Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Type *</label>
              <select
                v-model="form.invoice_type"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.invoice_type ? 'border-red-300' : 'border-gray-300'"
              >
                <option value="">Select Invoice Type</option>
                <option v-for="type in invoiceTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
              <div v-if="form.errors.invoice_type" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_type }}</div>
            </div>
            <!-- Due Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <input
                v-model="form.due_date"
                type="date"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.due_date ? 'border-red-300' : 'border-gray-300'"
                :min="form.invoice_date"
              />
              <div v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</div>
            </div>
          </div>
          <!-- Customer Information -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Customer Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
                <input
                  v-model="form.customer_name"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_name ? 'border-red-300' : 'border-gray-300'"
                  placeholder="Enter customer name"
                />
                <div v-if="form.errors.customer_name" class="mt-1 text-sm text-red-600">{{ form.errors.customer_name }}</div>
              </div>
              <!-- Customer Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Type *</label>
                <select
                  v-model="form.customer_type"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_type ? 'border-red-300' : 'border-gray-300'"
                >
                  <option value="">Select Customer Type</option>
                  <option v-for="type in customerTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
                <div v-if="form.errors.customer_type" class="mt-1 text-sm text-red-600">{{ form.errors.customer_type }}</div>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                  v-model="form.customer_email"
                  type="email"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_email ? 'border-red-300' : 'border-gray-300'"
                  placeholder="customer@example.com"
                />
                <div v-if="form.errors.customer_email" class="mt-1 text-sm text-red-600">{{ form.errors.customer_email }}</div>
              </div>
              <!-- Phone -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input
                  v-model="form.customer_phone"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_phone ? 'border-red-300' : 'border-gray-300'"
                  placeholder="+220 123 4567"
                />
                <div v-if="form.errors.customer_phone" class="mt-1 text-sm text-red-600">{{ form.errors.customer_phone }}</div>
              </div>
            </div>
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
              <textarea
                v-model="form.customer_address"
                rows="3"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.customer_address ? 'border-red-300' : 'border-gray-300'"
                placeholder="Customer address"
              ></textarea>
              <div v-if="form.errors.customer_address" class="mt-1 text-sm text-red-600">{{ form.errors.customer_address }}</div>
            </div>
          </div>
          <!-- Financial Details -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Details</h3>
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-green-800">Credit Only Module</h3>
                  <p class="mt-1 text-sm text-green-700">
                    All invoices are credit transactions. When paid, the specified bank account will be credited with the invoice amount.
                  </p>
                </div>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Amount -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                <div class="relative">
                  <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    class="w-full pl-12 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors.amount ? 'border-red-300' : 'border-gray-300'"
                    placeholder="0.00"
                  />
                </div>
                <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
              </div>
              <!-- Bank Account -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account to Credit *</label>
                <select
                  v-model="form.bank_account_id"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.bank_account_id ? 'border-red-300' : 'border-gray-300'"
                >
                  <option value="">Select Bank Account</option>
                  <option v-for="account in bankAccounts" :key="account.value" :value="account.value">{{ account.label }}</option>
                </select>
                <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
                <p class="mt-1 text-sm text-gray-500">Account to be credited when payment is received</p>
              </div>
            </div>
          </div>
          <!-- Additional Information -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Branch -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select
                  v-model="form.branch_id"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.branch_id ? 'border-red-300' : 'border-gray-300'"
                >
                  <option value="">Select Branch</option>
                  <option v-for="branch in branches" :key="branch.value" :value="branch.value">{{ branch.label }}</option>
                </select>
                <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</div>
              </div>
              <!-- Billing Period (if daily_supply) -->
              <div v-if="form.invoice_type === 'daily_supply'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Billing Period</label>
                <input
                  v-model="form.billing_period"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.billing_period ? 'border-red-300' : 'border-gray-300'"
                  placeholder="e.g. Jan 2024"
                />
                <div v-if="form.errors.billing_period" class="mt-1 text-sm text-red-600">{{ form.errors.billing_period }}</div>
              </div>
            </div>
          </div>
        </div>
        <!-- Form Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link href="/invoices" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
              Back to Invoices
            </Link>
            <button
              v-if="!invoice.deleted_at"
              type="button"
              @click="destroy"
              class="px-4 py-2 text-sm text-red-600 hover:text-red-800"
            >
              Delete Invoice
            </button>
          </div>
          <button
            v-if="!invoice.deleted_at"
            type="submit"
            :disabled="form.processing"
            :class="[
              'px-6 py-2 bg-brand-600 text-white font-medium rounded-md hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
              { 'opacity-50 cursor-not-allowed': form.processing }
            ]"
          >
            <span v-if="form.processing" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Updating...
            </span>
            <span v-else>Update Invoice</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'

export default {
  components: {
    Head,
    Link,
  },
  layout: Layout,
  props: {
    invoice: Object,
    bankAccounts: Array,
    branches: Array,
    invoiceTypes: Array,
    customerTypes: Array,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        invoice_number: this.invoice.invoice_number || '',
        customer_name: this.invoice.customer_name || '',
        customer_email: this.invoice.customer_email || '',
        customer_phone: this.invoice.customer_phone || '',
        customer_address: this.invoice.customer_address || '',
        invoice_date: this.invoice.invoice_date || '',
        due_date: this.invoice.due_date || '',
        amount: this.invoice.amount || '',
        invoice_type: this.invoice.invoice_type || '',
        customer_type: this.invoice.customer_type || '',
        description: this.invoice.description || '',
        notes: this.invoice.notes || '',
        bank_account_id: this.invoice.bank_account_id || '',
        branch_id: this.invoice.branch_id || '',
        billing_period: this.invoice.billing_period || '',
        attachment: null,
      }),
    }
  },
  methods: {
    update() {
      this.form.put(`/invoices/${this.invoice.id}`)
    },
    getDescriptionPlaceholder() {
      const placeholders = {
        'bulk_sales': 'Describe the bulk order for shops (e.g., quantity, items, delivery details)',
        'credit_customer': 'Details of goods/services provided on credit to customer',
        'loans': 'Loan repayment details and terms (credit transaction)',
        'daily_supply': 'Summary of daily supplies provided to hotel/partner during billing period',
        'partner_billing': 'Services provided to partner/branch - bills owed to us',
        'other': 'Unusual payments made to the business (credit transaction)'
      }
      return placeholders[this.form.invoice_type] || 'Invoice description (credit transaction to specified bank account)'
    },
  },
}
</script>