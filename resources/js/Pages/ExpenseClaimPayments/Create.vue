<template>
  <div>
    <Head :title="`Create Payment - ${expenseClaim.claim_number || `Expense Claim #${expenseClaim.id}`}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/expense-claims">Expense Claims</Link>
        <span class="text-brand-400 font-medium">/</span>
        <Link :href="`/expense-claims/${expenseClaim.id}`" class="text-brand-400 hover:text-brand-600">{{ expenseClaim.claim_number || `Claim #${expenseClaim.id}` }}</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create Payment
      </h1>
      <p class="text-gray-600">Create payment for this expense claim</p>
    </div>

    <!-- Expense Claim Summary -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Expense Claim Summary</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <div class="text-sm font-medium text-gray-500">Claim Number</div>
          <div class="text-gray-900 font-mono">{{ expenseClaim.claim_number }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Total Amount</div>
          <div class="text-gray-900 font-semibold">{{ $formatAmount(expenseClaim.total) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Payee</div>
          <div class="text-gray-900">{{ expenseClaim.payee }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Title</div>
          <div class="text-gray-900">{{ expenseClaim.title }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Claim Date</div>
          <div class="text-gray-900">{{ $formatDate(expenseClaim.claim_date) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Status</div>
          <div class="text-gray-900">{{ expenseClaim.status }}</div>
        </div>
      </div>
    </div>

    <!-- Payment Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Payment Information</h2>
      </div>

      <form @submit.prevent="createPayment">
        <div class="p-6 space-y-6">
          <!-- Payment Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount *</label>
            <div class="relative">
              <span class="absolute left-3 top-2 text-gray-500">GMD</span>
              <input
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0.01"
                :max="expenseClaim.total"
                class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.amount ? 'border-red-300' : 'border-gray-300'"
                :value="expenseClaim.total"
                required
                readonly
              />
            </div>
            <p class="mt-1 text-sm text-gray-500">Amount matches expense claim total</p>
            <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
          </div>

          <!-- Payment Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
            <input
              v-model="form.payment_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.payment_date ? 'border-red-300' : 'border-gray-300'"
              required
            />
            <div v-if="form.errors.payment_date" class="mt-1 text-sm text-red-600">{{ form.errors.payment_date }}</div>
          </div>

          <!-- Payment Method -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
            <select
              v-model="form.payment_method"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.payment_method ? 'border-red-300' : 'border-gray-300'"
              required
            >
              <option value="">Select payment method</option>
              <option v-for="method in paymentMethods" :key="method" :value="method">
                {{ method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
              </option>
            </select>
            <div v-if="form.errors.payment_method" class="mt-1 text-sm text-red-600">{{ form.errors.payment_method }}</div>
          </div>

          <!-- Bank Account (required for non-cash payments) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Bank Account <span v-if="form.payment_method && form.payment_method !== 'cash'">*</span>
            </label>
            <select
              v-model="form.bank_account_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.bank_account_id ? 'border-red-300' : 'border-gray-300'"
              :required="form.payment_method && form.payment_method !== 'cash'"
            >
              <option value="">Select bank account</option>
              <option v-for="account in bankAccounts" :key="account.value" :value="account.value">
                {{ account.label }}
              </option>
            </select>
            <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
          </div>

          <!-- Reference Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
            <input
              v-model="form.reference_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.reference_number ? 'border-red-300' : 'border-gray-300'"
              placeholder="e.g., CHQ-001, TFR-123456"
            />
            <p class="mt-1 text-sm text-gray-500">Optional: Transaction reference, cheque number, etc.</p>
            <div v-if="form.errors.reference_number" class="mt-1 text-sm text-red-600">{{ form.errors.reference_number }}</div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.notes ? 'border-red-300' : 'border-gray-300'"
              placeholder="Optional payment notes..."
            ></textarea>
            <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link :href="`/expense-claims/${expenseClaim.id}`" class="text-gray-600 hover:text-gray-800">
              <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
              </svg>
              Cancel
            </Link>
          </div>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-kingbaker flex items-center"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Creating...' : 'Create Payment' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import { formatterMixin } from '@/Utils/formatters'

export default {
  components: {
    Head,
    Link,
  },
  mixins: [formatterMixin],
  layout: Layout,
  props: {
    expenseClaim: {
      type: Object,
      required: true
    },
    bankAccounts: {
      type: Array,
      default: () => []
    },
    paymentMethods: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      form: useForm({
        expense_claim_id: this.expenseClaim.id,
        amount: this.expenseClaim.total,
        payment_date: new Date().toISOString().split('T')[0],
        payment_method: '',
        bank_account_id: '',
        reference_number: '',
        notes: '',
      })
    }
  },
  methods: {
    createPayment() {
      this.form.post('/expense-claim-payments', {
        onSuccess: () => {
          // Redirect handled by controller
        }
      })
    }
  }
}
</script>
