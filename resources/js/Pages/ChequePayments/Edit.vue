<template>
  <div>
    <Head :title="`Edit Payment ${payment.payment_number}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/cheque-payments">Payments</Link>
        <span class="text-brand-400 font-medium">/</span>
        <Link :href="`/cheque-payments/${payment.id}`" class="text-brand-400 hover:text-brand-600">{{ payment.payment_number }}</Link>
        <span class="text-brand-400 font-medium">/</span>
        Edit
      </h1>
      <p class="text-gray-600">Update payment details and information</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Form Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Payment Information</h2>
        <p class="text-sm text-gray-600 mt-1">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 mr-2">DEBIT ONLY</span>
          All payments are debit transactions. When processed, the specified bank account will be debited with the payment amount.
        </p>
      </div>

      <form @submit.prevent="submit">
        <div class="p-6 space-y-6">
          <!-- Payment Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Number</label>
              <input
                v-model="form.payment_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.payment_number ? 'border-red-500' : ''"
                placeholder="Auto-generated if empty"
                disabled
              />
              <div v-if="form.errors.payment_number" class="mt-1 text-sm text-red-600">{{ form.errors.payment_number }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
              <input
                v-model="form.payment_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.payment_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.payment_date" class="mt-1 text-sm text-red-600">{{ form.errors.payment_date }}</div>
            </div>
          </div>

          <!-- Payee Information -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Payee Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payee Name *</label>
                <input
                  v-model="form.payee"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.payee ? 'border-red-500' : ''"
                  required
                />
                <div v-if="form.errors.payee" class="mt-1 text-sm text-red-600">{{ form.errors.payee }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Category *</label>
                <select
                  v-model="form.payment_category"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.payment_category ? 'border-red-500' : ''"
                  required
                >
                  <option value="">Select Payment Category</option>
                  <option v-for="category in paymentCategories" :key="category.value" :value="category.value">
                    {{ category.label }}
                  </option>
                </select>
                <div v-if="form.errors.payment_category" class="mt-1 text-sm text-red-600">{{ form.errors.payment_category }}</div>
              </div>

            </div>
          </div>

          <!-- Conditional Fields Based on Payment Category -->
          <div v-if="form.payment_category === 'vendor_payment'" class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Vendor Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
                <select
                  v-model="form.vendor_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.vendor_id ? 'border-red-500' : ''"
                >
                  <option value="">Select Vendor</option>
                  <option v-for="vendor in vendors" :key="vendor.value" :value="vendor.value">{{ vendor.label }}</option>
                </select>
                <div v-if="form.errors.vendor_id" class="mt-1 text-sm text-red-600">{{ form.errors.vendor_id }}</div>
              </div>
            </div>
          </div>

          <div v-if="form.payment_category === 'bill'" class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bill Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bill *</label>
                <select
                  v-model="form.bill_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.bill_id ? 'border-red-500' : ''"
                  @change="updateFromBill"
                >
                  <option value="">Select Bill</option>
                  <option v-for="bill in bills" :key="bill.value" :value="bill.value">{{ bill.label }}</option>
                </select>
                <div v-if="form.errors.bill_id" class="mt-1 text-sm text-red-600">{{ form.errors.bill_id }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recurring</label>
                <div class="flex items-center">
                  <input
                    v-model="form.is_recurring"
                    type="checkbox"
                    class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 text-sm text-gray-700">This is a recurring payment</label>
                </div>
                <div v-if="form.errors.is_recurring" class="mt-1 text-sm text-red-600">{{ form.errors.is_recurring }}</div>
              </div>
            </div>
            
            <!-- Recurring Frequency (only show when recurring is checked) -->
            <div v-if="form.is_recurring" class="mt-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Recurring Frequency *</label>
                  <select
                    v-model="form.recurring_frequency"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors.recurring_frequency ? 'border-red-500' : ''"
                  >
                    <option value="">Select Frequency</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="annually">Annually</option>
                  </select>
                  <div v-if="form.errors.recurring_frequency" class="mt-1 text-sm text-red-600">{{ form.errors.recurring_frequency }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Financial Details -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Details</h3>
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-red-800">Debit Only Module</h3>
                  <p class="mt-1 text-sm text-red-700">
                    All payments are debit transactions. When processed, the specified bank account will be debited with the payment amount.
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
                    required
                  />
                </div>
                <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account to Debit *</label>
                <select
                  v-model="form.bank_account_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.bank_account_id ? 'border-red-500' : ''"
                  required
                >
                  <option value="">Select Bank Account</option>
                  <option v-for="account in bankAccounts" :key="account.value" :value="account.value">
                    {{ account.label }}
                  </option>
                </select>
                <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
                <p class="mt-1 text-sm text-gray-500">Account to be debited when payment is processed</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Mode *</label>
                <select
                  v-model="form.payment_mode"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.payment_mode ? 'border-red-500' : ''"
                  required
                >
                  <option value="">Select Payment Mode</option>
                  <option v-for="mode in paymentModes" :key="mode.value" :value="mode.value">
                    {{ mode.label }}
                  </option>
                </select>
                <div v-if="form.errors.payment_mode" class="mt-1 text-sm text-red-600">{{ form.errors.payment_mode }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select
                  v-model="form.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.status ? 'border-red-500' : ''"
                  required
                >
                  <option value="">Select Status</option>
                  <option v-for="status in statuses" :key="status.value" :value="status.value">
                    {{ status.label }}
                  </option>
                </select>
                <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
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
                  <option v-for="branch in branches" :key="branch.value" :value="branch.value">
                    {{ branch.label }}
                  </option>
                </select>
                <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cheque Number</label>
                <input
                  v-model="form.cheque_number"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.cheque_number ? 'border-red-500' : ''"
                />
                <div v-if="form.errors.cheque_number" class="mt-1 text-sm text-red-600">{{ form.errors.cheque_number }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                <input
                  v-model="form.reference_number"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.reference_number ? 'border-red-500' : ''"
                />
                <div v-if="form.errors.reference_number" class="mt-1 text-sm text-red-600">{{ form.errors.reference_number }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Image</label>
                <input
                  type="file"
                  @input="form.receipt_image = $event.target.files[0]"
                  accept="image/*"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.receipt_image ? 'border-red-500' : ''"
                />
                <div v-if="form.errors.receipt_image" class="mt-1 text-sm text-red-600">{{ form.errors.receipt_image }}</div>
                <div v-if="payment.receipt_image_path" class="mt-2">
                  <img :src="`/storage/${payment.receipt_image_path}`" alt="Current Receipt" class="max-w-xs rounded-lg shadow-sm" />
                </div>
              </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.description ? 'border-red-500' : ''"
                required
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
                placeholder="Internal notes (not visible to payee)"
              ></textarea>
              <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link :href="`/cheque-payments/${payment.id}`" class="text-gray-600 hover:text-gray-800">
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
            {{ form.processing ? 'Updating...' : 'Update Payment' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'

export default {
  components: {
    Head,
    Link,
  },
  layout: Layout,
  props: {
    payment: Object,
    bankAccounts: Array,
    branches: Array,
    vendors: Array,
    bills: Array,
    paymentCategories: Array,
    paymentModes: Array,
    statuses: Array,
  },
  data() {
    return {
      form: useForm({
        payment_number: this.payment.payment_number,
        payee: this.payment.payee,
        amount: this.payment.amount,
        payment_date: this.payment.payment_date,
        payment_category: this.payment.payment_category,
        payment_mode: this.payment.payment_mode,
        bank_account_id: this.payment.bank_account_id,
        branch_id: this.payment.branch_id,
        vendor_id: this.payment.vendor_id || '',
        bill_id: this.payment.bill_id || '',
        is_recurring: this.payment.is_recurring || false,
        recurring_frequency: this.payment.recurring_frequency || '',
        cheque_number: this.payment.cheque_number,
        reference_number: this.payment.reference_number,
        description: this.payment.description,
        notes: this.payment.notes,
        status: this.payment.status,
        receipt_image: null,
      }),
    }
  },
  methods: {
    submit() {
      this.form.put(`/cheque-payments/${this.payment.id}`, {
        preserveScroll: true,
      })
    },
    updateFromBill() {
      if (this.form.bill_id) {
        const selectedBill = this.bills.find(bill => bill.value == this.form.bill_id)
        if (selectedBill) {
          this.form.amount = selectedBill.amount
          this.form.vendor_id = selectedBill.vendor_id
          this.form.description = selectedBill.description || `Payment for ${selectedBill.label}`
        }
      }
    },
  },
}
</script>