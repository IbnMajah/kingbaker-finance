<template>
  <div>
    <Head title="Create Sale" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center space-x-4 mb-2">
        <Link href="/sales" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          </svg>
        </Link>
        <h1 class="text-3xl font-bold">Create Sale</h1>
      </div>
      <p class="text-gray-600">Record a new sales transaction</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="store">
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
                  <span class=" sm:text-sm text-[#9B672A]">D</span>
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
                />
              </div>
              <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">
                {{ form.errors.amount }}
              </p>
            </div>
          </div>
        </div>

        <!-- Form Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/sales" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
            Cancel
          </Link>
          <button
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
              Creating...
            </span>
            <span v-else>Create Sale</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'

export default {
  components: {
    Head,
    Link,
  },
  layout: Layout,
  props: {
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
        branch_id: '',
        shift_id: '',
        sales_date: new Date().toISOString().split('T')[0],
        amount: '',
      }),
    }
  },
  methods: {
    store() {
      this.form.post('/sales')
    },
  },
}
</script>