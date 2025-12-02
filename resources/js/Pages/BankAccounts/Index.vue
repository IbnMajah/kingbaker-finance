<template>
  <div>
    <Head title="Bank Accounts" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Accounts</h1>
      <p class="text-gray-600">Manage bank accounts and access account sheets</p>
    </div>

    <!-- Admin Summary Cards -->
    <div v-if="isAdmin" class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Accounts</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ bankAccounts.total || 0 }}</p>
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
            <p class="text-xs md:text-sm font-medium text-gray-600">Active Accounts</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ activeCount }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Inactive Accounts</p>
            <p class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ inactiveCount }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-3 md:p-6">
        <div class="flex items-center">
          <div class="hidden lg:flex flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="lg:ml-4">
            <p class="text-xs md:text-sm font-medium text-gray-600">Total Balance</p>
            <p v-if="isAdmin" class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900">{{ $formatAmount(totalBalance) }}</p>
            <p v-else class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-500">••••••</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Filter Accounts</h2>
        <Link v-if="canCreateBankAccounts" class="btn-kingbaker" href="/bank-accounts/create">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
          </svg>
          <span>Create Account</span>
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="form.search"
            type="text"
            placeholder="Account name, number, bank name..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div class="flex items-center mt-4 space-x-4">
        <button @click="reset" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
          Clear Filters
        </button>
        <div class="text-sm text-gray-500">
          Showing {{ bankAccounts.data?.length || 0 }} of {{ bankAccounts.total || 0 }} accounts
        </div>
      </div>
    </div>

    <!-- Accounts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Bank Accounts</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Details</th>
              <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
              <th class="w-20 px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="w-24 px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="account in bankAccounts?.data || []" :key="account.id" class="hover:bg-gray-50">
              <td class="px-3 py-2 text-xs">
                <div class="font-medium text-gray-900 truncate max-w-40">{{ account.name }}</div>
              </td>
              <td class="px-3 py-2 text-xs">
                <div class="text-gray-900 font-medium">{{ account.bank_name || 'N/A' }}</div>
                <div class="text-gray-500 text-xs">{{ account.account_number || 'N/A' }}</div>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-xs text-right">
                <span v-if="isAdmin" :class="account.current_balance >= 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                  {{ $formatAmount(account.current_balance) }}
                </span>
                <span v-else class="text-gray-500 font-medium">
                  ••••••
                </span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-center">
                <span v-if="account.active" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Active
                </span>
                <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  Inactive
                </span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-center">
                <div class="flex items-center justify-center space-x-2">
                  <Link v-if="canViewBankAccounts" class="text-brand-600 hover:text-brand-900 text-xs font-medium" :href="`/bank-accounts/${account.id}`" title="Account Sheet">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                    </svg>
                  </Link>
                  <Link v-if="canEditBankAccounts" class="text-gray-600 hover:text-gray-900 text-xs font-medium" :href="`/bank-accounts/${account.id}/edit`" title="Edit">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                  </Link>
                </div>
              </td>
            </tr>
            <tr v-if="!bankAccounts?.data?.length">
              <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                No bank accounts found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="bankAccounts?.data?.length" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ bankAccounts.from || 0 }} to {{ bankAccounts.to || 0 }} of {{ bankAccounts.total || 0 }} results
          </div>
          <Pagination v-if="bankAccounts.links" :links="bankAccounts.links" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Pagination from '@/Shared/Pagination.vue'
import { usePermissions } from '@/composables/usePermissions.js'
import throttle from 'lodash/throttle'
import pickBy from 'lodash/pickBy'
import mapValues from 'lodash/mapValues'
// import { formatAmount, formatDate } from '../../Utils/formatters'
import { formatterMixin } from '@/Utils/formatters'

export default {
  components: {
    Head,
    Link,
    Pagination,
  },
  mixins: [formatterMixin],
  layout: Layout,
  setup() {
    const { canCreateBankAccounts, canViewBankAccounts, canEditBankAccounts, isAdmin } = usePermissions()
    return { canCreateBankAccounts, canViewBankAccounts, canEditBankAccounts, isAdmin }
  },
  props: {
    filters: {
      type: Object,
      default: () => ({})
    },
    bankAccounts: {
      type: Object,
      default: () => ({ data: [] })
    },
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        status: this.filters.status,
      },
    }
  },
  computed: {
    activeCount() {
      return this.bankAccounts?.data?.filter(account => account.active).length || 0
    },
    inactiveCount() {
      return this.bankAccounts?.data?.filter(account => !account.active).length || 0
    },
    totalBalance() {
      return this.bankAccounts?.data?.reduce((sum, account) => sum + parseFloat(account.current_balance || 0), 0) || 0
    },
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/bank-accounts', pickBy(this.form), {
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