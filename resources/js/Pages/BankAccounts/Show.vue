<template>
  <div>
    <Head :title="`${bankAccount.name} - Account Sheet`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/bank-accounts">Bank Accounts</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ bankAccount.name }}
      </h1>
      <p class="text-gray-600">Account Sheet & Transaction History</p>
    </div>

    <!-- Account Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Current Balance</div>
        <div v-if="isAdmin" class="text-2xl font-bold" :class="bankAccount.current_balance >= 0 ? 'text-green-600' : 'text-red-600'">
          {{ $formatAmount(bankAccount.current_balance) }}
        </div>
        <div v-else class="text-2xl font-bold text-gray-500">
          ••••••
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Total Credits</div>
        <div v-if="isAdmin" class="text-2xl font-bold text-green-600">
          {{ $formatAmount(summary.total_credits) }}
        </div>
        <div v-else class="text-2xl font-bold text-gray-500">
          ••••••
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Total Debits</div>
        <div v-if="isAdmin" class="text-2xl font-bold text-red-600">
          {{ $formatAmount(summary.total_debits) }}
        </div>
        <div v-else class="text-2xl font-bold text-gray-500">
          ••••••
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Net Movement</div>
        <div v-if="isAdmin" class="text-2xl font-bold" :class="summary.net_movement >= 0 ? 'text-green-600' : 'text-red-600'">
          {{ $formatAmount(summary.net_movement) }}
        </div>
        <div v-else class="text-2xl font-bold text-gray-500">
          ••••••
        </div>
      </div>
    </div>

    <!-- Account Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Account Details</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <div class="text-sm font-medium text-gray-500">Account Number</div>
          <div class="text-gray-900">{{ bankAccount.account_number || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Bank Name</div>
          <div class="text-gray-900">{{ bankAccount.bank_name || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Status</div>
          <div :class="bankAccount.active ? 'text-green-600' : 'text-red-600'">
            {{ bankAccount.active ? 'Active' : 'Inactive' }}
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-lg font-semibold mb-4">Filter Transactions</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="form.search"
            type="text"
            placeholder="Description, reference, payee..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Types</option>
            <option value="credit">Credit</option>
            <option value="debit">Debit</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Payment Mode</label>
          <select v-model="form.payment_mode" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Modes</option>
            <option v-for="mode in paymentModes" :key="mode" :value="mode">
              {{ mode.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select v-model="form.category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input
            v-model="form.date_from"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input
            v-model="form.date_to"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
      </div>
      <div class="flex items-center mt-4 space-x-4">
        <button @click="reset" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
          Clear Filters
        </button>
        <div class="text-sm text-gray-500">
          Showing {{ transactions.data.length }} of {{ transactions.total }} transactions
        </div>
      </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Transaction History</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payee</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $formatDate(transaction.transaction_date) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate" :title="transaction.description">
                  {{ transaction.description || '-' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ transaction.reference_number || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  {{ transaction.payment_mode.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ transaction.payee || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                <span v-if="transaction.type === 'debit'" class="text-red-600 font-medium">
                  {{ $formatAmount(transaction.amount) }}
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                <span v-if="transaction.type === 'credit'" class="text-green-600 font-medium">
                  {{ $formatAmount(transaction.amount) }}
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span v-if="transaction.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ transaction.category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
            </tr>
            <tr v-if="!transactions.data.length">
              <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                No transactions found for this account.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="transactions.data.length" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ transactions.from || 0 }} to {{ transactions.to || 0 }} of {{ transactions.total }} results
          </div>
          <div class="flex space-x-2">
            <Link
              v-if="transactions.prev_page_url"
              :href="transactions.prev_page_url"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              preserve-state
            >
              Previous
            </Link>
            <Link
              v-if="transactions.next_page_url"
              :href="transactions.next_page_url"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              preserve-state
            >
              Next
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import { formatterMixin } from '@/Utils/formatters'
import { usePermissions } from '@/composables/usePermissions.js'
import throttle from 'lodash/throttle'
import pickBy from 'lodash/pickBy'
import mapValues from 'lodash/mapValues'

export default {
  components: {
    Head,
    Link,
  },
  mixins: [formatterMixin],
  layout: Layout,
  setup() {
    const { isAdmin } = usePermissions()
    return { isAdmin }
  },
  props: {
    bankAccount: Object,
    transactions: Object,
    filters: Object,
    summary: Object,
    paymentModes: Array,
    categories: Array,
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        type: this.filters.type,
        payment_mode: this.filters.payment_mode,
        category: this.filters.category,
        date_from: this.filters.date_from,
        date_to: this.filters.date_to,
      },
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get(`/bank-accounts/${this.bankAccount.id}`, pickBy(this.form), {
          preserveState: true,
          preserveScroll: true,
        })
      }, 300),
    },
  },
  methods: {
    reset() {
      this.form = mapValues(this.form, () => '')
    },
  },
}
</script>