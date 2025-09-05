<template>
  <div>
    <Head :title="`Expense Claim ${expenseClaim.claim_number}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/expense-claims">Expense Claims</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ expenseClaim.claim_number }}
      </h1>
      <p class="text-gray-600">Expense Claim Details & Information</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
            <p class="text-sm font-medium text-gray-600">Total Amount</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatAmount(expenseClaim.total) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm-1-5a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Status</p>
            <p class="text-2xl font-semibold" :class="getStatusTextClass(expenseClaim.status)">
              {{ getStatusLabel(expenseClaim.status) }}
            </p>
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
            <p class="text-sm font-medium text-gray-600">Claim Date</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatDate(expenseClaim.claim_date) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Items Count</p>
            <p class="text-2xl font-semibold text-gray-900">{{ expenseClaim.items?.length || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Claim Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Claim Details</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <div class="text-sm font-medium text-gray-500">Claim Number</div>
          <div class="text-gray-900 font-mono">{{ expenseClaim.claim_number }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Title</div>
          <div class="text-gray-900">{{ expenseClaim.title || 'No title' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Bank Account</div>
          <div class="text-gray-900">{{ expenseClaim.bank_account?.name ?? 'Not specified' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Payee</div>
          <div class="text-gray-900">{{ expenseClaim.payee }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Branch</div>
          <div class="text-gray-900">{{ expenseClaim.branch?.name ?? 'Not specified' }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-gray-500">Created By</div>
          <div class="text-gray-900">{{ expenseClaim.creator ? `${expenseClaim.creator.first_name} ${expenseClaim.creator.last_name}` : 'Unknown' }}</div>
        </div>
        <div v-if="expenseClaim.approver">
          <div class="text-sm font-medium text-gray-500">Approved By</div>
          <div class="text-gray-900">{{ expenseClaim.approver.name }}</div>
        </div>

      </div>
    </div>

    <!-- Description -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-lg font-semibold mb-4">Description</h2>
      <div class="bg-gray-50 p-4 rounded-md">
        <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">
          {{ expenseClaim.description || 'No description provided.' }}
        </p>
      </div>
    </div>

    <!-- Expense Items -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Expense Items</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in expenseClaim.items" :key="item.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">
                <div class="font-medium">{{ item.description }}</div>
                <div v-if="item.notes" class="text-xs text-gray-500 truncate max-w-xs">{{ item.notes }}</div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <span v-if="item.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  {{ item.category.replace('_', ' ') }}
                </span>
                <span v-else class="text-gray-400">No category</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                <button
                  v-if="item.receipt_image_path"
                  @click="showImage(item.receipt_image_path)"
                  class="text-brand-600 hover:text-brand-900"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                  </svg>
                </button>
                <span v-else class="text-gray-400">No receipt</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                {{ item.quantity || 1 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                {{ $formatAmount(item.unit_price) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                {{ item.unit_measurement || '—' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                {{ $formatAmount((item.unit_price || 0) * (item.quantity || 1)) }}
              </td>
            </tr>
            <tr v-if="!expenseClaim.items?.length">
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                No expense items found.
              </td>
            </tr>
            <tr class="bg-gray-50">
              <td colspan="6" class="px-6 py-4 text-right font-semibold text-gray-900">
                Total:
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900">
                {{ $formatAmount(expenseClaim.total) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold mb-4">Actions</h2>
      <div class="flex flex-wrap gap-3">
        <Link
          v-if="expenseClaim.status === 'active'"
          :href="'/expense-claims/' + expenseClaim.id + '/edit'"
          class="btn-kingbaker flex items-center"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit Claim
        </Link>



        <button
          v-if="expenseClaim.status === 'submitted' && canApprove"
          @click="approveClaim"
          :disabled="processing"
          class="btn-kingbaker flex items-center bg-green-600 hover:bg-green-700"
        >
          <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg>
          <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ processing ? 'Approving...' : 'Approve' }}
        </button>

        <button
          v-if="expenseClaim.status === 'submitted' && canApprove"
          @click="rejectClaim"
          :disabled="processing"
          class="btn-kingbaker flex items-center bg-red-600 hover:bg-red-700"
        >
          <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg>
          <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          {{ processing ? 'Rejecting...' : 'Reject' }}
        </button>


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
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { formatterMixin } from '@/Utils/formatters'


export default {
  components: {
    Head,
    Link,
  },
  mixins: [formatterMixin],
  layout: Layout,
  props: {
    expenseClaim: Object,
  },
  data() {
    return {
      showImageModal: false,
      selectedImage: null,
      processing: false,
    }
  },
  computed: {
    canApprove() {
      return this.expenseClaim.status === 'submitted' && this.expenseClaim.approver_id === this.$page.props.user.id;
    },
  },
  methods: {
    getStatusTextClass(status) {
      const classes = {
        'active': 'text-gray-600',
        'submitted': 'text-yellow-600',
        'approved': 'text-green-600',
          'rejected': 'text-red-600',
        'paid': 'text-blue-600',
      };
      return classes[status] || 'text-gray-600';
    },
    getStatusLabel(status) {
      return status.charAt(0).toUpperCase() + status.slice(1);
      },
    submitClaim() {
      this.processing = true;
      this.$inertia.post('/expense-claims/' + this.expenseClaim.id + '/submit', {}, {
        onSuccess: () => {
          // Page will refresh with updated data
        },
        onError: () => {
          // Handle error
        },
        onFinish: () => {
          this.processing = false;
        }
      });
    },
    approveClaim() {
      this.processing = true;
      this.$inertia.post('/expense-claims/' + this.expenseClaim.id + '/approve', {}, {
        onSuccess: () => {
          // Page will refresh with updated data
        },
        onError: () => {
          // Handle error
        },
        onFinish: () => {
          this.processing = false;
        }
      });
    },
    rejectClaim() {
      const reason = prompt('Please provide a reason for rejection:');
      if (reason) {
        this.processing = true;
        this.$inertia.post('/expense-claims/' + this.expenseClaim.id + '/reject', {
          rejection_reason: reason
        }, {
          onSuccess: () => {
                // Page will refresh with updated data
          },
          onError: () => {
            // Handle error
          },
          onFinish: () => {
            this.processing = false;
          }
        });
      }
    },
    showImage(imagePath) {
      this.selectedImage = imagePath;
      this.showImageModal = true;
    },
    closeImageModal() {
      this.showImageModal = false;
      this.selectedImage = null;
    },
  },
}
</script>
