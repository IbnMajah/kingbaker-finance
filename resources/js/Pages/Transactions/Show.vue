<template>
  <div>
    <Head :title="`Transaction #${transaction.id}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/transactions">Transactions</Link>
        <span class="text-brand-400 font-medium">/</span>
        #{{ transaction.id }}
      </h1>
      <p class="text-gray-600">Transaction Details & Information</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div :class="transaction.type === 'credit' ? 'w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center' : 'w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center'">
              <svg v-if="transaction.type === 'credit'" class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-10.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 7.414V11a1 1 0 102 0V7.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Amount</p>
            <p :class="transaction.type === 'credit' ? 'text-2xl font-semibold text-green-600' : 'text-2xl font-semibold text-red-600'">
              {{ transaction.type === 'credit' ? '+' : '-' }}{{ $formatAmount(transaction.amount) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Payment Mode</p>
            <p class="text-2xl font-semibold text-gray-900">{{ transaction.payment_mode?.replace('_', ' ').toUpperCase() }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Transaction Date</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatDate(transaction.transaction_date) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div :class="transaction.is_reconciled ? 'w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center' : 'w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center'">
              <svg v-if="transaction.is_reconciled" class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Status</p>
            <p class="text-2xl font-semibold text-gray-900">{{ transaction.is_reconciled ? 'Reconciled' : 'Pending' }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Transaction Details -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Transaction Information</h3>
          </div>
          <div class="p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">Reference Number</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.reference_number || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Bank Account</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.bank_account?.name || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Payee</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.payee || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Category</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.category?.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Branch</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.branch?.name || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.creator?.name || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $formatDateTime(transaction.created_at) }}</dd>
              </div>
              <div v-if="transaction.description" class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.description }}</dd>
              </div>
              <div v-if="transaction.bill_payment" class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Related Bill</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      Bill #{{ transaction.bill_payment.bill?.bill_number }}
                    </span>
                    <span class="text-gray-600">{{ transaction.bill_payment.bill?.vendor?.name }}</span>
                  </div>
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>

      <!-- Actions Panel -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Actions</h3>
          <div class="space-y-3">
            <button
              v-if="!transaction.is_reconciled"
              @click="reconcileTransaction"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
              Mark as Reconciled
            </button>

            <button
              v-else
              @click="unreconcileTransaction"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
            >
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
              Mark as Pending
            </button>


          </div>
        </div>

        <!-- Transaction Image -->
        <div v-if="transaction.image_path" class="bg-white rounded-lg shadow p-6 mt-6">
          <h3 class="text-lg font-semibold mb-4">Receipt Image</h3>
          <div class="cursor-pointer" @click="showImage(transaction.image_path)">
            <img
              :src="`/storage/${transaction.image_path}`"
              :alt="`Receipt for transaction ${transaction.id}`"
              class="max-w-full h-auto rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
            />
          </div>
          <p class="text-xs text-gray-500 mt-2">Click to view full size</p>
        </div>

        <!-- Image Modal -->
        <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="closeImageModal">
          <div class="relative w-1/2 h-4/5 flex items-center justify-center">
            <img
              :src="`/storage/${selectedImage}`"
              :alt="`Receipt for transaction ${transaction.id}`"
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
    transaction: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      form: this.$inertia.form({}),
      showImageModal: false,
      selectedImage: null,
    }
  },
  methods: {
    reconcileTransaction() {
      this.$inertia.put(`/transactions/${this.transaction.id}/reconcile`, {}, {
        preserveState: true,
        onSuccess: () => {
          // Transaction will be updated via Inertia page reload
        }
      })
    },
    unreconcileTransaction() {
      this.$inertia.put(`/transactions/${this.transaction.id}/unreconcile`, {}, {
        preserveState: true,
        onSuccess: () => {
          // Transaction will be updated via Inertia page reload
        }
      })
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