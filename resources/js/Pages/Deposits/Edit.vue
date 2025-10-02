<template>
  <div>
    <Head :title="form.reference_number" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/deposits">Deposits</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ form.reference_number }}
      </h1>
      <p class="text-gray-600">Edit deposit transaction details</p>
    </div>

    <!-- Deposit Details Card -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Amount</div>
          <div class="text-2xl font-bold text-green-600 mt-1">
            {{ $formatAmount(deposit.amount) }}
          </div>
        </div>
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Deposit Type</div>
          <div class="mt-1">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
              {{ deposit.deposit_type?.replace('_', ' ').toUpperCase() }}
            </span>
          </div>
        </div>
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Deposit Date</div>
          <div class="text-sm text-gray-900 mt-1">
            {{ $formatDate(deposit.deposit_date) }}
          </div>
        </div>
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Created</div>
          <div class="text-sm text-gray-900 mt-1">
            {{ $formatDate(deposit.created_at) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Edit Deposit Information</h2>
      </div>

      <form @submit.prevent="update">
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
                Amount will be calculated from associated sales
              </div>
            </div>
          </div>

          <!-- Branch Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
              <input
                v-model="form.reference_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.reference_number ? 'border-red-500' : ''"
                placeholder="Reference number"
              />
              <div v-if="form.errors.reference_number" class="mt-1 text-sm text-red-600">{{ form.errors.reference_number }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Receipt/Image</label>
              <input
                @input="form.image = $event.target.files[0]"
                type="file"
                accept="image/*"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.image ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</div>
              <div v-if="deposit.image_path" class="mt-2">
                <button type="button" @click="showCurrentImage" class="text-sm text-blue-600 hover:text-blue-800">
                  View current image
                </button>
              </div>
              <p class="mt-1 text-sm text-gray-500">Optional: Upload new receipt or proof of deposit</p>
            </div>

            <!-- Multiple Attachments Management -->
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Supporting Documents</label>

              <!-- Current Attachments -->
              <div v-if="currentAttachments.length" class="mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Current Documents:</h4>
                <div class="space-y-2">
                  <div v-for="(attachment, index) in currentAttachments" :key="index" class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                    <div class="flex items-center space-x-3">
                      <svg v-if="attachment.type?.includes('pdf')" class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                      </svg>
                      <img v-else :src="`/storage/${attachment.path}`" :alt="attachment.filename" class="w-5 h-5 object-cover rounded" />
                      <div>
                        <span class="text-sm font-medium text-gray-900">{{ attachment.filename }}</span>
                        <p class="text-xs text-gray-500">{{ formatFileSize(attachment.size) }}</p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <button @click="viewAttachment(attachment)" type="button" class="text-blue-600 hover:text-blue-800 text-sm">
                        View
                      </button>
                      <button @click="removeCurrentAttachment(attachment.path)" type="button" class="text-red-600 hover:text-red-800 text-sm">
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Upload New Attachments -->
              <input
                @change="handleAttachmentsUpload"
                type="file"
                accept="image/*,.pdf"
                multiple
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.attachments ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.attachments" class="mt-1 text-sm text-red-600">{{ form.errors.attachments }}</div>
              <p class="text-xs text-gray-500 mt-1">Upload additional supporting documents (images or PDFs, max 5MB each, up to 10 files total)</p>

              <!-- Show selected new files -->
              <div v-if="newAttachments.length" class="mt-3">
                <h4 class="text-sm font-medium text-gray-700 mb-2">New Files to Upload:</h4>
                <div class="space-y-2">
                  <div v-for="(file, index) in newAttachments" :key="index" class="flex items-center justify-between bg-blue-50 p-2 rounded">
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
                    <button @click="removeNewAttachment(index)" type="button" class="text-red-600 hover:text-red-800">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Depositor Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Depositor Name *</label>
            <input
              v-model="form.depositor_name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.depositor_name ? 'border-red-500' : ''"
              placeholder="Name of person making the deposit"
            />
            <div v-if="form.errors.depositor_name" class="mt-1 text-sm text-red-600">{{ form.errors.depositor_name }}</div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.description ? 'border-red-500' : ''"
              placeholder="Optional: Add notes about this deposit"
            ></textarea>
            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
          </div>

          <!-- Associated Sales Management -->
          <div v-if="form.deposit_type === 'daily_sales_deposit'">
            <div class="border-t pt-6">
              <h3 class="text-md font-medium text-gray-900 mb-4">Associated Sales</h3>

              <!-- Current Sales Table -->
              <div v-if="filteredCurrentSales.length" class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Current Sales ({{ filteredCurrentSales.length }})</h4>
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cashier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="sale in filteredCurrentSales" :key="sale.id">
                        <td class="px-4 py-2 text-sm">{{ $formatDate(sale.sales_date) }}</td>
                        <td class="px-4 py-2 text-sm font-medium">{{ $formatAmount(sale.amount) }}</td>
                        <td class="px-4 py-2 text-sm">{{ sale.cashier || 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">{{ sale.branch || 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">{{ sale.shift || 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">
                          <button
                            @click="removeSale(sale.id)"
                            type="button"
                            class="text-red-600 hover:text-red-800 text-xs font-medium"
                          >
                            Remove
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                      <span class="text-sm font-medium text-gray-700">Current Sales Total:</span>
                      <span class="text-lg font-semibold text-gray-900">
                        {{ $formatAmount(currentSalesTotal) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add Sales Section -->
              <div v-if="filteredAvailableSales.length">
                <h4 class="text-sm font-medium text-gray-700 mb-2">
                  Add Sales to Deposit ({{ filteredAvailableSales.length }} available)
                </h4>
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                  <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        :checked="areAllAvailableSelected"
                        @change="toggleSelectAllAvailable"
                        class="rounded border-gray-300"
                      />
                      <span class="ml-2 text-sm font-medium text-gray-700">Select All Available</span>
                    </label>
                  </div>
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Select</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cashier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="sale in filteredAvailableSales" :key="sale.id">
                        <td class="px-4 py-2">
                          <input
                            type="checkbox"
                            :value="parseInt(sale.id)"
                            v-model="salesToAdd"
                            class="rounded border-gray-300"
                          />
                        </td>
                        <td class="px-4 py-2 text-sm">{{ $formatDate(sale.sales_date) }}</td>
                        <td class="px-4 py-2 text-sm font-medium">{{ $formatAmount(sale.amount) }}</td>
                        <td class="px-4 py-2 text-sm">{{ sale.cashier || 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">{{ sale.branch || 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">{{ sale.shift || 'N/A' }}</td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-if="salesToAdd.length" class="px-4 py-3 bg-blue-50 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                      <span class="text-sm font-medium text-blue-700">
                        Selected Sales ({{ salesToAdd.length }}):
                      </span>
                      <span class="text-lg font-semibold text-blue-900">
                        {{ $formatAmount(selectedSalesTotal) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else-if="!filteredCurrentSales.length" class="text-sm text-gray-500">
                No sales available to add to this deposit.
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link href="/deposits" class="text-gray-600 hover:text-gray-800">
              <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
              </svg>
              Back to Deposits
            </Link>
            <button
              v-if="!deposit.deleted_at"
              @click="destroy"
              type="button"
              class="text-red-600 hover:text-red-800 flex items-center"
            >
              <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
              Delete Deposit
            </button>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="btn-kingbaker flex items-center"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Updating...' : 'Update Deposit' }}
          </button>
        </div>
      </form>
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
    deposit: Object,
    currentSales: Array,
    availableSales: Array,
    bankAccounts: Array,
    branches: Array,

    depositTypes: Array,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        bank_account_id: this.deposit.bank_account_id,
        deposit_date: this.deposit.deposit_date ? this.deposit.deposit_date.split('T')[0] : '',
        deposit_type: this.deposit.deposit_type,
        amount: this.deposit.amount,
        branch_id: this.deposit.branch_id,

        reference_number: this.deposit.reference_number,
        depositor_name: this.deposit.depositor_name,
        description: this.deposit.description,
        image: null,
      }),
      showImageModal: false,
      selectedImage: null,
      salesToAdd: [],
      salesToRemove: [],
      currentAttachments: this.deposit.attachments || [],
      newAttachments: [],
      removeAttachments: [],
    }
  },
  computed: {
    // Filter current sales based on what hasn't been removed
    filteredCurrentSales() {
      return this.currentSales?.filter(sale => !this.salesToRemove.includes(sale.id)) || [];
    },
    // Filter available sales: not deposited OR being added, but not currently associated unless being removed
    filteredAvailableSales() {
      return this.availableSales?.filter(sale => {
        // Include if not deposited at all
        if (!sale.is_deposited) return true;
        // Include if it's currently in this deposit and being removed
        if (sale.current_deposit_id === this.deposit.id && this.salesToRemove.includes(sale.id)) return true;
        // Exclude everything else (deposited in other deposits or currently in this deposit but not being removed)
        return false;
      }) || [];
    },
    currentSalesTotal() {
      return this.filteredCurrentSales.reduce((sum, sale) => sum + parseFloat(sale.amount), 0);
    },
    selectedSalesTotal() {
      const selectedSales = this.filteredAvailableSales.filter(sale => this.salesToAdd.includes(parseInt(sale.id)));
      return selectedSales.reduce((sum, sale) => sum + parseFloat(sale.amount), 0);
    },
    areAllAvailableSelected() {
      return this.filteredAvailableSales.length > 0 && this.salesToAdd.length === this.filteredAvailableSales.length;
    },
  },
  watch: {
    'form.deposit_type'(newType) {
      if (newType === 'daily_sales_deposit') {
        // When switching to sales mode, calculate amount from current sales
        this.form.amount = this.currentSalesTotal + this.selectedSalesTotal;
      }
    },
    selectedSalesTotal(newTotal) {
      if (this.form.deposit_type === 'daily_sales_deposit') {
        this.form.amount = this.currentSalesTotal + newTotal;
      }
    },
  },
  methods: {
    update() {
      // Include sales changes and attachment changes in the form data
      const formData = {
        ...this.form.data(),
        sales_to_add: this.salesToAdd,
        sales_to_remove: this.salesToRemove,
        attachments: this.newAttachments,
        remove_attachments: this.removeAttachments,
      };

      this.form.put(`/deposits/${this.deposit.id}`, {
        data: formData,
        onSuccess: () => {
          this.form.reset('image');
          this.salesToAdd = [];
          this.salesToRemove = [];
          this.newAttachments = [];
          this.removeAttachments = [];
          // Refresh current attachments from response if available
          if (this.$page.props.deposit.attachments) {
            this.currentAttachments = this.$page.props.deposit.attachments;
          }
        },
      })
    },
    destroy() {
      if (confirm('Are you sure you want to delete this deposit?')) {
        this.$inertia.delete(`/deposits/${this.deposit.id}`)
      }
    },
    showCurrentImage() {
      this.selectedImage = this.deposit.image_path
      this.showImageModal = true
    },
    closeImageModal() {
      this.showImageModal = false
      this.selectedImage = null
    },
    removeSale(saleId) {
      this.salesToRemove.push(saleId);
      // Recalculate amount if in daily sales deposit mode
      if (this.form.deposit_type === 'daily_sales_deposit') {
        this.form.amount = this.currentSalesTotal + this.selectedSalesTotal;
      }
    },
    toggleSelectAllAvailable() {
      if (this.areAllAvailableSelected) {
        this.salesToAdd = [];
      } else {
        this.salesToAdd = this.filteredAvailableSales.map(sale => parseInt(sale.id));
      }
    },
    handleAttachmentsUpload(event) {
      const files = Array.from(event.target.files);

      // Validate file count (current + new + pending new files)
      const totalFiles = this.currentAttachments.length + this.newAttachments.length + files.length - this.removeAttachments.length;
      if (totalFiles > 10) {
        alert('You can only have up to 10 files total.');
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

      // Add files to new attachments
      this.newAttachments = [...this.newAttachments, ...files];
    },
    removeCurrentAttachment(path) {
      // Add to removal list
      this.removeAttachments.push(path);
      // Remove from current display
      this.currentAttachments = this.currentAttachments.filter(attachment => attachment.path !== path);
    },
    removeNewAttachment(index) {
      this.newAttachments.splice(index, 1);
    },
    viewAttachment(attachment) {
      if (attachment.type?.includes('pdf')) {
        // Open PDF in new tab
        window.open(`/storage/${attachment.path}`, '_blank');
      } else {
        // Show image in modal
        this.selectedImage = attachment.path;
        this.showImageModal = true;
      }
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