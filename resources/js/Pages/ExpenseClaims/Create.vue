<template>
  <div>
    <Head title="Create Expense Claim" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/expense-claims">Expense Claims</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create
      </h1>
      <p class="text-gray-600">Create a new expense claim</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Form Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Expense Claim Information</h2>
      </div>

      <form @submit.prevent="submit">
        <div class="p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Reference ID</label>
              <input
                v-model="form.reference_id"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.reference_id ? 'border-red-500' : ''"

              />
              <div v-if="form.errors.reference_id" class="mt-1 text-sm text-red-600">{{ form.errors.reference_id }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Claim Date *</label>
              <input
                v-model="form.claim_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.claim_date ? 'border-red-500' : ''"
                required
              />
              <div v-if="form.errors.claim_date" class="mt-1 text-sm text-red-600">{{ form.errors.claim_date }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
              <input
                v-model="form.title"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.title ? 'border-red-500' : ''"
                required
              />
              <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payee *</label>
              <input
                v-model="form.payee"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.payee ? 'border-red-500' : ''"
                required
              />
              <div v-if="form.errors.payee" class="mt-1 text-sm text-red-600">{{ form.errors.payee }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Expense Type *</label>
              <select
                v-model="form.expense_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.expense_type ? 'border-red-500' : ''"
                required
              >
                <option value="">Select Type</option>
                <option v-for="type in expenseTypes" :key="type" :value="type">
                  {{ type.replace('_', ' ').charAt(0).toUpperCase() + type.slice(1) }}
                </option>
              </select>
              <div v-if="form.errors.expense_type" class="mt-1 text-sm text-red-600">{{ form.errors.expense_type }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
              <select
                v-model="form.branch_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.branch_id ? 'border-red-500' : ''"
              >
                <option value="">Select Branch</option>
                <option v-for="branch in branches" :key="branch.value" :value="branch.value">
                  {{ branch.label }}
                </option>
              </select>
              <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</div>
            </div>
          </div>

          <!-- Expense Items -->
          <div class="col-span-full">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Expense Items</h3>
              </div>
              <div class="text-right">
                <div class="flex items-center justify-end space-x-4">
                  <!-- Auto-save Status -->
                  <div class="flex items-center space-x-2">
                    <div v-if="autoSaveStatus === 'saving'" class="flex items-center text-blue-600">
                      <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span class="text-sm">Saving...</span>
                    </div>
                    <div v-else-if="autoSaveStatus === 'saved'" class="flex items-center text-green-600">
                      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                      <span class="text-sm">Saved</span>
                    </div>
                    <div v-else-if="autoSaveStatus === 'error'" class="flex items-center text-red-600">
                      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                      </svg>
                      <span class="text-sm">Save failed</span>
                    </div>
                  </div>

                  <!-- Total Amount -->
                  <div>
                    <div class="text-sm text-gray-500">Total</div>
                    <div class="text-2xl font-bold text-brand-600">GMD {{ totalAmount.toFixed(2) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
              <!-- Items List -->
              <div class="divide-y divide-gray-200">
                <div v-for="(item, index) in form.items" :key="index" class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-gray-900">Item #{{ index + 1 }}</h4>
                    <button
                      type="button"
                      @click="removeItem(index)"
                      class="text-red-600 hover:text-red-800 p-1"
                      :disabled="form.items.length <= 1"
                    >
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 012 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v3a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                      </svg>
                    </button>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Description -->
                    <div class="lg:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                      <input
                        v-model="item.description"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.description`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="e.g., Taxi fare to airport"
                        required
                      />
                      <div v-if="form.errors[`items.${index}.description`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.description`] }}
                      </div>
                    </div>

                    <!-- Category -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                      <select
                        v-model="item.category"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.category`] ? 'border-red-300' : 'border-gray-300'"
                      >
                        <option value="">Select Category</option>
                        <option v-for="category in categories" :key="category" :value="category">
                          {{ category.replace('_', ' ').charAt(0).toUpperCase() + category.slice(1) }}
                        </option>
                      </select>
                      <div v-if="form.errors[`items.${index}.category`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.category`] }}
                      </div>
                    </div>

                    <!-- Receipt -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Receipt</label>
                      <input
                        @input="handleReceiptUpload(index, $event)"
                        type="file"
                        accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                        :class="form.errors[`items.${index}.receipt_image`] ? 'border-red-300' : 'border-gray-300'"
                      />
                      <div v-if="form.errors[`items.${index}.receipt_image`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.receipt_image`] }}
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <!-- Unit Price -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price *</label>
                      <input
                        v-model.number="item.unit_price"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.unit_price`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="0.00"
                        required
                      />
                      <div v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.unit_price`] }}
                      </div>
                    </div>

                    <!-- Unit Measurement -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                      <input
                        v-model="item.unit_measurement"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.unit_measurement`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="e.g., each, hour, mile"
                      />
                      <div v-if="form.errors[`items.${index}.unit_measurement`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.unit_measurement`] }}
                      </div>
                    </div>

                    <!-- Quantity -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                      <input
                        v-model.number="item.quantity"
                        type="number"
                        min="0.01"
                        step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.quantity`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="1.00"
                        required
                      />
                      <div v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.quantity`] }}
                      </div>
                    </div>
                  </div>

                  <!-- Item Total -->
                  <div class="mt-4 flex justify-end">
                    <div class="text-right">
                      <div class="text-sm text-gray-500">Item Total:</div>
                      <div class="text-lg font-bold text-blue-600">
                        GMD {{ ((item.unit_price || 0) * (item.quantity || 0)).toFixed(2) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add Item Button -->
              <div class="px-6 py-4 border-t border-gray-200 border-dashed">
                <button
                  type="button"
                  @click="addItem"
                  class="w-full flex items-center justify-center px-4 py-3 text-brand-600 hover:text-brand-700 text-sm font-medium border-2 border-dashed border-gray-300 rounded-lg hover:border-brand-400 transition-colors"
                >
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                  </svg>
                  Add Another Item
                </button>
              </div>
            </div>
            <div v-if="form.errors.items" class="mt-1 text-sm text-red-600">{{ form.errors.items }}</div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.notes ? 'border-red-500' : ''"
              placeholder="Optional: Add any additional notes about this expense claim"
            ></textarea>
            <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/expense-claims" class="text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-kingbaker flex items-center"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ form.processing ? 'Creating...' : 'Create Expense Claim' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { useFormTokenRefresh } from '@/composables/useFormTokenRefresh.js';

export default {
  components: {
    Head,
    Link,
  },
  layout: Layout,
  setup() {
    const { ensureValidToken } = useFormTokenRefresh();

    return {
      ensureValidToken
    };
  },
  props: {
    referenceId: {
      type: String,
      required: true,
    },
    expenseTypes: {
      type: Array,
      required: true,
    },
    categories: {
      type: Array,
      required: true,
    },
    branches: {
      type: Array,
      required: true,
    },
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        reference_id: this.referenceId,
        claim_date: new Date().toISOString().split('T')[0],
        title: '',
        payee: '',
        expense_type: '',
        branch_id: '',
        notes: '',
        items: [
          {
            description: '',
            category: '',
            receipt_image: null,
            unit_price: '',
            unit_measurement: '',
            quantity: 1,
          },
        ],
      }),
      autoSaveStatus: 'idle', // 'idle', 'saving', 'saved', 'error'
      autoSaveTimeout: null,
      expenseClaimId: null,
    }
  },
  methods: {
    addItem() {
      this.form.items.push({
        description: '',
        category: '',
        receipt_image: null,
        unit_price: '',
        unit_measurement: '',
        quantity: 1,
      });

      // Auto-save after adding item
      this.autoSave();
    },
    removeItem(index) {
      this.form.items.splice(index, 1);

      // Auto-save after removing item
      this.autoSave();
    },
    handleReceiptUpload(index, event) {
      this.form.items[index].receipt_image = event.target.files[0];
    },
    async autoSave() {
      // Clear existing timeout
      if (this.autoSaveTimeout) {
        clearTimeout(this.autoSaveTimeout);
      }

      // Check if we should auto-save
      if (!this.shouldAutoSave()) {
        return;
      }

      // Set status to saving
      this.autoSaveStatus = 'saving';

      // Debounce auto-save
      this.autoSaveTimeout = setTimeout(async () => {
        try {
          // Ensure CSRF token is valid
          await this.ensureValidToken();

          // Get valid items (exclude empty/incomplete items)
          const validItems = this.getValidItems();

          // Prepare form data for auto-save
          const formData = new FormData();
          formData.append('reference_id', this.form.reference_id);
          formData.append('claim_date', this.form.claim_date);
          formData.append('title', this.form.title || 'Draft Expense Claim');
          formData.append('payee', this.form.payee || 'Draft Payee');
          formData.append('expense_type', this.form.expense_type || 'other');
          formData.append('branch_id', this.form.branch_id || '');
          formData.append('notes', this.form.notes || '');

          // Add valid items data
          validItems.forEach((item, index) => {
            formData.append(`items[${index}][description]`, item.description);
            formData.append(`items[${index}][category]`, item.category || '');
            formData.append(`items[${index}][unit_price]`, item.unit_price);
            formData.append(`items[${index}][unit_measurement]`, item.unit_measurement || '');
            formData.append(`items[${index}][quantity]`, item.quantity);
          });

          const response = await fetch('/expense-claims/auto-save', {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
          });

          const result = await response.json();

          if (result.success) {
            this.autoSaveStatus = 'saved';
            this.expenseClaimId = result.expense_claim_id;

            // Reset status after 2 seconds
            setTimeout(() => {
              this.autoSaveStatus = 'idle';
            }, 2000);
          } else {
            this.autoSaveStatus = 'error';
            console.error('Auto-save failed:', result.message);
          }
        } catch (error) {
          this.autoSaveStatus = 'error';
          console.error('Auto-save error:', error);
        }
      }, 1000); // 1 second delay
    },
    shouldAutoSave() {
      // Don't auto-save if basic required fields are missing
      if (!this.form.claim_date) {
        return false;
      }

      // Get valid items (exclude empty/incomplete items)
      const validItems = this.getValidItems();

      // Don't auto-save if there are no valid items
      if (validItems.length === 0) {
        return false;
      }

      return true;
    },
    getValidItems() {
      return this.form.items.filter(item => {
        // Exclude items that are empty or missing required fields
        return item.description &&
               item.description.trim() !== '' &&
               item.unit_price &&
               item.unit_price > 0 &&
               item.quantity &&
               item.quantity > 0;
      });
    },
    async submit() {
      // Ensure CSRF token is valid before submission
      await this.ensureValidToken();

      this.form.post('/expense-claims');
    },
  },
  computed: {
    totalAmount() {
      return this.form.items.reduce((sum, item) => {
        const price = parseFloat(item.unit_price || 0);
        const qty = parseInt(item.quantity || 1);
        return sum + price * qty;
      }, 0);
    },
  },
}
</script>