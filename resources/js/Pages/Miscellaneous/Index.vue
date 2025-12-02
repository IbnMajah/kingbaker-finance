<template>
  <div>
    <Head title="Miscellaneous Transactions" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Miscellaneous Transactions</h1>
      <p class="text-gray-600">Track and manage non-operational income and expenses</p>
    </div>

    <!-- Admin Summary Cards -->
    <div v-if="isAdmin" class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Transactions</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ totalTransactions?.toLocaleString() || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Debits</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-red-600">
              {{ $formatAmount(totalDebits) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Credits</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-green-600">
              {{ $formatAmount(totalCredits) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Net Amount</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold" :class="netAmount >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ $formatAmount(netAmount) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Filter Transactions</h2>
        <Link v-if="isAdmin" class="btn-kingbaker" href="/miscellaneous/create">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
          </svg>
          <span>New Transaction</span>
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="form.search"
            type="text"
            placeholder="Reference #, description..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>

        <!-- Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select
            v-model="form.type"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          >
            <option value="">All Types</option>
            <option value="debit">Debit</option>
            <option value="credit">Credit</option>
          </select>
        </div>

        <!-- Bank Account -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label>
          <select
            v-model="form.bank_account_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          >
            <option value="">All Accounts</option>
            <option v-for="account in bankAccounts" :key="account.id" :value="account.id">{{ account.name }}</option>
          </select>
        </div>

        <!-- Branch -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
          <select
            v-model="form.branch_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          >
            <option value="">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        <!-- Category -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            v-model="form.category"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          >
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ capitalizeString(category) }}
            </option>
          </select>
        </div>

        <!-- Date From -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
          <input
            v-model="form.date_from"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>

        <!-- Date To -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
          <input
            v-model="form.date_to"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="form.trashed"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          >
            <option value="">Active Only</option>
            <option value="with">Include Deleted</option>
            <option value="only">Deleted Only</option>
          </select>
        </div>
      </div>

      <div class="flex items-center mt-4 space-x-4">
        <button @click="clearFilters" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
          Clear Filters
        </button>
        <div class="text-sm text-gray-500">
          Showing {{ transactions.data?.length || 0 }} of {{ transactions.total || 0 }} transactions
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Miscellaneous Transactions</h2>
      </div>

      <div v-if="loading" class="flex items-center justify-center p-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-500"></div>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="w-28 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="font-medium">{{ transaction.reference_number }}</div>
                <div class="text-sm text-gray-500">{{ transaction.branch?.name || 'No Branch' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $formatDate(transaction.transaction_date) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate">{{ transaction.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getTypeBadgeClass(transaction.type)"
                >
                  {{ capitalizeString(transaction.type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                <span :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                  {{ $formatAmount(transaction.amount) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCategory(transaction.subcategory || transaction.category) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ transaction.bank_account?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span v-if="!transaction.deleted_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Active
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  Deleted
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="flex items-center justify-center space-x-2">
                  <Link :href="`/miscellaneous/${transaction.id}`" class="text-brand-600 hover:text-brand-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                    </svg>
                  </Link>
                  <Link v-if="!transaction.deleted_at" :href="`/miscellaneous/${transaction.id}/edit`" class="text-brand-600 hover:text-brand-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                  </Link>
                  <button v-if="transaction.deleted_at" @click="restore(transaction)" class="text-green-600 hover:text-green-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!transactions.data?.length">
              <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                No transactions found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="transactions?.data?.length" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ transactions.from || 0 }} to {{ transactions.to || 0 }} of {{ transactions.total || 0 }} results
          </div>
          <Pagination v-if="transactions.links" :links="transactions.links" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Pagination from '@/Shared/Pagination.vue'
import { formatterMixin } from '@/Utils/formatters'
import { usePermissions } from '@/composables/usePermissions.js'
import throttle from 'lodash/throttle'
import pickBy from 'lodash/pickBy'
import mapValues from 'lodash/mapValues'

export default {
  components: {
    Head,
    Link,
    Pagination,
  },
  mixins: [formatterMixin],
  layout: Layout,
  setup() {
    const permissions = usePermissions()
    return { permissions }
  },
  props: {
    filters: {
      type: Object,
      default: () => ({})
    },
    transactions: {
      type: Object,
      default: () => ({ data: [] })
    },
    totalTransactions: Number,
    totalDebits: Number,
    totalCredits: Number,
    netAmount: Number,
    bankAccounts: Array,
    branches: Array,
    categories: Array,
  },
  data() {
    return {
      loading: false,
      form: {
        search: this.filters.search || '',
        type: this.filters.type || '',
        bank_account_id: this.filters.bank_account_id || '',
        branch_id: this.filters.branch_id || '',
        category: this.filters.category || '',
        date_from: this.filters.date_from || '',
        date_to: this.filters.date_to || '',
        trashed: this.filters.trashed || '',
      },
    }
  },
  computed: {
    isAdmin() {
      return this.permissions.hasRole('admin') || this.$page.props.auth.user?.owner
    },
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.loading = true
        this.$inertia.get('/miscellaneous', pickBy(this.form), {
          preserveState: true,
          onFinish: () => {
            this.loading = false
          }
        })
      }, 150),
    },
  },
  methods: {
    clearFilters() {
      this.form = mapValues(this.form, () => '')
    },
    reset() {
      this.form = mapValues(this.form, () => '')
    },
    getTypeBadgeClass(type) {
      return type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
    },
    deleteTransaction(transaction) {
      if (confirm(`Are you sure you want to delete transaction ${transaction.reference_number}? This action cannot be undone.`)) {
        this.$inertia.delete(`/miscellaneous/${transaction.id}`)
      }
    },
    restore(transaction) {
      if (confirm('Are you sure you want to restore this transaction?')) {
        this.$inertia.put(`/miscellaneous/${transaction.id}/restore`)
      }
    },
    capitalizeString(str) {
      if (!str) return ''
      return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
    },
    formatCategory(category) {
      if (!category) return ''
      return category.split('_')
        .map(word => this.capitalizeString(word))
        .join(' ')
    }
  },
}
</script>