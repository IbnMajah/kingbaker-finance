<template>
  <div>
    <Head title="New Miscellaneous Transaction" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/miscellaneous">Miscellaneous</Link>
        <span class="text-brand-400 font-medium">/</span>
        New Transaction
      </h1>
      <p class="text-gray-600">Create a new miscellaneous transaction for non-operational income or expenses</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Form Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Transaction Information</h2>
        <p class="text-sm text-gray-600 mt-1">Enter the details for this miscellaneous transaction</p>
      </div>

      <form @submit.prevent="store">
        <div class="p-6 space-y-6">
          <!-- Transaction Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
              <input
                v-model="form.reference_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.reference_number ? 'border-red-500' : ''"
                placeholder="Auto-generated if empty"
              />
              <div v-if="form.errors.reference_number" class="mt-1 text-sm text-red-600">{{ form.errors.reference_number }}</div>
              <p class="mt-1 text-sm text-gray-500">Leave empty to auto-generate</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Date *</label>
              <input
                v-model="form.transaction_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.transaction_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.transaction_date" class="mt-1 text-sm text-red-600">{{ form.errors.transaction_date }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type *</label>
              <select
                v-model="form.type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.type ? 'border-red-500' : ''"
              >
                <option value="">Select Transaction Type</option>
                <option value="debit">Debit (Expense)</option>
                <option value="credit">Credit (Income)</option>
              </select>
              <div v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Mode *</label>
              <select
                v-model="form.payment_mode"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.payment_mode ? 'border-red-500' : ''"
              >
                <option value="">Select Payment Mode</option>
                <option v-for="mode in paymentModes" :key="mode" :value="mode">
                  {{ mode.charAt(0).toUpperCase() + mode.slice(1).replace('_', ' ') }}
                </option>
              </select>
              <div v-if="form.errors.payment_mode" class="mt-1 text-sm text-red-600">{{ form.errors.payment_mode }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
              <select
                v-model="form.category"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.category ? 'border-red-500' : ''"
              >
                <option value="">Select Category</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category.charAt(0).toUpperCase() + category.slice(1).replace('_', ' ') }}
                </option>
              </select>
              <div v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account *</label>
              <select
                v-model="form.bank_account_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bank_account_id ? 'border-red-500' : ''"
              >
                <option value="">Select Bank Account</option>
                <option v-for="account in bankAccounts" :key="account.id" :value="account.id">{{ account.name }}</option>
              </select>
              <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
            </div>
          </div>

          <!-- Financial Details -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Details</h3>

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
                  <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
                <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Image</label>
                <input
                  @input="form.receipt_image = $event.target.files[0]"
                  type="file"
                  accept="image/*"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.receipt_image ? 'border-red-500' : ''"
                />
                <div v-if="form.errors.receipt_image" class="mt-1 text-sm text-red-600">{{ form.errors.receipt_image }}</div>
                <p class="mt-1 text-sm text-gray-500">Upload a receipt image (optional)</p>
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
                placeholder="Describe the purpose of this transaction"
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
                placeholder="Internal notes (not visible to others)"
              ></textarea>
              <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/miscellaneous" class="text-gray-600 hover:text-gray-800">
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
            {{ form.processing ? 'Creating...' : 'Create Transaction' }}
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
    categories: Array,
    paymentModes: Array,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        reference_number: '',
        transaction_date: new Date().toISOString().split('T')[0],
        type: '',
        category: '',
        payment_mode: '',
        amount: '',
        description: '',
        notes: '',
        bank_account_id: '',
        branch_id: '',
        receipt_image: null,
      }),
    }
  },
  methods: {
    store() {
      this.form.post('/miscellaneous')
    },
  },
}
</script>