<template>
  <div>
    <Head :title="`Transaction ${transaction.reference_number}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/miscellaneous">Miscellaneous</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ transaction.reference_number }}
      </h1>
      <p class="text-gray-600">View transaction details and information</p>
    </div>

    <!-- Actions -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <Link
          :href="`/miscellaneous/${transaction.id}/edit`"
          class="btn-kingbaker"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Edit Transaction
        </Link>
      </div>

      <button
        @click="confirmDelete"
        class="text-red-600 hover:text-red-800"
      >
        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Delete Transaction
      </button>
    </div>

    <!-- Transaction Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Transaction Details</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <div class="text-sm font-medium text-gray-500">Reference Number</div>
          <div class="text-gray-900 font-mono">{{ transaction.reference_number }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Transaction Date</div>
          <div class="text-gray-900">{{ $formatDate(transaction.transaction_date) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Type</div>
          <div>
            <span :class="getTypeBadgeClass(transaction.type)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
              {{ capitalizeString(transaction.type) }}
            </span>
          </div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Category</div>
          <div class="text-gray-900">{{ formatCategory(transaction.category) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Amount</div>
          <div class="text-gray-900" :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'">
            {{ $formatAmount(transaction.amount) }}
          </div>
        </div>
        <div v-if="transaction.bank_account">
          <div class="text-sm font-medium text-gray-500">Bank Account</div>
          <div class="text-gray-900">{{ transaction.bank_account.name }}</div>
        </div>
        <div v-if="transaction.branch">
          <div class="text-sm font-medium text-gray-500">Branch</div>
          <div class="text-gray-900">{{ transaction.branch.name }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Created By</div>
          <div class="text-gray-900">{{ transaction.creator ? `${transaction.creator.first_name} ${transaction.creator.last_name}` : 'Unknown' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Created At</div>
          <div class="text-gray-900">{{ $formatDate(transaction.created_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Description & Notes -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Description & Notes</h2>
      <div class="space-y-4">
        <div>
          <div class="text-sm font-medium text-gray-500 mb-1">Description</div>
          <div class="text-gray-900 whitespace-pre-wrap">{{ transaction.description }}</div>
        </div>
        <div v-if="transaction.notes">
          <div class="text-sm font-medium text-gray-500 mb-1">Internal Notes</div>
          <div class="text-gray-900 whitespace-pre-wrap">{{ transaction.notes }}</div>
        </div>
      </div>
    </div>

    <!-- Receipt Image -->
    <div v-if="transaction.image_path" class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Receipt Image</h2>
      <div class="flex items-center justify-center">
        <img :src="transaction.image_path" alt="Receipt" class="max-w-full h-auto rounded-lg">
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="closeDeleteModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-2">
          Delete Transaction
        </h2>
        <p class="text-sm text-gray-600 mb-4">
          Are you sure you want to delete this transaction? This action cannot be undone.
        </p>

        <div class="mt-6 flex justify-end space-x-3">
          <button
            type="button"
            class="px-3 py-2 rounded-md text-gray-700 hover:text-gray-900 focus:outline-none"
            @click="closeDeleteModal"
          >
            Cancel
          </button>
          <button
            type="button"
            class="px-3 py-2 rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none"
            :class="{ 'opacity-50 cursor-not-allowed': processing }"
            :disabled="processing"
            @click="deleteTransaction"
          >
            <span v-if="processing">Deleting...</span>
            <span v-else>Delete Transaction</span>
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Modal from '@/Shared/Modal.vue'
import { formatterMixin } from '@/Utils/formatters'

export default {
  components: {
    Head,
    Link,
    Modal,
  },
  mixins: [formatterMixin],
  layout: Layout,
  props: {
    transaction: Object,
  },
  data() {
    return {
      showDeleteModal: false,
      processing: false,
    }
  },
  methods: {
    getTypeBadgeClass(type) {
      return type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
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
    },
    confirmDelete() {
      this.showDeleteModal = true
    },
    closeDeleteModal() {
      this.showDeleteModal = false
    },
    deleteTransaction() {
      this.processing = true
      this.$inertia.delete(`/miscellaneous/${this.transaction.id}`, {
        onFinish: () => {
          this.processing = false
          this.showDeleteModal = false
        },
      })
    },
  },
}
</script>