<template>
  <div>
    <Head title="Create Deposit" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/deposits">Deposits</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create
      </h1>
      <p class="text-gray-600">Record a new deposit transaction</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Form Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">
          {{ form.payment_mode === 'sales' ? 'Daily Sales Entry' : 'Deposit Information' }}
        </h2>
        <p v-if="form.payment_mode === 'sales'" class="text-sm text-gray-600 mt-1">
          Record daily shift sales to credit the selected bank account
        </p>
      </div>

      <form @submit.prevent="store">
        <div class="p-6 space-y-6">
          <!-- Transaction Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account *</label>
              <select
                v-model="form.bank_account_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bank_account_id ? 'border-red-500' : ''"
              >
                <option value="">Select Bank Account</option>
                <option v-for="bankAccount in bankAccounts" :key="bankAccount.value" :value="bankAccount.value">{{ bankAccount.label }}</option>
              </select>
              <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Mode *</label>
              <select
                v-model="form.payment_mode"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.payment_mode ? 'border-red-500' : ''"
              >
                <option value="">Select Payment Mode</option>
                <option v-for="paymentMode in paymentModes" :key="paymentMode.value" :value="paymentMode.value">{{ paymentMode.label }}</option>
              </select>
              <div v-if="form.errors.payment_mode" class="mt-1 text-sm text-red-600">{{ form.errors.payment_mode }}</div>
            </div>

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

          <!-- Branch and Shift Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ form.payment_mode === 'sales' ? 'Branch *' : 'Branch' }}
              </label>
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

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ form.payment_mode === 'sales' ? 'Shift *' : 'Shift' }}
              </label>
              <select
                v-model="form.shift_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.shift_id ? 'border-red-500' : ''"
              >
                <option value="">Select Shift</option>
                <option v-for="shift in shifts" :key="shift.value" :value="shift.value">{{ shift.label }}</option>
              </select>
              <div v-if="form.errors.shift_id" class="mt-1 text-sm text-red-600">{{ form.errors.shift_id }}</div>
            </div>
          </div>

          <!-- Reference and Documentation -->
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Receipt/Image</label>
              <input
                @input="form.image = $event.target.files[0]"
                type="file"
                accept="image/*"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.image ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</div>
              <p class="mt-1 text-sm text-gray-500">Optional: Upload receipt or proof of deposit</p>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.description ? 'border-red-500' : ''"
              :placeholder="form.payment_mode === 'sales' ? 'Optional: Add notes about this sales entry (e.g., total transactions, special items sold)' : 'Optional: Add notes about this deposit'"
            ></textarea>
            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/deposits" class="text-gray-600 hover:text-gray-800">
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
            {{ form.processing ? 'Processing...' : (form.payment_mode === 'sales' ? 'Record Sales' : 'Create Deposit') }}
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
    shifts: Array,
    paymentModes: Array,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        bank_account_id: '',
        transaction_date: new Date().toISOString().split('T')[0],
        payment_mode: '',
        amount: '',
        branch_id: '',
        shift_id: '',
        reference_number: '',
        description: '',
        image: null,
      }),
    }
  },
  watch: {
    'form.payment_mode'(newMode) {
      if (newMode === 'sales' && !this.form.reference_number) {
        // Auto-generate sales reference
        const today = new Date().toISOString().split('T')[0].replace(/-/g, '')
        this.form.reference_number = `SALES-${today}-`
      }
    }
  },
  methods: {
    store() {
      this.form.post('/deposits')
    },
  },
}
</script>