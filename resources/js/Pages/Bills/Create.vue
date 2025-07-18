<template>
  <div>
    <Head title="Create Bill" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/bills">Bills</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create
      </h1>
      <p class="text-gray-600">Add a new bill to the system</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Bill Information</h2>
      </div>

      <form @submit.prevent="store">
        <div class="p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
              <select
                v-model="form.vendor_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.vendor_id ? 'border-red-500' : ''"
              >
                <option value="">Select vendor</option>
                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.name }}
                </option>
              </select>
              <div v-if="form.errors.vendor_id" class="mt-1 text-sm text-red-600">{{ form.errors.vendor_id }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bill Number</label>
              <input
                v-model="form.bill_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bill_number ? 'border-red-500' : ''"
                placeholder="Enter bill number (optional)"
              />
              <div v-if="form.errors.bill_number" class="mt-1 text-sm text-red-600">{{ form.errors.bill_number }}</div>
            </div>
          </div>

          <!-- Dates and Amount -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bill Date *</label>
              <input
                v-model="form.bill_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bill_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.bill_date" class="mt-1 text-sm text-red-600">{{ form.errors.bill_date }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <input
                v-model="form.due_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.due_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                <input
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.amount ? 'border-red-500' : ''"
                  placeholder="0.00"
                />
              </div>
              <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
            </div>
          </div>

          <!-- Bill Type -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bill Type *</label>
              <select
                v-model="form.bill_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bill_type ? 'border-red-500' : ''"
              >
                <option value="">Select bill type</option>
                <option v-for="type in billTypes" :key="type" :value="type">
                  {{ type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </option>
              </select>
              <div v-if="form.errors.bill_type" class="mt-1 text-sm text-red-600">{{ form.errors.bill_type }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Recurring Frequency</label>
              <select
                v-model="form.recurring_frequency"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.recurring_frequency ? 'border-red-500' : ''"
              >
                <option value="">Not recurring</option>
                <option v-for="frequency in recurringFrequencies" :key="frequency" :value="frequency">
                  {{ frequency.replace(/\b\w/g, l => l.toUpperCase()) }}
                </option>
              </select>
              <div v-if="form.errors.recurring_frequency" class="mt-1 text-sm text-red-600">{{ form.errors.recurring_frequency }}</div>
            </div>
          </div>

          <!-- Next Due Date (only show if recurring) -->
          <div v-if="form.recurring_frequency" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next Due Date</label>
              <input
                v-model="form.next_due_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.next_due_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.next_due_date" class="mt-1 text-sm text-red-600">{{ form.errors.next_due_date }}</div>
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
              placeholder="Enter bill description or notes"
            ></textarea>
            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
          </div>

          <!-- Bill Image -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bill Image</label>
            <input
              @input="form.bill_image = $event.target.files[0]"
              type="file"
              accept="image/*"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.bill_image ? 'border-red-500' : ''"
            />
            <div v-if="form.errors.bill_image" class="mt-1 text-sm text-red-600">{{ form.errors.bill_image }}</div>
            <p class="mt-1 text-sm text-gray-500">Optional: Upload an image/scan of the bill</p>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/bills" class="text-gray-600 hover:text-gray-800">
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
            {{ form.processing ? 'Creating...' : 'Create Bill' }}
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
    vendors: {
      type: Array,
      default: () => []
    },
    billTypes: {
      type: Array,
      default: () => []
    },
    recurringFrequencies: {
      type: Array,
      default: () => []
    }
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        vendor_id: '',
        bill_number: '',
        bill_date: '',
        due_date: '',
        amount: '',
        bill_type: '',
        description: '',
        recurring_frequency: '',
        next_due_date: '',
        bill_image: null,
      }),
    }
  },
  methods: {
    store() {
      this.form.post('/bills')
    },
  },
}
</script>