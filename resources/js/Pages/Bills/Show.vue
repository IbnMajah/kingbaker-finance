<template>
  <div>
    <Head :title="`${bill.bill_number || `Bill #${bill.id}`} - Bill Details`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/bills">Bills</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ bill.bill_number || `Bill #${bill.id}` }}
      </h1>
      <p class="text-gray-600">Bill details and payment history</p>
    </div>

    <!-- Bill Summary Cards -->
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
            <p class="text-sm font-medium text-gray-600">Total Amount</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatAmount(bill.amount) }}</p>
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
            <p class="text-sm font-medium text-gray-600">Amount Paid</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatAmount(bill.amount_paid || 0) }}</p>
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
            <p class="text-sm font-medium text-gray-600">Balance Due</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatAmount(balanceDue) }}</p>
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
            <p class="text-sm font-medium text-gray-600">Status</p>
            <p class="text-lg font-semibold" :class="{
              'text-green-600': bill.status === 'paid',
              'text-yellow-600': bill.status === 'partially_paid',
              'text-red-600': bill.status === 'overdue' && bill.status !== 'paid',
              'text-gray-600': bill.status === 'pending'
            }">
              {{ bill.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Bill Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Bill Details</h2>
        <Link :href="`/bills/${bill.id}/edit`" class="btn-kingbaker">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
          </svg>
          Edit Bill
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div>
          <div class="text-sm font-medium text-gray-500">Vendor</div>
          <div class="text-gray-900">{{ bill.vendor?.name || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Bill Number</div>
          <div class="text-gray-900">{{ bill.bill_number || 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Bill Type</div>
          <div class="text-gray-900">
            {{ bill.bill_type ? bill.bill_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A' }}
          </div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Bill Date</div>
          <div class="text-gray-900">{{ $formatDate(bill.bill_date) }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Due Date</div>
          <div class="text-gray-900">{{ bill.due_date ? $formatDate(bill.due_date) : 'N/A' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Created By</div>
          <div class="text-gray-900">{{ bill.creator?.first_name + ' ' + bill.creator?.last_name || 'N/A' }}</div>
        </div>
        <div v-if="bill.recurring_frequency">
          <div class="text-sm font-medium text-gray-500">Recurring</div>
          <div class="text-gray-900">{{ bill.recurring_frequency.replace(/\b\w/g, l => l.toUpperCase()) }}</div>
        </div>
        <div v-if="bill.next_due_date">
          <div class="text-sm font-medium text-gray-500">Next Due Date</div>
          <div class="text-gray-900">{{ $formatDate(bill.next_due_date) }}</div>
        </div>
      </div>

      <div v-if="bill.description" class="mb-6">
        <div class="text-sm font-medium text-gray-500 mb-1">Description</div>
        <div class="text-gray-900">{{ bill.description }}</div>
      </div>

      <!-- Bill Image -->
      <div v-if="bill.image_path">
        <div class="text-sm font-medium text-gray-500 mb-2">Bill Image</div>
        <img :src="`/storage/${bill.image_path}`" :alt="bill.bill_number || 'Bill image'" class="max-w-md h-auto rounded-md border border-gray-300" />
      </div>
    </div>

    <!-- Bill Items (only for inventory bills) -->
    <div v-if="bill.bill_type === 'inventory' && bill.items && bill.items.length > 0" class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Bill Items</h2>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in bill.items" :key="item.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">{{ item.description }}</td>
              <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $formatAmount(item.unit_price) }}</td>
              <td class="px-6 py-4 text-sm text-center text-gray-900">{{ item.unit_measurement || '—' }}</td>
              <td class="px-6 py-4 text-sm text-center text-gray-900">{{ item.quantity }}</td>
              <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ $formatAmount(item.total) }}</td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Total Amount</td>
              <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">{{ $formatAmount(bill.amount) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Payment History -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Payment History</h2>
          <Link
            v-if="balanceDue > 0"
            :href="`/bills/${bill.id}/pay`"
            class="btn-kingbaker"
          >
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
            Record Payment
          </Link>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="payment in bill.payments" :key="payment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $formatDate(payment.payment_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ payment.reference_number || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ payment.payment_method || 'Cash' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ payment.bank_account?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                {{ $formatAmount(payment.amount) }} 
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 text-center">
                {{ payment.notes || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                <div class="flex items-center justify-center space-x-2">
                  <button
                    @click="openEditModal(payment)"
                    class="text-brand-600 hover:text-brand-900"
                    title="Edit Payment"
                  >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                  </button>
                  <button
                    @click="openDeleteModal(payment)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete Payment"
                  >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 012 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v3a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!bill.payments?.length">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                No payments recorded for this bill.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete Payment Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeDeleteModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Delete Payment</h3>
            <button @click="closeDeleteModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div v-if="deletingPayment" class="space-y-4">
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-red-800">Warning</h3>
                  <div class="mt-2 text-sm text-red-700">
                    <p>Are you sure you want to delete this payment? This action will:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                      <li>Remove the payment from this bill</li>
                      <li>Create a reverse transaction to credit the account</li>
                      <li>Update the bill's payment status</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Details</label>
              <div class="text-sm text-gray-600 space-y-1">
                <p><strong>Amount:</strong> {{ $formatAmount(deletingPayment.amount) }}</p>
                <p><strong>Date:</strong> {{ $formatDate(deletingPayment.payment_date) }}</p>
                <p><strong>Method:</strong> {{ deletingPayment.payment_method || 'Cash' }}</p>
                <p v-if="deletingPayment.bank_account"><strong>Account:</strong> {{ deletingPayment.bank_account.name }}</p>
              </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4">
              <button
                @click="closeDeleteModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                @click="deletePayment"
                :disabled="deleteForm.processing"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50"
              >
                <span v-if="deleteForm.processing">Deleting...</span>
                <span v-else>Delete Payment</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Payment Account Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeEditModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Edit Payment</h3>
            <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div v-if="editingPayment" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Details</label>
              <div class="text-sm text-gray-600">
                <p>Amount: {{ $formatAmount(editingPayment.amount) }}</p>
                <p>Date: {{ $formatDate(editingPayment.payment_date) }}</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
              <select
                v-model="editForm.payment_method"
                @change="handlePaymentMethodChange"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="editForm.errors.payment_method ? 'border-red-300' : 'border-gray-300'"
              >
                <option value="">Select payment method</option>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cheque">Cheque</option>
                <option value="mobile_money">Mobile Money</option>
              </select>
              <div v-if="editForm.errors.payment_method" class="mt-1 text-sm text-red-600">
                {{ editForm.errors.payment_method }}
              </div>
            </div>

            <div v-if="editForm.payment_method && editForm.payment_method !== 'cash'">
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account *</label>
              <select
                v-model="editForm.bank_account_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="editForm.errors.bank_account_id ? 'border-red-300' : 'border-gray-300'"
              >
                <option value="">Select Bank Account</option>
                <option v-for="account in bankAccounts" :key="account.value" :value="account.value">
                  {{ account.label }}
                </option>
              </select>
              <div v-if="editForm.errors.bank_account_id" class="mt-1 text-sm text-red-600">
                {{ editForm.errors.bank_account_id }}
              </div>
              <p class="mt-1 text-sm text-gray-500">Select the bank account for this payment</p>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4">
              <button
                @click="closeEditModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                @click="updatePaymentAccount"
                :disabled="editForm.processing"
                class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-md hover:bg-brand-700 disabled:opacity-50"
              >
                <span v-if="editForm.processing">Updating...</span>
                <span v-else>Update Payment</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { Head, Link, useForm } from '@inertiajs/vue3'
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
    bill: {
      type: Object,
      required: true
    },
    bankAccounts: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      showEditModal: false,
      editingPayment: null,
      editForm: useForm({
        payment_method: null,
        bank_account_id: null,
      }),
      showDeleteModal: false,
      deletingPayment: null,
      deleteForm: useForm({}),
    }
  },
  computed: {
    balanceDue() {
      return parseFloat(this.bill.amount) - parseFloat(this.bill.amount_paid || 0)
    }
  },
  methods: {
    openEditModal(payment) {
      this.editingPayment = payment
      this.editForm.payment_method = payment.payment_method || 'cash'
      this.editForm.bank_account_id = payment.bank_account_id
      this.showEditModal = true
    },
    closeEditModal() {
      this.showEditModal = false
      this.editingPayment = null
      this.editForm.reset()
      this.editForm.clearErrors()
    },
    handlePaymentMethodChange() {
      // Clear bank account if switching to cash
      if (this.editForm.payment_method === 'cash') {
        this.editForm.bank_account_id = null
      }
    },
    updatePaymentAccount() {
      if (!this.editingPayment) return

      this.editForm.put(`/bill-payments/${this.editingPayment.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          this.closeEditModal()
        }
      })
    },
    openDeleteModal(payment) {
      this.deletingPayment = payment
      this.showDeleteModal = true
    },
    closeDeleteModal() {
      this.showDeleteModal = false
      this.deletingPayment = null
      this.deleteForm.reset()
      this.deleteForm.clearErrors()
    },
    deletePayment() {
      if (!this.deletingPayment) return

      this.deleteForm.delete(`/bill-payments/${this.deletingPayment.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          this.closeDeleteModal()
        }
      })
    }
  }
}
</script>