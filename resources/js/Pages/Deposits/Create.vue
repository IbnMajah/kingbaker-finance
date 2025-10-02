<template>
  <div>
    <Head title="Create Deposit" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/deposits">Deposits</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create
      </h1>
      <p class="text-gray-600">Record a new deposit transaction</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Form Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">
          {{ form.deposit_type === 'daily_sales_deposit' ? 'Daily Sales Entry' : 'Deposit Information' }}
        </h2>
                  <p v-if="form.deposit_type === 'daily_sales_deposit'" class="text-sm text-gray-600 mt-1">
          Record daily sales to credit the selected bank account
        </p>
      </div>

      <form @submit.prevent="store">
        <div class="p-6 space-y-6">
          <!-- Transaction Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account *</label>
              <select
                v-model="form.bank_account_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bank_account_id ? 'border-red-500' : ''"
              >
                <option value="">Select Bank Account</option>
                <option v-for="bankAccount in bankAccounts" :key="bankAccount.value" :value="bankAccount.value">{{ bankAccount.label }}</option>
              </select>
              <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deposit Date *</label>
              <input
                v-model="form.deposit_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.deposit_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.deposit_date" class="mt-1 text-sm text-red-600">{{ form.errors.deposit_date }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deposit Type *</label>
              <select
                v-model="form.deposit_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.deposit_type ? 'border-red-500' : ''"
              >
                <option value="">Select Deposit Type</option>
                <option v-for="depositType in depositTypes" :key="depositType.value" :value="depositType.value">{{ depositType.label }}</option>
              </select>
              <div v-if="form.errors.deposit_type" class="mt-1 text-sm text-red-600">{{ form.errors.deposit_type }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                <input
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0.01"
                  class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.amount ? 'border-red-500' : ''"
                  :readonly="form.deposit_type === 'daily_sales_deposit'"
                  placeholder="0.00"
                />
              </div>
              <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
              <div v-if="form.deposit_type === 'daily_sales_deposit'" class="mt-1 text-sm text-blue-600">
                Amount will be calculated from selected sales
              </div>
            </div>
          </div>

          <!-- Branch Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ form.deposit_type === 'daily_sales_deposit' ? 'Branch *' : 'Branch' }}
              </label>
              <select
                v-model="form.branch_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.branch_id ? 'border-red-500' : ''"
              >
                <option value="">Select Branch</option>
                <option v-for="branch in branches" :key="branch.value" :value="branch.value">{{ branch.label }}</option>
              </select>
              <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</div>
            </div>
          </div>

          <!-- Reference and Documentation -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number *</label>
              <input
                v-model="form.reference_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.reference_number ? 'border-red-500' : ''"
                placeholder="Enter unique reference number"
                required
              />
              <div v-if="form.errors.reference_number" class="mt-1 text-sm text-red-600">{{ form.errors.reference_number }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Depositor Name *</label>
              <input
                v-model="form.depositor_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.depositor_name ? 'border-red-500' : ''"
                placeholder="Enter depositor name"
                required
              />
              <div v-if="form.errors.depositor_name" class="mt-1 text-sm text-red-600">{{ form.errors.depositor_name }}</div>
            </div>
          </div>

          <!-- Sales Selection (when payment mode is sales) -->
          <div v-if="form.deposit_type === 'daily_sales_deposit'" class="space-y-4">
            <div class="border-t pt-4">
              <h3 class="text-md font-medium text-gray-900 mb-3">
                Select Sales to Deposit
                <span class="text-sm text-gray-500">({{ filteredAvailableSales.length }} sales available)</span>
              </h3>

              <!-- Sales List -->
              <div class="max-h-60 overflow-y-auto border rounded-lg">
                <table class="w-full">
                  <thead class="bg-gray-50 sticky top-0">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">
                        <input
                          type="checkbox"
                          @change="toggleSelectAll"
                          :checked="areAllSelected"
                          class="rounded border-gray-300"
                        />
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Amount</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cashier</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Branch</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Shift</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr
                      v-for="sale in filteredAvailableSales"
                      :key="sale.id"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-4 py-2">
                                                 <input
                           type="checkbox"
                           :value="parseInt(sale.id)"
                           v-model="form.selected_sales"
                           class="rounded border-gray-300"
                           @change="calculateSelectedTotal"
                         />
                      </td>
                      <td class="px-4 py-2 text-sm">{{ $formatDate(sale.sales_date) }}</td>
                      <td class="px-4 py-2 text-sm font-medium">{{ $formatAmount(sale.amount) }}</td>
                      <td class="px-4 py-2 text-sm">{{ sale.cashier || 'N/A' }}</td>
                      <td class="px-4 py-2 text-sm">{{ sale.branch || 'N/A' }}</td>
                      <td class="px-4 py-2 text-sm">{{ sale.shift || 'N/A' }}</td>
                    </tr>
                                         <tr v-if="!filteredAvailableSales.length">
                       <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                         No sales available for deposit
                       </td>
                     </tr>
                  </tbody>
                </table>
              </div>

              <!-- Selected Sales Summary -->
              <div v-if="selectedSalesData.length" class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-900 mb-2">Selected Sales Summary</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="text-blue-700">Number of Sales:</span>
                    <span class="font-medium ml-2">{{ selectedSalesData.length }}</span>
                  </div>
                  <div>
                    <span class="text-blue-700">Total Amount:</span>
                    <span class="font-medium ml-2 text-lg">{{ $formatAmount(selectedTotal) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.description ? 'border-red-500' : ''"
                              :placeholder="form.deposit_type === 'daily_sales_deposit' ? 'Optional: Additional notes about the sales deposit' : 'Optional: Add description for this deposit'"
            ></textarea>
            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
          </div>

          <!-- Image Upload -->
          <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ form.deposit_type === 'daily_sales_deposit' ? 'Sales Receipt/Image' : 'Receipt/Deposit Slip' }}
              </label>
            <input
              @input="form.image = $event.target.files[0]"
              type="file"
              accept="image/*"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.image ? 'border-red-500' : ''"
            />
            <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</div>
                          <p class="mt-1 text-sm text-gray-500">
                {{ form.deposit_type === 'daily_sales_deposit' ? 'Optional: Upload a photo of the sales summary or receipt' : 'Optional: Upload deposit slip or receipt' }}
              </p>
          </div>

          <!-- Multiple Attachments Upload -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Supporting Documents (Optional)</label>
            <input
              @change="handleAttachmentsUpload"
              type="file"
              accept="image/*,.pdf"
              multiple
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.attachments ? 'border-red-500' : ''"
            />
            <div v-if="form.errors.attachments" class="mt-1 text-sm text-red-600">{{ form.errors.attachments }}</div>
            <p class="text-xs text-gray-500 mt-1">Upload up to 10 supporting documents (images or PDFs, max 5MB each)</p>

            <!-- Show selected files -->
            <div v-if="form.attachments && form.attachments.length" class="mt-3">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Files:</h4>
              <div class="space-y-2">
                <div v-for="(file, index) in form.attachments" :key="index" class="flex items-center justify-between bg-gray-50 p-2 rounded">
                  <div class="flex items-center space-x-2">
                    <svg v-if="file.type?.includes('pdf')" class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-gray-900">{{ file.name }}</span>
                    <span class="text-xs text-gray-500">({{ formatFileSize(file.size) }})</span>
                  </div>
                  <button @click="removeAttachment(index)" type="button" class="text-red-600 hover:text-red-800">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/deposits" class="text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-kingbaker flex items-center"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Processing...' : (form.deposit_type === 'daily_sales_deposit' ? 'Record Sales' : 'Create Deposit') }}
          </button>
        </div>
      </form>
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
    bankAccounts: Array,
    branches: Array,

    depositTypes: Array,
    availableSales: Array,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        bank_account_id: '',
        deposit_date: new Date().toISOString().split('T')[0],
        deposit_type: '',
        amount: '',
        branch_id: '',

        reference_number: '',
        depositor_name: '',
        description: '',
        image: null,
        attachments: [],
        selected_sales: [], // New for sales mode
      }),
    }
  },
  computed: {
    filteredAvailableSales() {
      // Filter out sales that are already deposited (not available for new deposits)
      return this.availableSales?.filter(sale => !sale.is_deposited) || [];
    },
    selectedSalesData() {
      return this.filteredAvailableSales.filter(sale => this.form.selected_sales.includes(parseInt(sale.id)));
    },
    selectedTotal() {
      return this.selectedSalesData.reduce((sum, sale) => sum + parseFloat(sale.amount), 0);
    },
    areAllSelected() {
      return this.filteredAvailableSales.length > 0 && this.form.selected_sales.length === this.filteredAvailableSales.length;
    }
  },
  watch: {
    'form.deposit_type'(newType) {
      if (newType === 'daily_sales_deposit') {
        // Clear any manually entered amount since it will be calculated from selected sales
        this.form.amount = '';
        this.form.selected_sales = [];
      } else {
        // Clear sales selection when switching away from daily sales deposit mode
        this.form.selected_sales = [];
      }
    },
    selectedTotal(newTotal) {
      if (this.form.deposit_type === 'daily_sales_deposit') {
        this.form.amount = newTotal.toString();
      }
    }
  },
  methods: {
    toggleSelectAll() {
      if (this.areAllSelected) {
        this.form.selected_sales = [];
      } else {
        this.form.selected_sales = this.filteredAvailableSales.map(sale => parseInt(sale.id));
      }
      this.calculateSelectedTotal();
    },
    calculateSelectedTotal() {
      // The computed property and watcher will handle the calculation
      // This method is just for triggering any necessary updates
    },
    store() {
      this.form.post('/deposits')
    },
    handleAttachmentsUpload(event) {
      const files = Array.from(event.target.files);

      // Validate file count
      if (this.form.attachments.length + files.length > 10) {
        alert('You can only upload up to 10 files total.');
        return;
      }

      // Validate each file
      for (let file of files) {
        // Check file size (5MB = 5120KB)
        if (file.size > 5120 * 1024) {
          alert(`File "${file.name}" is too large. Maximum size is 5MB.`);
          return;
        }

        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
          alert(`File "${file.name}" is not supported. Only images and PDFs are allowed.`);
          return;
        }
      }

      // Add files to form
      this.form.attachments = [...this.form.attachments, ...files];
    },
    removeAttachment(index) {
      this.form.attachments.splice(index, 1);
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