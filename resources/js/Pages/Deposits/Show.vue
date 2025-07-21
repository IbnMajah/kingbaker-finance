<template>
  <div>
    <Head :title="`Deposit ${deposit.reference_number}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/deposits">Deposits</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ deposit.reference_number }}
      </h1>
      <p class="text-gray-600">Deposit Details & Information</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Amount</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatAmount(deposit.amount) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Deposit Type</p>
            <p class="text-2xl font-semibold text-gray-900">{{ deposit.deposit_type?.replace('_', ' ').toUpperCase() }}</p>
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
            <p class="text-sm font-medium text-gray-600">Deposit Date</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $formatDate(deposit.deposit_date) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Depositor</p>
            <p class="text-2xl font-semibold text-gray-900">{{ deposit.depositor_name || 'N/A' }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Deposit Details -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Deposit Information</h3>
          </div>
          <div class="p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">Reference Number</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ deposit.reference_number }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Bank Account</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ deposit.bank_account?.name || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Branch</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ deposit.branch?.name || 'N/A' }}</dd>
              </div>
              <div v-if="deposit.description" class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ deposit.description }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Associated Sales -->
        <div v-if="deposit.sales?.length" class="bg-white rounded-lg shadow overflow-hidden mt-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Associated Sales ({{ deposit.sales.length }})</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="sale in deposit.sales" :key="sale.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $formatDate(sale.sales_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $formatAmount(sale.amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ sale.cashier || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ sale.branch?.name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ sale.shift?.name || 'N/A' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Sales Total -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-gray-700">Total from Sales:</span>
              <span class="text-lg font-semibold text-gray-900">
                {{ $formatAmount(deposit.sales.reduce((sum, sale) => sum + parseFloat(sale.amount), 0)) }}
              </span>
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
              :href="`/deposits/${deposit.id}/edit`"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
            >
              Edit Deposit
            </Link>

            <button
              @click="deleteDeposit"
              :disabled="form.processing"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Delete Deposit
            </button>
          </div>
        </div>

                <!-- Deposit Image -->
        <div v-if="deposit.image_path" class="bg-white rounded-lg shadow p-6 mt-6">
          <h3 class="text-lg font-semibold mb-4">Receipt Image</h3>
          <div class="cursor-pointer" @click="showImage(deposit.image_path)">
            <img
              :src="`/storage/${deposit.image_path}`"
              :alt="`Receipt for ${deposit.reference_number}`"
              class="max-w-full h-auto rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
            />
          </div>
          <p class="text-xs text-gray-500 mt-2">Click to view full size</p>
        </div>

        <!-- Supporting Documents -->
        <div v-if="deposit.attachments && deposit.attachments.length" class="bg-white rounded-lg shadow p-6 mt-6">
          <h3 class="text-lg font-semibold mb-4">Supporting Documents ({{ deposit.attachments.length }})</h3>
          <div class="grid grid-cols-1 gap-3">
            <div v-for="(attachment, index) in deposit.attachments" :key="index" class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
              <div class="flex items-center space-x-3">
                <div v-if="attachment.type?.includes('pdf')" class="flex-shrink-0">
                  <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div v-else class="flex-shrink-0">
                  <img
                    :src="`/storage/${attachment.path}`"
                    :alt="attachment.filename"
                    class="w-8 h-8 object-cover rounded cursor-pointer"
                    @click="showImage(attachment.path)"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.filename }}</p>
                  <p class="text-xs text-gray-500">{{ formatFileSize(attachment.size) }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="downloadAttachment(attachment)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Download
                </button>
                <button
                  v-if="!attachment.type?.includes('pdf')"
                  @click="showImage(attachment.path)"
                  class="text-green-600 hover:text-green-800 text-sm font-medium"
                >
                  View
                </button>
                <a
                  v-else
                  :href="`/storage/${attachment.path}`"
                  target="_blank"
                  class="text-green-600 hover:text-green-800 text-sm font-medium"
                >
                  View PDF
                </a>
              </div>
            </div>
          </div>
        </div>

                        <!-- Image Modal -->
        <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="closeImageModal">
          <div class="relative w-1/2 h-4/5 flex items-center justify-center">
            <img
              :src="`/storage/${selectedImage}`"
              :alt="`Receipt for ${deposit.reference_number}`"
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
  layout: Layout,
  mixins: [formatterMixin],
  props: {
    deposit: Object,
  },
  data() {
    return {
      form: this.$inertia.form({}),
      showImageModal: false,
      selectedImage: null,
    }
  },
  methods: {
    deleteDeposit() {
      if (confirm('Are you sure you want to delete this deposit?')) {
        this.form.delete(`/deposits/${this.deposit.id}`)
      }
    },
    showImage(imagePath) {
      this.selectedImage = imagePath
      this.showImageModal = true
    },
    closeImageModal() {
      this.showImageModal = false
      this.selectedImage = null
    },
    downloadAttachment(attachment) {
      const link = document.createElement('a');
      link.href = `/storage/${attachment.path}`;
      link.download = attachment.filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
  },
}
</script>