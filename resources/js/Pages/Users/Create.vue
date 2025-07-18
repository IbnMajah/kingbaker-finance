<template>
  <div>
    <Head title="Create User" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/users">Users</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create
      </h1>
      <p class="text-gray-600">Add a new user to the system</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">User Information</h2>
      </div>

      <form @submit.prevent="store">
        <div class="p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
              <input
                v-model="form.first_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.first_name ? 'border-red-500' : ''"
                placeholder="Enter first name"
              />
              <div v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
              <input
                v-model="form.last_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.last_name ? 'border-red-500' : ''"
                placeholder="Enter last name"
              />
              <div v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.email ? 'border-red-500' : ''"
                placeholder="Enter email address"
              />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input
                v-model="form.phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.phone ? 'border-red-500' : ''"
                placeholder="Enter phone number"
              />
              <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
              <input
                v-model="form.password"
                type="password"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.password ? 'border-red-500' : ''"
                placeholder="Enter password"
              />
              <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
              <select
                v-model="form.branch_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.branch_id ? 'border-red-500' : ''"
              >
                <option value="">Select Branch</option>
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                  {{ branch.name }}
                </option>
              </select>
              <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</div>
            </div>
          </div>

          <!-- Role and Permissions -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
              <select
                v-model="form.role"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.role ? 'border-red-500' : ''"
                @change="handleRoleChange"
              >
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
                <option value="accountant">Accountant</option>
                <option value="cashier">Cashier</option>
                <option value="staff">Staff</option>
              </select>
              <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">User Status</label>
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
          </div>

          <!-- Permissions Section (shown only for non-admin roles) -->
          <div v-if="form.role && form.role !== 'admin'" class="border rounded-md p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div v-for="(permissionGroup, groupName) in availablePermissions" :key="groupName">
                <h4 class="font-medium text-gray-700 mb-2">{{ formatGroupName(groupName) }}</h4>
                <div class="space-y-2">
                  <label v-for="permission in permissionGroup" :key="permission.value" class="flex items-start">
                    <input
                      type="checkbox"
                      v-model="form.permissions"
                      :value="permission.value"
                      class="mt-1 h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded"
                      :disabled="isPermissionDisabled(permission.value)"
                    />
                    <span class="ml-2 text-sm text-gray-600">{{ permission.label }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Photo Upload -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
            <input
              @input="form.photo = $event.target.files[0]"
              type="file"
              accept="image/*"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.photo ? 'border-red-500' : ''"
            />
            <div v-if="form.errors.photo" class="mt-1 text-sm text-red-600">{{ form.errors.photo }}</div>
            <p class="mt-1 text-sm text-gray-500">Optional: Upload a profile photo</p>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/users" class="text-gray-600 hover:text-gray-800">
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
            {{ form.processing ? 'Creating...' : 'Create User' }}
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
  remember: 'form',
  props: {
    branches: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      form: this.$inertia.form({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        phone: '',
        role: '',
        permissions: [],
        branch_id: '',
        active: true,
        photo: null,
      }),
      availablePermissions: {
        accounts: [
          { value: 'view_accounts', label: 'View Accounts' },
          { value: 'create_accounts', label: 'Create Accounts' },
          { value: 'edit_accounts', label: 'Edit Accounts' },
          { value: 'delete_accounts', label: 'Delete Accounts' },
        ],
        transactions: [
          { value: 'view_transactions', label: 'View Transactions' },
          { value: 'create_transactions', label: 'Create Transactions' },
          { value: 'edit_transactions', label: 'Edit Transactions' },
          { value: 'delete_transactions', label: 'Delete Transactions' },
        ],
        deposits: [
          { value: 'view_deposits', label: 'View Deposits' },
          { value: 'create_deposits', label: 'Create Deposits' },
          { value: 'edit_deposits', label: 'Edit Deposits' },
          { value: 'delete_deposits', label: 'Delete Deposits' },
        ],
        expenses: [
          { value: 'view_expenses', label: 'View Expenses' },
          { value: 'create_expenses', label: 'Create Expenses' },
          { value: 'edit_expenses', label: 'Edit Expenses' },
          { value: 'delete_expenses', label: 'Delete Expenses' },
        ],
        reports: [
          { value: 'view_reports', label: 'View Reports' },
          { value: 'export_reports', label: 'Export Reports' },
        ],
      },
      rolePermissions: {
        manager: [
          'view_accounts', 'view_transactions', 'view_deposits', 'view_expenses',
          'create_deposits', 'create_expenses', 'edit_deposits', 'edit_expenses',
          'view_reports', 'export_reports'
        ],
        accountant: [
          'view_accounts', 'view_transactions', 'view_deposits', 'view_expenses',
          'create_transactions', 'edit_transactions', 'create_deposits', 'edit_deposits',
          'view_reports', 'export_reports'
        ],
        cashier: [
          'view_transactions', 'create_deposits', 'edit_deposits', 'view_deposits'
        ],
        staff: [
          'view_transactions', 'view_deposits'
        ]
      }
    }
  },
  methods: {
    store() {
      this.form.post('/users')
    },
    handleRoleChange() {
      if (this.form.role === 'admin') {
        this.form.permissions = []
      } else {
        this.form.permissions = this.rolePermissions[this.form.role] || []
      }
    },
    formatGroupName(name) {
      return name.charAt(0).toUpperCase() + name.slice(1)
    },
    isPermissionDisabled(permission) {
      if (!this.form.role || this.form.role === 'admin') return true
      return !this.rolePermissions[this.form.role].includes(permission)
    }
  }
}
</script>
