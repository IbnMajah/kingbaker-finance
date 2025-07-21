<template>
  <div>
    <Head title="Create Invoice" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center space-x-4 mb-2">
        <Link href="/invoices" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          </svg>
        </Link>
        <h1 class="text-3xl font-bold">Create Invoice</h1>
      </div>
      <p class="text-gray-600">Generate a new customer invoice for billing and tracking</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="store">
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
              <p class="mt-1 text-sm text-gray-500">Leave empty to auto-generate</p>
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
            <!-- Invoice Items -->
            <div class="col-span-full">
              <label class="block text-sm font-medium text-gray-700 mb-3">Invoice Items *</label>
              <div class="border border-gray-300 rounded-md overflow-hidden">
                <!-- Items Header -->
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-300">
                  <div class="grid grid-cols-12 gap-3 text-sm font-medium text-gray-700">
                    <div class="col-span-5">Description</div>
                    <div class="col-span-2">Unit Price (GMD)</div>
                    <div class="col-span-2">Quantity</div>
                    <div class="col-span-2">Total</div>
                    <div class="col-span-1">Action</div>
                  </div>
                </div>
                <!-- Items List -->
                <div class="divide-y divide-gray-200">
                  <div v-for="(item, index) in form.items" :key="index" class="px-4 py-3">
                    <div class="grid grid-cols-12 gap-3 items-start">
                      <!-- Description -->
                      <div class="col-span-5">
                        <input
                          v-model="item.description"
                          type="text"
                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                          :class="form.errors[`items.${index}.description`] ? 'border-red-300' : 'border-gray-300'"
                          placeholder="Item description"
                        />
                        <div v-if="form.errors[`items.${index}.description`]" class="mt-1 text-sm text-red-600">
                          {{ form.errors[`items.${index}.description`] }}
                        </div>
                      </div>
                      <!-- Unit Price -->
                      <div class="col-span-2">
                        <input
                          v-model.number="item.unit_price"
                          type="number"
                          step="0.01"
                          min="0.01"
                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                          :class="form.errors[`items.${index}.unit_price`] ? 'border-red-300' : 'border-gray-300'"
                          placeholder="0.00"
                          @input="calculateItemTotal(index)"
                        />
                        <div v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-sm text-red-600">
                          {{ form.errors[`items.${index}.unit_price`] }}
                        </div>
                      </div>
                      <!-- Quantity -->
                      <div class="col-span-2">
                        <input
                          v-model.number="item.quantity"
                          type="number"
                          min="1"
                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                          :class="form.errors[`items.${index}.quantity`] ? 'border-red-300' : 'border-gray-300'"
                          placeholder="1"
                          @input="calculateItemTotal(index)"
                        />
                        <div v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-sm text-red-600">
                          {{ form.errors[`items.${index}.quantity`] }}
                        </div>
                      </div>
                      <!-- Total -->
                      <div class="col-span-2">
                        <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                          GMD {{ (item.unit_price * item.quantity || 0).toFixed(2) }}
                        </div>
                      </div>
                      <!-- Action -->
                      <div class="col-span-1">
                        <button
                          type="button"
                          @click="removeItem(index)"
                          class="w-full text-red-600 hover:text-red-800 px-2 py-2 text-sm"
                          :disabled="form.items.length <= 1"
                        >
                          <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 012 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v3a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Add Item Button -->
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-300">
                  <button
                    type="button"
                    @click="addItem"
                    class="text-brand-600 hover:text-brand-700 text-sm font-medium flex items-center"
                  >
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Add Item
                  </button>
                </div>
                <!-- Total Section -->
                <div class="px-4 py-3 bg-blue-50 border-t border-gray-300">
                  <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
                    <span class="text-xl font-bold text-brand-600">GMD {{ totalAmount.toFixed(2) }}</span>
                  </div>
                </div>
              </div>
              <div v-if="form.errors.items" class="mt-1 text-sm text-red-600">{{ form.errors.items }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Bank Account (moved here from above) -->
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
          <Link href="/invoices" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
            Cancel
          </Link>
          <button
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
              Creating...
            </span>
            <span v-else>Create Invoice</span>
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
        items: [
          {
            description: '',
            unit_price: '',
            quantity: 1,
          }
        ],
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
  computed: {
    totalAmount() {
      return this.form.items.reduce((total, item) => {
        const itemTotal = (parseFloat(item.unit_price) || 0) * (parseInt(item.quantity) || 0)
        return total + itemTotal
      }, 0)
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
    addItem() {
      this.form.items.push({
        description: '',
        unit_price: '',
        quantity: 1,
      })
    },
    removeItem(index) {
      if (this.form.items.length > 1) {
        this.form.items.splice(index, 1)
      }
    },
    calculateItemTotal(index) {
      // This method triggers reactivity for computed total
      // The actual calculation is done in the template and computed property
      this.$forceUpdate()
    },
  },
}
</script>