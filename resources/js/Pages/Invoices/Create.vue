<template>
  <div>
    <Head title="Create Invoice" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/invoices">Invoices</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create
      </h1>
      <p class="text-gray-600">Generate a new customer invoice for billing and tracking</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Form Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Invoice Information</h2>
        <p v-if="form.invoice_type === 'daily_supply'" class="text-sm text-gray-600 mt-1">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">CREDIT ONLY</span>
          Daily supply billing for monthly partner/hotel invoicing
        </p>
        <p v-else-if="form.invoice_type === 'bulk_sales'" class="text-sm text-gray-600 mt-1">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">CREDIT ONLY</span>
          Large order invoice for bulk sales transactions to shops
        </p>
        <p v-else-if="form.invoice_type === 'credit_customer'" class="text-sm text-gray-600 mt-1">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">CREDIT ONLY</span>
          Credit billing for customer payment tracking
        </p>
        <p v-else-if="form.invoice_type === 'partner_billing'" class="text-sm text-gray-600 mt-1">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">CREDIT ONLY</span>
          Bills owed from partners/branches for services provided
        </p>
        <p v-else-if="form.invoice_type === 'loans'" class="text-sm text-gray-600 mt-1">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">CREDIT ONLY</span>
          Invoice for loan repayments or loan-related transactions
        </p>
        <p v-else-if="form.invoice_type === 'other'" class="text-sm text-gray-600 mt-1">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">CREDIT ONLY</span>
          Other unusual payments made to the business
        </p>
        <p v-else class="text-sm text-gray-600 mt-1">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">CREDIT ONLY</span>
          Generate customer invoices that will credit specified bank accounts when paid
        </p>
      </div>

      <form @submit.prevent="store">
        <div class="p-6 space-y-6">
          <!-- Invoice Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
              <input
                v-model="form.invoice_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.invoice_number ? 'border-red-500' : ''"
                placeholder="Auto-generated if empty"
              />
              <div v-if="form.errors.invoice_number" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_number }}</div>
              <p class="mt-1 text-sm text-gray-500">Leave empty to auto-generate</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date *</label>
              <input
                v-model="form.invoice_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.invoice_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.invoice_date" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_date }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Type *</label>
              <select
                v-model="form.invoice_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.invoice_type ? 'border-red-500' : ''"
              >
                <option value="">Select Invoice Type</option>
                <option v-for="type in invoiceTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
              <div v-if="form.errors.invoice_type" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_type }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <input
                v-model="form.due_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.due_date ? 'border-red-500' : ''"
                :min="form.invoice_date"
              />
              <div v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</div>
            </div>
          </div>

          <!-- Customer Information -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
                <input
                  v-model="form.customer_name"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_name ? 'border-red-500' : ''"
                  placeholder="Enter customer name"
                />
                <div v-if="form.errors.customer_name" class="mt-1 text-sm text-red-600">{{ form.errors.customer_name }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Type *</label>
                <select
                  v-model="form.customer_type"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_type ? 'border-red-500' : ''"
                >
                  <option value="">Select Customer Type</option>
                  <option v-for="type in customerTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
                <div v-if="form.errors.customer_type" class="mt-1 text-sm text-red-600">{{ form.errors.customer_type }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                  v-model="form.customer_email"
                  type="email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_email ? 'border-red-500' : ''"
                  placeholder="customer@example.com"
                />
                <div v-if="form.errors.customer_email" class="mt-1 text-sm text-red-600">{{ form.errors.customer_email }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input
                  v-model="form.customer_phone"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_phone ? 'border-red-500' : ''"
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
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.customer_address ? 'border-red-500' : ''"
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
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                <div class="relative">
                  <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors.amount ? 'border-red-500' : ''"
                    placeholder="0.00"
                  />
                </div>
                <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account to Credit *</label>
                <select
                  v-model="form.bank_account_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.bank_account_id ? 'border-red-500' : ''"
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
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select
                  v-model="form.branch_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.branch_id ? 'border-red-500' : ''"
                >
                  <option value="">Select Branch</option>
                  <option v-for="branch in branches" :key="branch.value" :value="branch.value">{{ branch.label }}</option>
                </select>
                <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</div>
              </div>

              <div v-if="form.invoice_type === 'daily_supply'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Billing Period</label>
                <input
                  v-model="form.billing_period"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.billing_period ? 'border-red-500' : ''"
                  placeholder="e.g., January 2024"
                />
                <div v-if="form.errors.billing_period" class="mt-1 text-sm text-red-600">{{ form.errors.billing_period }}</div>
                <p class="mt-1 text-sm text-gray-500">For monthly billing of daily supplies</p>
              </div>

              <div v-if="form.invoice_type !== 'daily_supply'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Attachment</label>
                <input
                  @input="form.attachment = $event.target.files[0]"
                  type="file"
                  accept="image/*,application/pdf"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.attachment ? 'border-red-500' : ''"
                />
                <div v-if="form.errors.attachment" class="mt-1 text-sm text-red-600">{{ form.errors.attachment }}</div>
                <p class="mt-1 text-sm text-gray-500">Optional: Upload supporting documents</p>
              </div>
            </div>

            <!-- Recurring Invoice Options -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center mb-3">
                <input
                  v-model="form.is_recurring"
                  type="checkbox"
                  id="is_recurring"
                  class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded"
                />
                <label for="is_recurring" class="ml-2 block text-sm font-medium text-gray-700">
                  Make this a recurring invoice
                </label>
              </div>

              <div v-if="form.is_recurring" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Frequency *</label>
                  <select
                    v-model="form.recurring_frequency"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors.recurring_frequency ? 'border-red-500' : ''"
                  >
                    <option value="">Select Frequency</option>
                    <option v-for="freq in recurringFrequencies" :key="freq.value" :value="freq.value">{{ freq.label }}</option>
                  </select>
                  <div v-if="form.errors.recurring_frequency" class="mt-1 text-sm text-red-600">{{ form.errors.recurring_frequency }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Next Invoice Date</label>
                  <input
                    v-model="form.next_invoice_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors.next_invoice_date ? 'border-red-500' : ''"
                    :min="form.invoice_date"
                  />
                  <div v-if="form.errors.next_invoice_date" class="mt-1 text-sm text-red-600">{{ form.errors.next_invoice_date }}</div>
                </div>
              </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.description ? 'border-red-500' : ''"
                :placeholder="getDescriptionPlaceholder()"
              ></textarea>
              <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
            </div>

            <!-- Notes -->
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
              <textarea
                v-model="form.notes"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.notes ? 'border-red-500' : ''"
                placeholder="Internal notes (not visible to customer)"
              ></textarea>
              <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/invoices" class="text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-kingbaker flex items-center"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Creating...' : 'Create Invoice' }}
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
    bankAccounts: Array,
    branches: Array,
    invoiceTypes: Array,
    customerTypes: Array,
    recurringFrequencies: Array,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        invoice_number: '',
        customer_name: '',
        customer_email: '',
        customer_phone: '',
        customer_address: '',
        invoice_date: new Date().toISOString().split('T')[0],
        due_date: '',
        amount: '',
        invoice_type: '',
        customer_type: '',
        description: '',
        notes: '',
        bank_account_id: '',
        branch_id: '',
        billing_period: '',
        is_recurring: false,
        recurring_frequency: '',
        next_invoice_date: '',
        attachment: null,
      }),
    }
  },
  watch: {
    'form.invoice_type'(newType) {
      // Auto-set due date based on invoice type
      if (newType === 'daily_supply') {
        // Monthly billing - set due date to end of next month
        const nextMonth = new Date()
        nextMonth.setMonth(nextMonth.getMonth() + 1)
        nextMonth.setDate(new Date(nextMonth.getFullYear(), nextMonth.getMonth() + 1, 0).getDate())
        this.form.due_date = nextMonth.toISOString().split('T')[0]

        // Set billing period to current month
        const now = new Date()
        this.form.billing_period = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
      } else if (newType === 'bulk_sales') {
        // 30 days from invoice date
        const dueDate = new Date(this.form.invoice_date)
        dueDate.setDate(dueDate.getDate() + 30)
        this.form.due_date = dueDate.toISOString().split('T')[0]
      }
    },
    'form.is_recurring'(isRecurring) {
      if (isRecurring && !this.form.next_invoice_date) {
        // Set next invoice date to 1 month from invoice date by default
        const nextDate = new Date(this.form.invoice_date)
        nextDate.setMonth(nextDate.getMonth() + 1)
        this.form.next_invoice_date = nextDate.toISOString().split('T')[0]
      }
    }
  },
  methods: {
    store() {
      this.form.post('/invoices')
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