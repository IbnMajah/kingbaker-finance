<template>
  <div>
    <Head title="Expense Claims" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Expense Claims</h1>
      <p class="text-gray-600">Manage expense claims and reimbursements</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Claims</p>
            <p class="text-2xl font-semibold text-gray-900">{{ totalClaims }}</p>
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
            <p class="text-sm font-medium text-gray-600">Approved Claims</p>
            <p class="text-2xl font-semibold text-gray-900">{{ approvedClaims }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Pending Claims</p>
            <p class="text-2xl font-semibold text-gray-900">{{ pendingClaims }}</p>
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

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Filter Claims</h2>
        <Link class="btn-kingbaker" href="/expense-claims/create">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
          </svg>
          <span>Create Claim</span>
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="form.search"
            type="text"
            placeholder="Reference, description..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select v-model="form.category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Categories</option>
            <option v-for="(label, value) in categories" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Expense Type</label>
          <select v-model="form.expenseType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500">
            <option value="">All Types</option>
            <option v-for="(label, value) in expenseTypes" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>
      </div>

      <div class="flex items-center mt-4 space-x-4">
        <button @click="reset" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
          Clear Filters
        </button>
        <div class="text-sm text-gray-500">
          Showing {{ claims.data?.length || 0 }} of {{ claims.total || 0 }} claims
        </div>
      </div>
    </div>

    <!-- Claims Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Claims</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payee</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="claim in claims?.data || []" :key="claim.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="flex items-center">
                  <div class="ml-4">
                    <div class="font-medium">{{ claim.claim_number }}</div>
                    <div v-if="claim.description" class="text-sm text-gray-500 truncate max-w-xs">
                      {{ claim.description }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="font-medium">{{ claim.title || 'No title' }}</div>
                <div class="flex flex-wrap gap-2 mt-1">
                  <span v-if="claim.category" class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                    {{ categories[claim.category] || claim.category.replace('_', ' ') }}
                  </span>
                  <span v-if="claim.expense_type" class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded">
                    {{ expenseTypes[claim.expense_type] || claim.expense_type.replace('_', ' ') }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ claim.payee }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ $formatDate(claim.claim_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                {{ $formatAmount(claim.total) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="{
                    'bg-gray-100 text-gray-800': claim.status === 'draft',
                    'bg-yellow-100 text-yellow-800': claim.status === 'submitted',
                    'bg-green-100 text-green-800': claim.status === 'approved',
                    'bg-red-100 text-red-800': claim.status === 'rejected',
                    'bg-blue-100 text-blue-800': claim.status === 'paid'
                  }"
                >
                  {{ claim.status.charAt(0).toUpperCase() + claim.status.slice(1) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="flex items-center justify-center space-x-2">
                  <Link class="flex items-center space-x-1 text-brand-600 hover:text-brand-900 text-sm font-medium" :href="`/expense-claims/${claim.id}`">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                      <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span>View</span>
                  </Link>
                  <Link
                    v-if="claim.status === 'draft'"
                    class="flex items-center space-x-1 text-gray-600 hover:text-gray-900 text-sm font-medium"
                    :href="`/expense-claims/${claim.id}/edit`"
                  >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                    <span>Edit</span>
                  </Link>
                  <button
                    v-if="claim.status === 'draft'"
                    @click="submitClaim(claim)"
                    class="flex items-center space-x-1 text-blue-600 hover:text-blue-900 text-sm font-medium"
                  >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Submit</span>
                  </button>
                  <button
                    v-if="claim.status === 'submitted'"
                    @click="approveClaim(claim)"
                    class="flex items-center space-x-1 text-green-600 hover:text-green-900 text-sm font-medium"
                  >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Approve</span>
                  </button>
                  <button
                    v-if="claim.status === 'submitted'"
                    @click="openRejectModal(claim)"
                    class="flex items-center space-x-1 text-red-600 hover:text-red-900 text-sm font-medium"
                  >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Reject</span>
                  </button>

                </div>
              </td>
            </tr>
            <tr v-if="!claims?.data?.length">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                No expense claims found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="claims?.data?.length" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ claims.from || 0 }} to {{ claims.to || 0 }} of {{ claims.total || 0 }} results
          </div>
          <div class="flex space-x-2">
            <Link
              v-if="claims.prev_page_url"
              :href="claims.prev_page_url"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              preserve-state
            >
              Previous
            </Link>
            <Link
              v-if="claims.next_page_url"
              :href="claims.next_page_url"
              class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              preserve-state
            >
              Next
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Expense Claim</h3>
          <form @submit.prevent="rejectClaim">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection</label>
              <textarea
                v-model="rejectForm.rejection_reason"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                required
              ></textarea>
            </div>
            <div class="flex items-center justify-end space-x-2">
              <button
                type="button"
                @click="closeRejectModal"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="rejectForm.processing"
                class="btn-kingbaker"
              >
                {{ rejectForm.processing ? 'Rejecting...' : 'Reject Claim' }}
              </button>
            </div>
          </form>
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
    claims: {
      type: Object,
      default: () => ({ data: [] })
    },
    totalClaims: {
      type: Number,
      default: 0
    },
    pendingClaims: {
      type: Number,
      default: 0
    },
    approvedClaims: {
      type: Number,
      default: 0
    },
    totalAmount: {
      type: Number,
      default: 0
    },
    categories: {
      type: Object,
      default: () => ({})
    },
    expenseTypes: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        status: this.filters.status,
        category: this.filters.category,
        expenseType: this.filters.expense_type,
      },
      showRejectModal: false,
      selectedClaim: null,
      rejectForm: this.$inertia.form({
        rejection_reason: '',
      }),
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/expense-claims', pickBy(this.form), {
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
    submitClaim(claim) {
      this.$inertia.post(`/expense-claims/${claim.id}/submit`)
    },
    approveClaim(claim) {
      this.$inertia.post(`/expense-claims/${claim.id}/approve`)
    },
    openRejectModal(claim) {
      this.selectedClaim = claim
      this.showRejectModal = true
      this.rejectForm.reset()
    },
    closeRejectModal() {
      this.showRejectModal = false
      this.selectedClaim = null
      this.rejectForm.reset()
    },
    rejectClaim() {
      this.rejectForm.post(`/expense-claims/${this.selectedClaim.id}/reject`, {
        onSuccess: () => {
          this.closeRejectModal()
        }
      })
    }
  },
}
</script>