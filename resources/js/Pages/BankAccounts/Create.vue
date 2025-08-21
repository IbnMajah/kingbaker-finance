<template>
  <div>
    <Head title="Create Bank Account" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/bank-accounts">Accounts</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create
      </h1>
      <p class="text-gray-600">Add a new bank account to the system</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Account Information</h2>
      </div>

      <form @submit.prevent="store">
        <div class="p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Account Name *</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.name ? 'border-red-500' : ''"
                placeholder="Enter account name"
              />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
              <input
                v-model="form.account_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.account_number ? 'border-red-500' : ''"
                placeholder="Enter account number"
              />
              <div v-if="form.errors.account_number" class="mt-1 text-sm text-red-600">{{ form.errors.account_number }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
              <input
                v-model="form.bank_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bank_name ? 'border-red-500' : ''"
                placeholder="Enter bank name"
              />
              <div v-if="form.errors.bank_name" class="mt-1 text-sm text-red-600">{{ form.errors.bank_name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Current Balance *</label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                <input
                  v-model="form.current_balance"
                  type="number"
                  step="0.01"
                  class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.current_balance ? 'border-red-500' : ''"
                  placeholder="0.00"
                />
              </div>
              <div v-if="form.errors.current_balance" class="mt-1 text-sm text-red-600">{{ form.errors.current_balance }}</div>
            </div>
          </div>

          <!-- Account Status -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Account Status</label>
              <select
                v-model="form.active"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.active ? 'border-red-500' : ''"
              >
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
              <div v-if="form.errors.active" class="mt-1 text-sm text-red-600">{{ form.errors.active }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Account Photo</label>
              <input
                @input="form.photo = $event.target.files[0]"
                type="file"
                accept="image/*"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.photo ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.photo" class="mt-1 text-sm text-red-600">{{ form.errors.photo }}</div>
              <p class="mt-1 text-sm text-gray-500">Optional: Upload an image for this account</p>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/bank-accounts" class="text-gray-600 hover:text-gray-800">
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
            {{ form.processing ? 'Creating...' : 'Create Account' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import { useFormTokenRefresh } from '@/composables/useFormTokenRefresh.js'

export default {
  components: {
    Head,
    Link,
  },
  layout: Layout,
  remember: 'form',
  setup() {
    const { ensureValidToken } = useFormTokenRefresh()
    
    return {
      ensureValidToken
    }
  },
  data() {
    return {
      form: this.$inertia.form({
        name: '',
        account_number: '',
        bank_name: '',
        current_balance: '',
        active: true,
        photo: null,
      }),
    }
  },
  methods: {
    async store() {
      // Ensure CSRF token is valid before submission
      await this.ensureValidToken()
      
      this.form.post('/bank-accounts')
    },
  },
}
</script>