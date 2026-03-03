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
      <p class="text-gray-600">Transaction details and information</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Main content -->
      <div class="lg:col-span-2">
        <!-- Transaction Details -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Transaction Information</h3>
          </div>
          <div class="p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">Reference Number</dt>
                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ transaction.reference_number }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Transaction Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $formatDate(transaction.transaction_date) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Type</dt>
                <dd class="mt-1">
                  <span :class="getTypeBadgeClass(transaction.type)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ capitalizeString(transaction.type) }}
                  </span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Category</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatCategory(transaction.category) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                <dd class="mt-1 text-sm font-medium" :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                  {{ $formatAmount(transaction.amount) }}
                </dd>
              </div>
              <div v-if="transaction.bank_account">
                <dt class="text-sm font-medium text-gray-500">Bank Account</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.bank_account.name }}</dd>
              </div>
              <div v-if="transaction.branch">
                <dt class="text-sm font-medium text-gray-500">Branch</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.branch.name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.creator ? `${transaction.creator.first_name} ${transaction.creator.last_name}` : 'Unknown' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $formatDate(transaction.created_at) }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Description & Notes -->
        <div class="bg-white rounded-lg shadow overflow-hidden mt-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Description & Notes</h3>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <div class="text-sm font-medium text-gray-500 mb-1">Description</div>
              <div class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ transaction.description }}</div>
            </div>
            <div v-if="transaction.notes">
              <div class="text-sm font-medium text-gray-500 mb-1">Internal Notes</div>
              <div class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ transaction.notes }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions Panel -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Actions</h3>
          <div class="space-y-3">
            <Link
              :href="`/miscellaneous/${transaction.id}/edit`"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
            >
              Edit Transaction
            </Link>

            <button
              @click="deleteTransaction"
              :disabled="form.processing"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Delete Transaction
            </button>
          </div>
        </div>

        <!-- Receipt Image -->
        <div v-if="transaction.image_path" class="bg-white rounded-lg shadow p-6 mt-6">
          <h3 class="text-lg font-semibold mb-4">Receipt Image</h3>
          <div class="flex items-center justify-center">
            <img :src="transaction.image_path" alt="Receipt" class="max-w-full h-auto rounded-lg shadow-sm border border-gray-200">
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

export default {
  components: {
    Head,
    Link,
  },
  mixins: [formatterMixin],
  layout: Layout,
  props: {
    transaction: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        processing: false,
      }),
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
    deleteTransaction() {
      if (confirm('Are you sure you want to delete this transaction? A reverse transaction will be created for the bank account.')) {
        this.form.processing = true;
        this.form.delete(`/miscellaneous/${this.transaction.id}`)
        .then(() => {
          this.form.processing = false;
        })
        .catch(() => {
          this.form.processing = false;
        });
      }
    },
  },
}
</script>