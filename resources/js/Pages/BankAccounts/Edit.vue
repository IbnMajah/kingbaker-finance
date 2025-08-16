<template>
  <div>
    <Head :title="form.name" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center">
        <h1 class="text-3xl font-bold">
          <Link class="text-brand-400 hover:text-brand-600" href="/bank-accounts">Accounts</Link>
          <span class="text-brand-400 font-medium">/</span>
          {{ form.name }}
        </h1>
        <div class="ml-4 flex-shrink-0">
          <img v-if="bankAccount.photo_path"
               class="h-12 w-12 rounded-full object-cover border-2 border-white shadow-md"
               :src="`/storage/${bankAccount.photo_path}`"
               :alt="bankAccount.name" />
          <div v-else class="h-12 w-12 rounded-full bg-brand-500 flex items-center justify-center border-2 border-white shadow-md">
            <span class="text-lg font-medium text-white">
              {{ bankAccount.name.charAt(0).toUpperCase() }}
            </span>
          </div>
        </div>
      </div>
      <p class="text-gray-600 mt-2">Edit account information and settings</p>
    </div>

    <!-- Alert Messages -->
    <trashed-message v-if="bankAccount.deleted_at" class="mb-6" @restore="restore">
      This bank account has been deleted.
    </trashed-message>

    <!-- Account Details Card -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500 mb-3">Account Avatar</div>
          <div class="flex justify-center">
            <img v-if="bankAccount.photo_path"
                 class="h-16 w-16 rounded-full object-cover border-2 border-gray-200"
                 :src="`/storage/${bankAccount.photo_path}`"
                 :alt="bankAccount.name" />
            <div v-else class="h-16 w-16 rounded-full bg-brand-500 flex items-center justify-center">
              <span class="text-xl font-medium text-white">
                {{ bankAccount.name.charAt(0).toUpperCase() }}
              </span>
            </div>
          </div>
        </div>
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Current Balance</div>
          <div v-if="isAdmin" class="text-2xl font-bold mt-1" :class="bankAccount.current_balance >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ $formatAmount(bankAccount.current_balance) }}
          </div>
          <div v-else class="text-2xl font-bold mt-1 text-gray-500">
            ••••••
          </div>
        </div>
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Account Status</div>
          <div class="mt-1">
            <span v-if="bankAccount.active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
              Active
            </span>
            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
              Inactive
            </span>
          </div>
        </div>
        <div class="text-center">
          <div class="text-sm font-medium text-gray-500">Last Updated</div>
          <div class="text-sm text-gray-900 mt-1">
            {{ $formatDate(bankAccount.updated_at) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Account Information</h2>
      </div>

      <form @submit.prevent="update">
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
              <p class="mt-1 text-sm text-gray-500">Optional: Upload a new image for this account</p>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link href="/bank-accounts" class="text-gray-600 hover:text-gray-800">
              <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
              </svg>
              Back to Accounts
            </Link>
            <button
              v-if="!bankAccount.deleted_at"
              @click="destroy"
              type="button"
              class="text-red-600 hover:text-red-800 flex items-center"
            >
              <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
              Delete Account
            </button>
          </div>

          <div class="flex items-center space-x-3">
            <Link :href="`/bank-accounts/${bankAccount.id}`" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-md hover:bg-gray-50">
              View Account Sheet
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
              {{ form.processing ? 'Updating...' : 'Update Account' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import TrashedMessage from '@/Shared/TrashedMessage.vue'
import { formatterMixin } from '@/Utils/formatters'
import { usePermissions } from '@/composables/usePermissions.js'

export default {
  components: {
    Head,
    Link,
    TrashedMessage,
  },
  mixins: [formatterMixin],
  layout: Layout,
  setup() {
    const { isAdmin } = usePermissions()
    return { isAdmin }
  },
  props: {
    bankAccount: Object,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        _method: 'put',
        name: this.bankAccount.name,
        account_number: this.bankAccount.account_number,
        bank_name: this.bankAccount.bank_name,
        current_balance: this.bankAccount.current_balance,
        active: this.bankAccount.active,
        photo: null,
      }),
    }
  },
  methods: {
    update() {
      this.form.post(`/bank-accounts/${this.bankAccount.id}`, {
        onSuccess: () => this.form.reset('photo'),
      })
    },
    destroy() {
      if (confirm('Are you sure you want to delete this bank account?')) {
        this.$inertia.delete(`/bank-accounts/${this.bankAccount.id}`)
      }
    },
    restore() {
      if (confirm('Are you sure you want to restore this bank account?')) {
        this.$inertia.put(`/bank-accounts/${this.bankAccount.id}/restore`)
      }
    },
  },
}
</script>