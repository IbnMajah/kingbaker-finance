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
            {{ $formatAmount(expenseClaim.amount) }}
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account *</label>
              <select
                v-model="form.bank_account_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bank_account_id ? 'border-red-500' : ''"
                required
              >
                <option value="">Select Account</option>
                <option v-for="account in bankAccounts" :key="account.value" :value="account.value">
                  {{ account.label }}
                </option>
              </select>
              <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
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
          <div>
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Expense Items</h3>
              <button
                type="button"
                @click="addItem"
                class="btn-kingbaker flex items-center"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
              </button>
            </div>

            <div v-for="(item, index) in form.items" :key="index" class="bg-gray-50 p-4 rounded-lg mb-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                  <input
                    v-model="item.expense_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors['items.' + index + '.expense_date'] ? 'border-red-500' : ''"
                    required
                  />
                  <div v-if="form.errors['items.' + index + '.expense_date']" class="mt-1 text-sm text-red-600">
                    {{ form.errors['items.' + index + '.expense_date'] }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                  <input
                    v-model="item.title"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors['items.' + index + '.title'] ? 'border-red-500' : ''"
                    placeholder="Brief title for the expense"
                    required
                  />
                  <div v-if="form.errors['items.' + index + '.title']" class="mt-1 text-sm text-red-600">
                    {{ form.errors['items.' + index + '.title'] }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                  <input
                    v-model="item.description"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors['items.' + index + '.description'] ? 'border-red-500' : ''"
                    required
                  />
                  <div v-if="form.errors['items.' + index + '.description']" class="mt-1 text-sm text-red-600">
                    {{ form.errors['items.' + index + '.description'] }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                  <input
                    v-model="item.quantity"
                    type="number"
                    min="1"
                    step="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors['items.' + index + '.quantity'] ? 'border-red-500' : ''"
                    placeholder="1"
                    required
                  />
                  <div v-if="form.errors['items.' + index + '.quantity']" class="mt-1 text-sm text-red-600">
                    {{ form.errors['items.' + index + '.quantity'] }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                  <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                    <input
                      v-model="item.amount"
                      type="number"
                      step="0.01"
                      min="0.01"
                      class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                      :class="form.errors['items.' + index + '.amount'] ? 'border-red-500' : ''"
                      placeholder="0.00"
                      required
                    />
                  </div>
                  <div v-if="form.errors['items.' + index + '.amount']" class="mt-1 text-sm text-red-600">
                    {{ form.errors['items.' + index + '.amount'] }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                  <select
                    v-model="item.category"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors['items.' + index + '.category'] ? 'border-red-500' : ''"
                  >
                    <option value="">Select Category</option>
                    <option v-for="category in categories" :key="category" :value="category">
                      {{ category.replace('_', ' ').charAt(0).toUpperCase() + category.slice(1) }}
                    </option>
                  </select>
                  <div v-if="form.errors['items.' + index + '.category']" class="mt-1 text-sm text-red-600">
                    {{ form.errors['items.' + index + '.category'] }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Receipt</label>
                  <input
                    @input="item.receipt_image = $event.target.files[0]"
                    type="file"
                    accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="form.errors['items.' + index + '.receipt_image'] ? 'border-red-500' : ''"
                  />
                  <div v-if="form.errors['items.' + index + '.receipt_image']" class="mt-1 text-sm text-red-600">
                    {{ form.errors['items.' + index + '.receipt_image'] }}
                  </div>
                  <div v-if="item.receipt_image_path" class="mt-2">
                    <button type="button" @click="showImage(item.receipt_image_path)" class="text-sm text-blue-600 hover:text-blue-800">
                      View current receipt
                    </button>
                  </div>
                  <p class="mt-1 text-sm text-gray-500">Optional: Upload new receipt or proof of expense</p>
                </div>

                <div class="flex items-end">
                  <button
                    type="button"
                    @click="removeItem(index)"
                    class="btn-kingbaker flex items-center bg-red-600 hover:bg-red-700"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remove
                  </button>
                </div>
              </div>
            </div>
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
    <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeImageModal">
      <div class="max-w-4xl max-h-full p-4">
        <img :src="`/storage/${selectedImage}`" class="max-w-full max-h-full rounded-lg" />
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
    bankAccounts: {
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
        payee: this.expenseClaim.payee,
        expense_type: this.expenseClaim.expense_type,
        bank_account_id: this.expenseClaim.bank_account_id,
        branch_id: this.expenseClaim.branch_id,
        notes: this.expenseClaim.notes,
        items: this.expenseClaim.items.map(item => ({
          id: item.id,
          expense_date: item.expense_date ? item.expense_date.split('T')[0] : '',
          title: item.title || '',
          description: item.description,
          quantity: item.quantity || 1,
          amount: item.amount,
          category: item.category,
          receipt_image: null,
          receipt_image_path: item.receipt_image_path,
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
      this.form.items.push({
        expense_date: new Date().toISOString().split('T')[0],
        title: '',
        description: '',
        quantity: 1,
        amount: '',
        category: '',
        receipt_image: null,
      });
    },
    removeItem(index) {
      this.form.items.splice(index, 1);
    },
    showImage(imagePath) {
      this.selectedImage = imagePath;
      this.showImageModal = true;
    },
    closeImageModal() {
      this.showImageModal = false;
      this.selectedImage = null;
    },
    submit() {
      this.form.put(`/expense-claims/${this.expenseClaim.id}`, {
        onSuccess: () => this.form.reset('items.*.receipt_image'),
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