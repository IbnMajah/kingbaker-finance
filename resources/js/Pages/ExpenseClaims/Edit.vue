<template>
  <div>
    <Head :title="`Edit Expense Claim ${expenseClaim.claim_number}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/expense-claims">Expense Claims</Link>
        <span class="text-brand-400 font-medium">/</span>
        <Link class="text-brand-400 hover:text-brand-600" :href="'/expense-claims/' + expenseClaim.id">{{ expenseClaim.claim_number }}</Link>
        <span class="text-brand-400 font-medium">/</span>
        Edit
      </h1>
      <p class="text-gray-600">Edit expense claim details</p>
    </div>

    <!-- Claim Summary -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Total Amount</div>
          <div class="text-2xl font-bold text-gray-900 mt-1">
            {{ $formatAmount(expenseClaim.total) }}
          </div>
        </div>
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Status</div>
          <div class="mt-1">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getStatusClass(expenseClaim.status)">
              {{ getStatusLabel(expenseClaim.status) }}
            </span>
          </div>
        </div>
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Claim Date</div>
          <div class="text-sm text-gray-900 mt-1">
            {{ $formatDate(expenseClaim.claim_date) }}
          </div>
        </div>
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Created</div>
          <div class="text-sm text-gray-900 mt-1">
            {{ $formatDate(expenseClaim.created_at) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Edit Expense Claim</h2>
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
                readonly
              />
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
                placeholder="Brief title for the expense claim"
                required
              />
              <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select
                v-model="form.category"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.category ? 'border-red-500' : ''"
              >
                <option value="">Select Category</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category.replace('_', ' ').charAt(0).toUpperCase() + category.slice(1) }}
                </option>
              </select>
              <div v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</div>
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

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Receipt</label>
              <input
                @input="form.receipt_image = $event.target.files[0]"
                type="file"
                accept="image/*"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.receipt_image ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.receipt_image" class="mt-1 text-sm text-red-600">{{ form.errors.receipt_image }}</div>
              <div v-if="expenseClaim.receipt_image_path" class="mt-2">
                <button type="button" @click="showImage(expenseClaim.receipt_image_path)" class="text-sm text-blue-600 hover:text-blue-800">
                  View current receipt
                </button>
              </div>
              <p class="mt-1 text-sm text-gray-500">Optional: Upload receipt or proof of expense</p>
            </div>
          </div>

          <!-- Expense Items -->
          <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-700 mb-3">Expense Items *</label>
            <div class="border border-gray-300 rounded-md overflow-hidden">
              <!-- Items Header -->
              <div class="bg-gray-50 px-4 py-3 border-b border-gray-300">
                <div class="grid grid-cols-12 gap-3 text-sm font-medium text-gray-700">
                  <div class="col-span-5">Description</div>
                  <div class="col-span-2">Unit Price (GMD)</div>
                  <div class="col-span-2">Quantity</div>
                  <div class="col-span-2">Total</div>
                  <div class="col-span-1">Action</div>
                </div>
              </div>
              <!-- Items List -->
              <div class="divide-y divide-gray-200">
                <div v-for="(item, index) in (form.items || [])" :key="index" class="px-4 py-3">
                  <div class="grid grid-cols-12 gap-3 items-start">
                    <!-- Description -->
                    <div class="col-span-5">
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
                    <!-- Unit Price -->
                    <div class="col-span-2">
                      <input
                        v-model.number="item.unit_price"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.unit_price`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="0.00"
                        required
                      />
                      <div v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.unit_price`] }}
                      </div>
                    </div>
                    <!-- Quantity -->
                    <div class="col-span-2">
                      <input
                        v-model.number="item.quantity"
                        type="number"
                        min="1"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
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
                      <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                        GMD {{ ((item.unit_price || 0) * (item.quantity || 0)).toFixed(2) }}
                      </div>
                    </div>
                    <!-- Action -->
                    <div class="col-span-1">
                      <button
                        type="button"
                        @click="removeItem(index)"
                        class="w-full text-red-600 hover:text-red-800 px-2 py-2 text-sm"
                        :disabled="(form.items || []).length <= 1"
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
                  <span class="text-xl font-bold text-brand-600">GMD {{ calculateTotal().toFixed(2) }}</span>
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
          <div class="flex items-center space-x-4">
            <Link :href="'/expense-claims/' + expenseClaim.id" class="text-gray-600 hover:text-gray-800">
              <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
              </svg>
              Back to Claim
            </Link>
            <button
              v-if="!expenseClaim.deleted_at"
              @click="destroy"
              type="button"
              class="text-red-600 hover:text-red-800 flex items-center"
            >
              <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              Delete Claim
            </button>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="btn-kingbaker flex items-center"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
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
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import { formatterMixin } from '@/Utils/formatters'

export default {
  components: {
    Head,
    Link,
  },
  layout: Layout,
  mixins: [formatterMixin],
  props: {
    expenseClaim: {
      type: Object,
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
        reference_id: this.expenseClaim.reference_id,
        claim_date: this.expenseClaim.claim_date ? this.expenseClaim.claim_date.split('T')[0] : '',
        title: this.expenseClaim.title || '',
        category: this.expenseClaim.category || '',
        payee: this.expenseClaim.payee,
        expense_type: this.expenseClaim.expense_type,
        branch_id: this.expenseClaim.branch_id,
        notes: this.expenseClaim.notes,
        receipt_image: null,
        items: (this.expenseClaim.items || []).map(item => ({
          id: item.id,
          description: item.description || '',
          unit_price: item.unit_price || 0,
          quantity: item.quantity || 1,
        })),
      }),
      showImageModal: false,
      selectedImage: null,
    }
  },
  methods: {

    getStatusClass(status) {
      const classes = {
        'draft': 'bg-gray-100 text-gray-800',
        'submitted': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'paid': 'bg-blue-100 text-blue-800',
      };
      return classes[status] || 'bg-gray-100 text-gray-800';
    },
    getStatusLabel(status) {
      return status.charAt(0).toUpperCase() + status.slice(1);
    },
    addItem() {
      if (!this.form.items) {
        this.form.items = [];
      }
      this.form.items.push({
        description: '',
        unit_price: 0,
        quantity: 1,
      });
    },
    removeItem(index) {
      if (this.form.items && this.form.items.length > index) {
        this.form.items.splice(index, 1);
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
    calculateTotal() {
      return (this.form.items || []).reduce((sum, item) => sum + (item.unit_price || 0) * (item.quantity || 1), 0);
    },
    submit() {
      this.form.put(`/expense-claims/${this.expenseClaim.id}`, {
        onSuccess: () => this.form.reset('receipt_image'),
      });
    },
    destroy() {
      if (confirm('Are you sure you want to delete this expense claim?')) {
        this.$inertia.delete(`/expense-claims/${this.expenseClaim.id}`);
      }
    },
  },
}
</script>