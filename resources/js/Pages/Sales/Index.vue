<template>
  <div>
    <Head title="Sales" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Sales</h1>
      <p class="text-gray-600">Manage and track all sales transactions</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Sales</p>
            <p class="text-2xl font-semibold text-gray-900">{{ summary?.total_sales || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-[#9B672A] bg-opacity-10 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-[#9B672A]" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Amount</p>
            <p class="text-2xl font-semibold text-gray-900">
              {{ $formatAmount(summary?.total_amount || 0) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">This Month</p>
            <p class="text-2xl font-semibold text-gray-900">
              {{ $formatAmount(summary?.this_month || 0) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Today</p>
            <p class="text-2xl font-semibold text-gray-900">
              {{ $formatAmount(summary?.today || 0) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Filter Sales</h2>
        <Link class="btn-kingbaker" href="/sales/create">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
          </svg>
          <span>Create Sale</span>
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="form.search"
            type="text"
            placeholder="Amount, branch, shift..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
          <select v-model="form.branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
          <select v-model="form.shift_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Shifts</option>
            <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
          <input
            v-model="form.date_from"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
          <input
            v-model="form.date_to"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="form.trashed" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">Active Only</option>
            <option value="with">Include Deleted</option>
            <option value="only">Deleted Only</option>
          </select>
        </div>
      </div>

      <div class="flex items-center mt-4 space-x-4">
        <button @click="reset" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
          Clear Filters
        </button>
        <div class="text-sm text-gray-500">
          Showing {{ sales.data?.length || 0 }} of {{ sales.total || 0 }} sales
        </div>
      </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Sales Transactions</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="sale in sales?.data || []" :key="sale.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $formatDate(sale.sales_date) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ sale.branch?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ sale.shift?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ sale.cashier || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                <span class="font-medium text-green-600">
                  {{ $formatAmount(sale.amount) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span v-if="!sale.deleted_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Active
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  Deleted
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="flex items-center justify-center space-x-2">
                  <Link :href="`/sales/${sale.id}/edit`" class="text-brand-600 hover:text-brand-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                  </Link>
                  <button v-if="sale.deleted_at" @click="restore(sale)" class="text-green-600 hover:text-green-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!sales?.data?.length">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                No sales found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="sales?.data?.length" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ sales.from || 0 }} to {{ sales.to || 0 }} of {{ sales.total || 0 }} results
          </div>
          <div class="flex space-x-2">
            <Link
              v-if="sales.prev_page_url"
              :href="sales.prev_page_url"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              preserve-state
            >
              Previous
            </Link>
            <Link
              v-if="sales.next_page_url"
              :href="sales.next_page_url"
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
import throttle from 'lodash/throttle'
import pickBy from 'lodash/pickBy'
import mapValues from 'lodash/mapValues'
import { formatterMixin } from '@/Utils/formatters'


export default {
  components: {
    Head,
    Link,
  },
  mixins: [formatterMixin],
  layout: Layout,
  props: {
    filters: {
      type: Object,
      default: () => ({})
    },
    sales: {
      type: Object,
      default: () => ({ data: [] })
    },
    summary: {
      type: Object,
      default: () => ({})
    },
    branches: {
      type: Array,
      default: () => []
    },
    shifts: {
      type: Array,
      default: () => []
    },
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        branch_id: this.filters.branch_id,
        shift_id: this.filters.shift_id,
        date_from: this.filters.date_from,
        date_to: this.filters.date_to,
        trashed: this.filters.trashed,
      },
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/sales', pickBy(this.form), {
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
    restore(sale) {
      if (confirm('Are you sure you want to restore this sale?')) {
        this.$inertia.put(`/sales/${sale.id}/restore`)
      }
    },
  },
}
</script>