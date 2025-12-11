<template>
  <div>
    <Head :title="`${contact.name} - Customer Details`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/contacts">Customers</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ contact.name }}
      </h1>
      <p class="text-gray-600">Customer details and credit information</p>
    </div>

    <!-- Credit Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
            <p class="text-sm font-medium text-gray-600">Total Credits</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatAmount(credit_summary.total_credits) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Paid</p>
            <p class="text-2xl font-semibold text-green-600">{{ $formatAmount(credit_summary.total_paid) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Outstanding</p>
            <p class="text-2xl font-semibold" :class="credit_summary.total_outstanding > 0 ? 'text-red-600' : 'text-green-600'">
              {{ $formatAmount(credit_summary.total_outstanding) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Invoices</p>
            <p class="text-2xl font-semibold text-gray-900">{{ credit_summary.invoices_count }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <Link
          :href="`/contacts/${contact.id}/edit`"
          class="btn-kingbaker"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Edit Customer
        </Link>
      </div>

      <button
        v-if="!contact.deleted_at"
        @click="confirmDelete"
        class="text-red-600 hover:text-red-800"
      >
        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Delete Customer
      </button>
    </div>

    <!-- Customer Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Customer Information</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <div class="text-sm font-medium text-gray-500">Name</div>
          <div class="text-gray-900 text-lg font-medium">{{ contact.name }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Customer Type</div>
          <div class="text-gray-900">
            <span v-if="contact.customer_type" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getCustomerTypeClass(contact.customer_type)">
              {{ contact.customer_type }}
            </span>
            <span v-else class="text-gray-400">-</span>
          </div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Email</div>
          <div class="text-gray-900">
            <a v-if="contact.email" :href="`mailto:${contact.email}`" class="text-brand-600 hover:text-brand-500">
              {{ contact.email }}
            </a>
            <span v-else class="text-gray-400">-</span>
          </div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Phone</div>
          <div class="text-gray-900">
            <a v-if="contact.phone" :href="`tel:${contact.phone}`" class="text-brand-600 hover:text-brand-500">
              {{ contact.phone }}
            </a>
            <span v-else class="text-gray-400">-</span>
          </div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Address</div>
          <div class="text-gray-900">{{ contact.address || '-' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Created At</div>
          <div class="text-gray-900">{{ $formatDate(contact.created_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Invoices -->
    <div v-if="invoices && invoices.length > 0" class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Invoices</h2>
        <div class="text-sm text-gray-500">Total: {{ invoices.length }} invoice(s)</div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900">
                {{ invoice.invoice_number }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                {{ $formatDate(invoice.invoice_date) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                {{ getInvoiceTypeLabel(invoice.invoice_type) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                {{ $formatAmount(invoice.amount) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600">
                {{ $formatAmount(invoice.amount_paid) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium" :class="invoice.remaining_amount > 0 ? 'text-red-600' : 'text-green-600'">
                {{ $formatAmount(invoice.remaining_amount) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-center">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="getStatusClass(invoice.status)">
                  {{ getStatusLabel(invoice.status) }}
                </span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-center">
                <Link :href="`/invoices/${invoice.id}`" class="text-blue-600 hover:text-blue-900" title="View">
                  <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Sales Credit Items -->
    <div v-if="sales_credit_items && sales_credit_items.length > 0" class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Sales Credit Items</h2>
        <div class="text-sm text-gray-500">Total: {{ sales_credit_items.length }} item(s)</div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Date</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in sales_credit_items" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                {{ $formatDate(item.sale?.sales_date) }}
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
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                {{ item.sale?.branch?.name || 'N/A' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-center">
                <Link v-if="item.sale" :href="`/sales/${item.sale.id}`" class="text-blue-600 hover:text-blue-900" title="View Sale">
                  <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </Link>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td colspan="5" class="px-4 py-3 text-right text-sm font-medium text-gray-700">
                Total Sales Credit:
              </td>
              <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">
                {{ $formatAmount(salesCreditTotal) }}
              </td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- No Credits Message -->
    <div v-if="(!invoices || invoices.length === 0) && (!sales_credit_items || sales_credit_items.length === 0)" class="bg-white rounded-lg shadow p-12 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No credits found</h3>
      <p class="mt-1 text-sm text-gray-500">This customer has no invoices or sales credit items.</p>
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
    contact: {
      type: Object,
      required: true
    },
    invoices: {
      type: Array,
      default: () => []
    },
    sales_credit_items: {
      type: Array,
      default: () => []
    },
    credit_summary: {
      type: Object,
      required: true
    },
  },
  computed: {
    salesCreditTotal() {
      if (!this.sales_credit_items || !Array.isArray(this.sales_credit_items)) {
        return 0
      }
      return this.sales_credit_items.reduce((total, item) => {
        return total + (parseFloat(item.total) || 0)
      }, 0)
    }
  },
  methods: {
    confirmDelete() {
      if (confirm('Are you sure you want to delete this customer?')) {
        this.$inertia.delete(`/contacts/${this.contact.id}`)
      }
    },
    getCustomerTypeClass(type) {
      const classes = {
        individual: 'bg-gray-100 text-gray-800',
        shop: 'bg-blue-100 text-blue-800',
        partner: 'bg-purple-100 text-purple-800',
        branch: 'bg-green-100 text-green-800',
        hotel: 'bg-orange-100 text-orange-800',
        other: 'bg-gray-100 text-gray-800',
      }
      return classes[type] || 'bg-gray-100 text-gray-800'
    },
    getInvoiceTypeLabel(type) {
      const labels = {
        bulk_sales: 'Bulk Sales',
        credit_customer: 'Credit Customer',
        loans: 'Loans',
        daily_supply: 'Daily Supply',
        partner_billing: 'Partner Billing',
        other: 'Other',
      }
      return labels[type] || type
    },
    getStatusLabel(status) {
      const labels = {
        draft: 'Draft',
        sent: 'Sent',
        paid: 'Paid',
        partially_paid: 'Partially Paid',
        overdue: 'Overdue',
        cancelled: 'Cancelled',
      }
      return labels[status] || status
    },
    getStatusClass(status) {
      const classes = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        partially_paid: 'bg-yellow-100 text-yellow-800',
        overdue: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },
  },
}
</script>
