<template>
  <div>
    <Head :title="`Sale #${sale.id}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/sales">Sales</Link>
        <span class="text-brand-400 font-medium">/</span>
        Sale #{{ sale.id }}
      </h1>
      <p class="text-gray-600">Sale transaction details and information</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Amount</p>
            <p class="text-2xl font-semibold text-green-600">{{ $formatAmount(sale.total_amount || sale.amount) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Depositable Amount</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatAmount(sale.amount) }}</p>
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
            <p class="text-sm font-medium text-gray-600">Sales Date</p>
            <p class="text-lg font-semibold text-gray-900">{{ $formatDate(sale.sales_date) }}</p>
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
            <p class="text-sm font-medium text-gray-600">Credit Items</p>
            <p class="text-2xl font-semibold text-gray-900">{{ sale.credit_items?.length || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <Link
          :href="`/sales/${sale.id}/edit`"
          class="btn-kingbaker"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Edit Sale
        </Link>
      </div>

      <button
        v-if="!sale.deleted_at"
        @click="confirmDelete"
        class="text-red-600 hover:text-red-800"
      >
        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Delete Sale
      </button>
    </div>

    <!-- Sale Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Sale Details</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div>
          <div class="text-sm font-medium text-gray-500">Branch</div>
          <div class="text-gray-900">{{ sale.branch?.name || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Shift</div>
          <div class="text-gray-900">{{ sale.shift?.name || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Sales Date</div>
          <div class="text-gray-900">{{ $formatDate(sale.sales_date) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Cashier</div>
          <div class="text-gray-900">{{ sale.cashier || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Closing Manager</div>
          <div class="text-gray-900">{{ sale.closing_manager || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Created At</div>
          <div class="text-gray-900">{{ $formatDate(sale.created_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Payment Methods</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div>
          <div class="text-sm font-medium text-gray-500 mb-1">Cash</div>
          <div class="text-lg font-semibold text-gray-900">{{ $formatAmount(sale.cash_amount || 0) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500 mb-1">Bank Transfer</div>
          <div class="text-lg font-semibold text-gray-900">{{ $formatAmount(sale.bank_transfer_amount || 0) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500 mb-1">Mobile Money - MyNafa</div>
          <div class="text-lg font-semibold text-gray-900">{{ $formatAmount(sale.mobile_money_my_nafa || 0) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500 mb-1">Mobile Money - APS</div>
          <div class="text-lg font-semibold text-gray-900">{{ $formatAmount(sale.mobile_money_aps || 0) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500 mb-1">Mobile Money - Wave</div>
          <div class="text-lg font-semibold text-gray-900">{{ $formatAmount(sale.mobile_money_wave || 0) }}</div>
        </div>
      </div>
    </div>

    <!-- Credit Items -->
    <div v-if="sale.credit_items && sale.credit_items.length > 0" class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Credit Customer Items</h2>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in sale.credit_items" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                {{ item.customer?.name || 'N/A' }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-900">
                {{ item.description }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                {{ item.unit_measurement || 'N/A' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                {{ $formatAmount(item.unit_price) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                {{ item.quantity }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                {{ $formatAmount(item.total) }}
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                Total Credit Amount:
              </td>
              <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">
                {{ $formatAmount(creditItemsTotal) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
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
    sale: {
      type: Object,
      required: true
    },
  },
  computed: {
    creditItemsTotal() {
      if (!this.sale.credit_items || !Array.isArray(this.sale.credit_items)) {
        return 0
      }
      return this.sale.credit_items.reduce((total, item) => {
        return total + (parseFloat(item.total) || 0)
      }, 0)
    }
  },
  methods: {
    confirmDelete() {
      if (confirm('Are you sure you want to delete this sale?')) {
        this.$inertia.delete(`/sales/${this.sale.id}`)
      }
    },
  },
}
</script>
