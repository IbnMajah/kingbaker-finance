<template>
  <div>
    <Head title="Edit Sale" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center space-x-4 mb-2">
        <Link href="/sales" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          </svg>
        </Link>
        <h1 class="text-3xl font-bold">Edit Sale</h1>
      </div>
      <p class="text-gray-600">Update sale transaction details</p>
    </div>

    <!-- Sale Summary Card -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold mb-2">Sale Summary</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
              <span class="text-gray-500">Date:</span>
              <div class="font-medium">{{ $formatDate(sale.sales_date) }}</div>
            </div>
            <div>
              <span class="text-gray-500">Branch:</span>
              <div class="font-medium">{{ sale.branch?.name || 'N/A' }}</div>
            </div>
            <div>
              <span class="text-gray-500">Shift:</span>
              <div class="font-medium">{{ sale.shift?.name || 'N/A' }}</div>
            </div>
            <div>
              <span class="text-gray-500">Amount:</span>
              <div class="font-medium text-green-600">{{ $formatAmount(sale.amount) }}</div>
            </div>
          </div>
        </div>
        <div class="text-right">
          <div class="text-sm text-gray-500">Last Updated</div>
          <div class="text-sm font-medium">{{ $formatDate(sale.updated_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Deleted Sale Notice -->
    <div v-if="sale.deleted_at" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <div>
          <h3 class="text-sm font-medium text-red-800">This sale has been deleted</h3>
          <p class="text-sm text-red-700 mt-1">
            You can restore this sale or permanently delete it.
          </p>
        </div>
        <button @click="restore" class="ml-auto px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
          Restore
        </button>
      </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="update">
        <!-- Form Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold">Sale Information</h2>
        </div>

        <!-- Form Body -->
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Branch -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Branch <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.branch_id"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                  form.errors.branch_id ? 'border-red-300' : 'border-gray-300'
                ]"
                :disabled="sale.deleted_at"
              >
                <option value="">Select a branch</option>
                <option v-for="branch in branches" :key="branch.value" :value="branch.value">
                  {{ branch.label }}
                </option>
              </select>
              <p v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.branch_id }}
              </p>
            </div>

            <!-- Shift -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Shift <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.shift_id"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                  form.errors.shift_id ? 'border-red-300' : 'border-gray-300'
                ]"
                :disabled="sale.deleted_at"
              >
                <option value="">Select a shift</option>
                <option v-for="shift in shifts" :key="shift.value" :value="shift.value">
                  {{ shift.label }}
                </option>
              </select>
              <p v-if="form.errors.shift_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.shift_id }}
              </p>
            </div>

            <!-- Sales Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Sales Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.sales_date"
                type="date"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                  form.errors.sales_date ? 'border-red-300' : 'border-gray-300'
                ]"
                :disabled="sale.deleted_at"
              />
              <p v-if="form.errors.sales_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.sales_date }}
              </p>
            </div>

            <!-- Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Amount <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="sm:text-sm text-[#9B672A]">D</span>
                </div>
                <input
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0.01"
                  placeholder="0.00"
                  :class="[
                    'w-full pl-8 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                    form.errors.amount ? 'border-red-300' : 'border-gray-300'
                  ]"
                  :disabled="sale.deleted_at"
                />
              </div>
              <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">
                {{ form.errors.amount }}
              </p>
            </div>

            <!-- Cashier -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Cashier
              </label>
              <input
                v-model="form.cashier"
                type="text"
                placeholder="Enter cashier name"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                  form.errors.cashier ? 'border-red-300' : 'border-gray-300'
                ]"
                :disabled="sale.deleted_at"
              />
              <p v-if="form.errors.cashier" class="mt-1 text-sm text-red-600">
                {{ form.errors.cashier }}
              </p>
            </div>
          </div>
        </div>

        <!-- Form Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link href="/sales" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
              Back to Sales
            </Link>
            <button
              v-if="!sale.deleted_at"
              type="button"
              @click="destroy"
              class="px-4 py-2 text-sm text-red-600 hover:text-red-800"
            >
              Delete Sale
            </button>
          </div>
          <button
            v-if="!sale.deleted_at"
            type="submit"
            :disabled="form.processing"
            :class="[
              'px-6 py-2 bg-brand-600 text-white font-medium rounded-md hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
              { 'opacity-50 cursor-not-allowed': form.processing }
            ]"
          >
            <span v-if="form.processing" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Updating...
            </span>
            <span v-else>Update Sale</span>
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
  mixins: [formatterMixin],
  layout: Layout,
  props: {
    sale: {
      type: Object,
      required: true
    },
    branches: {
      type: Array,
      default: () => []
    },
    shifts: {
      type: Array,
      default: () => []
    },
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        branch_id: this.sale.branch_id,
        shift_id: this.sale.shift_id,
        sales_date: this.sale.sales_date,
        amount: this.sale.amount,
        cashier: this.sale.cashier,
      }),
    }
  },
  methods: {
    update() {
      this.form.put(`/sales/${this.sale.id}`)
    },
    destroy() {
      if (confirm('Are you sure you want to delete this sale?')) {
        this.$inertia.delete(`/sales/${this.sale.id}`)
      }
    },
    restore() {
      if (confirm('Are you sure you want to restore this sale?')) {
        this.$inertia.put(`/sales/${this.sale.id}/restore`)
      }
    },
  },
}
</script>