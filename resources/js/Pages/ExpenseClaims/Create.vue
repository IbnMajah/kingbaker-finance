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
                readonly
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account *</label>
              <select
                v-model="form.bank_account_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bank_account_id ? 'border-red-500' : ''"
                required
              >
                <option value="">Select Bank Account</option>
                <option v-for="account in bankAccounts" :key="account.value" :value="account.value">
                  {{ account.label }}
                </option>
              </select>
              <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
              <p class="mt-1 text-sm text-gray-500">Select the bank account to debit for this expense</p>
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
            <label class="block text-sm font-medium text-gray-700 mb-3">Expense Items *</label>
            <div class="border border-gray-300 rounded-md overflow-hidden">
              <!-- Items Header -->
              <div class="bg-gray-50 px-4 py-3 border-b border-gray-300">
                <div class="grid grid-cols-12 gap-3 text-sm font-medium text-gray-700">
                  <div class="col-span-3">Description</div>
                  <div class="col-span-2">Category</div>
                  <div class="col-span-2">Receipt</div>
                  <div class="col-span-1">Unit Price</div>
                  <div class="col-span-1">Quantity</div>
                  <div class="col-span-2">Total</div>
                  <div class="col-span-1">Action</div>
                </div>
              </div>
              <!-- Items List -->
              <div class="divide-y divide-gray-200">
                <div v-for="(item, index) in form.items" :key="index" class="px-4 py-3">
                  <div class="grid grid-cols-12 gap-3 items-start">
                    <!-- Description -->
                    <div class="col-span-3">
                      <input
                        v-model="item.description"
                        type="text"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.description`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="Expense item description"
                        required
                      />
                      <div v-if="form.errors[`items.${index}.description`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.description`] }}
                      </div>
                    </div>
                    <!-- Category -->
                    <div class="col-span-2">
                      <select
                        v-model="item.category"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
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
                    <div class="col-span-2">
                      <input
                        @input="handleReceiptUpload(index, $event)"
                        type="file"
                        accept="image/*"
                        class="w-full px-2 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-xs"
                        :class="form.errors[`items.${index}.receipt_image`] ? 'border-red-300' : 'border-gray-300'"
                      />
                      <div v-if="form.errors[`items.${index}.receipt_image`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.receipt_image`] }}
                      </div>
                    </div>
                    <!-- Unit Price -->
                    <div class="col-span-1">
                      <input
                        v-model.number="item.unit_price"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="w-full px-2 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.unit_price`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="0.00"
                        required
                      />
                      <div v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.unit_price`] }}
                      </div>
                    </div>
                    <!-- Quantity -->
                    <div class="col-span-1">
                      <input
                        v-model.number="item.quantity"
                        type="number"
                        min="1"
                        class="w-full px-2 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.quantity`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="1"
                        required
                      />
                      <div v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.quantity`] }}
                      </div>
                    </div>
                    <!-- Total -->
                    <div class="col-span-2">
                      <div class="px-2 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-700 text-sm">
                        GMD {{ ((item.unit_price || 0) * (item.quantity || 0)).toFixed(2) }}
                      </div>
                    </div>
                    <!-- Action -->
                    <div class="col-span-1">
                      <button
                        type="button"
                        @click="removeItem(index)"
                        class="w-full text-red-600 hover:text-red-800 px-2 py-2 text-sm"
                        :disabled="form.items.length <= 1"
                      >
                        <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 012 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v3a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Add Item Button -->
              <div class="px-4 py-3 bg-gray-50 border-t border-gray-300">
                <button
                  type="button"
                  @click="addItem"
                  class="text-brand-600 hover:text-brand-700 text-sm font-medium flex items-center"
                >
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                  </svg>
                  Add Item
                </button>
              </div>
              <!-- Total Section -->
              <div class="px-4 py-3 bg-blue-50 border-t border-gray-300">
                <div class="flex justify-between items-center">
                  <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
                  <span class="text-xl font-bold text-brand-600">GMD {{ totalAmount.toFixed(2) }}</span>
                </div>
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
    bankAccounts: {
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
        bank_account_id: '',
        notes: '',
        items: [
          {
            description: '',
            category: '',
            receipt_image: null,
            unit_price: '',
            quantity: 1,
          },
        ],
      }),
    }
  },
  methods: {
    addItem() {
      this.form.items.push({
        description: '',
        category: '',
        receipt_image: null,
        unit_price: '',
        quantity: 1,
      });
    },
    removeItem(index) {
      this.form.items.splice(index, 1);
    },
    handleReceiptUpload(index, event) {
      this.form.items[index].receipt_image = event.target.files[0];
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