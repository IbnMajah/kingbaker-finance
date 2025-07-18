<template>
  <div>
    <Head title="Deposits" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Deposits</h1>
      <p class="text-gray-600">Manage and track all deposit transactions</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Deposits</p>
            <p class="text-2xl font-semibold text-gray-900">{{ summary?.total_deposits || 0 }}</p>
          </div>
        </div>
      </div>

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
            <p class="text-2xl font-semibold text-gray-900"> {{ $formatAmount(summary?.total_amount) }}</p>
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
        <h2 class="text-lg font-semibold">Filter Deposits</h2>
        <Link class="btn-kingbaker" href="/deposits/create">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
          </svg>
          <span>Create Deposit</span>
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="form.search"
            type="text"
            placeholder="Reference, description, bank..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Deposit Type</label>
          <select v-model="form.deposit_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Deposit Types</option>
            <option v-for="type in depositTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label>
          <select v-model="form.bank_account_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Accounts</option>
            <option v-for="account in bankAccounts" :key="account.id" :value="account.id">{{ account.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
          <select v-model="form.branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
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
      </div>

      <div class="flex items-center mt-4 space-x-4">
        <button @click="reset" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
          Clear Filters
        </button>
        <div class="text-sm text-gray-500">
          Showing {{ deposits.data?.length || 0 }} of {{ deposits.total || 0 }} deposits
        </div>
      </div>
    </div>

    <!-- Deposits Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Deposit Transactions</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deposit Type</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="deposit in deposits?.data || []" :key="deposit.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $formatDate(deposit.deposit_date) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="font-medium">{{ deposit.reference_number }}</div>
                <div v-if="deposit.description" class="text-xs text-gray-500 truncate max-w-xs">{{ deposit.description }}</div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ deposit.bank_account?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ deposit.deposit_type?.replace('_', ' ').toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                <span class="font-medium text-green-600">
                  {{ $formatAmount(deposit.amount) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ deposit.branch?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="flex items-center justify-center space-x-2">
                  <Link :href="`/deposits/${deposit.id}`" class="text-blue-600 hover:text-blue-900" title="View Details">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                    </svg>
                  </Link>
                  <Link :href="`/deposits/${deposit.id}/edit`" class="text-brand-600 hover:text-brand-900" title="Edit">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                  </Link>
                  <button v-if="deposit.image_path" @click="showImage(deposit.image_path)" class="text-gray-600 hover:text-gray-900" title="View Receipt">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!deposits?.data?.length">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                No deposits found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="deposits?.data?.length" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ deposits.from || 0 }} to {{ deposits.to || 0 }} of {{ deposits.total || 0 }} results
          </div>
          <div class="flex space-x-2">
            <Link
              v-if="deposits.prev_page_url"
              :href="deposits.prev_page_url"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              preserve-state
            >
              Previous
            </Link>
            <Link
              v-if="deposits.next_page_url"
              :href="deposits.next_page_url"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              preserve-state
            >
              Next
            </Link>
          </div>
        </div>
      </div>
    </div>

        <!-- Image Modal -->
    <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="closeImageModal">
      <div class="relative w-1/2 h-4/5 flex items-center justify-center">
        <img
          :src="`/storage/${selectedImage}`"
          alt="Receipt Image"
          class="w-full h-full object-contain rounded-lg shadow-2xl"
          @click.stop
        />
        <button
          @click="closeImageModal"
          class="absolute top-4 right-4 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 transition-colors"
        >
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          </svg>
        </button>
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
    deposits: {
      type: Object,
      default: () => ({ data: [] })
    },
    summary: {
      type: Object,
      default: () => ({})
    },
    bankAccounts: {
      type: Array,
      default: () => []
    },
    branches: {
      type: Array,
      default: () => []
    },
    depositTypes: {
      type: Array,
      default: () => []
    },
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        deposit_type: this.filters.deposit_type,
        bank_account_id: this.filters.bank_account_id,
        branch_id: this.filters.branch_id,
        date_from: this.filters.date_from,
        date_to: this.filters.date_to,
      },
      showImageModal: false,
      selectedImage: null,
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/deposits', pickBy(this.form), {
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
    showImage(imagePath) {
      this.selectedImage = imagePath
      this.showImageModal = true
    },
    closeImageModal() {
      this.showImageModal = false
      this.selectedImage = null
    },
  },
}
</script>