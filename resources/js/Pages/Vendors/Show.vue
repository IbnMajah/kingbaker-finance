<template>
  <div>
    <Head :title="`${vendor.name} - Vendor Details`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/vendors">Vendors</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ vendor.name }}
      </h1>
      <p class="text-gray-600">Vendor details and bill history</p>
    </div>

    <!-- Vendor Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Bills</p>
            <p class="text-2xl font-semibold text-gray-900">{{ totalBills }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Paid Bills</p>
            <p class="text-2xl font-semibold text-gray-900">{{ paidBills }}</p>
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
            <p class="text-sm font-medium text-gray-600">Pending Bills</p>
            <p class="text-2xl font-semibold text-gray-900">{{ pendingBills }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Amount</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatAmount(totalAmount) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Vendor Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Vendor Details</h2>
        <Link :href="`/vendors/${vendor.id}/edit`" class="btn-kingbaker">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
          </svg>
          Edit Vendor
        </Link>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <div class="text-sm font-medium text-gray-500">Contact Person</div>
          <div class="text-gray-900">{{ vendor.contact_person || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Email</div>
          <div class="text-gray-900">{{ vendor.email || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Phone</div>
          <div class="text-gray-900">{{ vendor.phone || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Vendor Type</div>
          <div class="text-gray-900">
            {{ vendor.vendor_type ? vendor.vendor_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A' }}
          </div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Organization</div>
          <div class="text-gray-900">{{ vendor.organization?.name || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Status</div>
          <div :class="vendor.active ? 'text-green-600' : 'text-red-600'">
            {{ vendor.active ? 'Active' : 'Inactive' }}
          </div>
        </div>
        <div class="md:col-span-3">
          <div class="text-sm font-medium text-gray-500">Address</div>
          <div class="text-gray-900">{{ vendor.address || 'N/A' }}</div>
        </div>
        <div v-if="vendor.description" class="md:col-span-3">
          <div class="text-sm font-medium text-gray-500">Description</div>
          <div class="text-gray-900">{{ vendor.description }}</div>
        </div>
      </div>
    </div>

    <!-- Recent Bills -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Recent Bills</h2>
          <Link href="/bills/create" class="text-brand-600 hover:text-brand-800 text-sm font-medium">
            Create New Bill
          </Link>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="bill in vendor.bills" :key="bill.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ bill.bill_number || `BILL-${bill.id}` }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $formatDate(bill.bill_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ bill.due_date ? $formatDate(bill.due_date) : 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate" :title="bill.description">
                  {{ bill.description || '-' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                {{ $formatAmount(bill.amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="{
                    'bg-green-100 text-green-800': bill.status === 'paid',
                    'bg-yellow-100 text-yellow-800': bill.status === 'partially_paid',
                    'bg-red-100 text-red-800': bill.status === 'overdue',
                    'bg-gray-100 text-gray-800': bill.status === 'pending'
                  }"
                >
                  {{ bill.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                <Link :href="`/bills/${bill.id}/edit`" class="text-brand-600 hover:text-brand-900 mr-2">
                  View
                </Link>
              </td>
            </tr>
            <tr v-if="!vendor.bills?.length">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                No bills found for this vendor.
              </td>
            </tr>
          </tbody>
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
    vendor: {
      type: Object,
      required: true
    }
  },
  computed: {
    totalBills() {
      return this.vendor.bills?.length || 0
    },
    paidBills() {
      return this.vendor.bills?.filter(bill => bill.status === 'paid').length || 0
    },
    pendingBills() {
      return this.vendor.bills?.filter(bill => bill.status === 'pending').length || 0
    },
    totalAmount() {
      return this.vendor.bills?.reduce((sum, bill) => sum + parseFloat(bill.amount || 0), 0) || 0
    }
  }
}
</script>