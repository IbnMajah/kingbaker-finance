<template>
  <div>
    <Head title="Users" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Users</h1>
      <p class="text-gray-600">Manage system users and their permissions</p>
    </div>

    <!-- Actions and Filters -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex-1 mr-4">
        <div class="relative">
          <input
            v-model="form.search"
            type="text"
            placeholder="Search users..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="flex items-center space-x-4">
        <select
          v-model="form.role"
          class="pl-3 pr-8 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
        >
          <option value="">All Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.name">
            {{ formatRoleName(role.name) }}
          </option>
        </select>

        <select
          v-model="form.branch"
          class="pl-3 pr-8 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
        >
          <option value="">All Branches</option>
          <option v-for="branch in branches" :key="branch.id" :value="branch.id">
            {{ branch.name }}
          </option>
        </select>

        <Link
          v-if="canCreateUsers"
          href="/users/create"
          class="btn-kingbaker"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New User
        </Link>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="h-10 w-10 flex-shrink-0">
                  <img
                    v-if="user.photo_path"
                    :src="user.photo_path"
                    class="h-10 w-10 rounded-full object-cover"
                  />
                  <div
                    v-else
                    class="h-10 w-10 rounded-full bg-brand-100 flex items-center justify-center"
                  >
                    <span class="text-brand-600 font-medium text-sm">
                      {{ getInitials(user.first_name, user.last_name) }}
                    </span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ user.first_name }} {{ user.last_name }}
                  </div>
                  <div class="text-sm text-gray-500">{{ user.email }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="user.primary_role" class="flex flex-wrap gap-1">
                <span
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getRoleBadgeClass(user.primary_role)"
                >
                  {{ formatRoleName(user.primary_role) }}
                </span>
                <span v-if="user.roles && user.roles.length > 1" class="text-xs text-gray-500">
                  +{{ user.roles.length - 1 }} more
                </span>
              </div>
              <div v-else-if="user.role" class="flex flex-wrap gap-1">
                <span
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getRoleBadgeClass(user.role)"
                >
                  {{ formatRoleName(user.role) }}
                </span>
              </div>
              <span v-else class="text-gray-500 text-sm">No role</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                {{ getBranchName(user.branch_id) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ user.phone || '—' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="user.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              >
                {{ user.active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center justify-end space-x-3">
                <Link
                  v-if="canViewUsers"
                  :href="`/users/${user.id}`"
                  class="text-brand-600 hover:text-brand-900"
                >
                  View
                </Link>
                <Link
                  v-if="canEditUsers"
                  :href="`/users/${user.id}/edit`"
                  class="text-brand-600 hover:text-brand-900"
                >
                  Edit
                </Link>
                <button
                  v-if="!user.owner"
                  @click="confirmDelete(user)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
              No users found. <Link v-if="canCreateUsers" href="/users/create" class="text-brand-600 hover:text-brand-900">Create one</Link>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="users.length > 0 && pagination" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <Link
              :href="pagination.prev_page_url"
              :class="[!pagination.prev_page_url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50']"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white"
            >
              Previous
            </Link>
            <Link
              :href="pagination.next_page_url"
              :class="[!pagination.next_page_url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50']"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white"
            >
              Next
            </Link>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ pagination.from }}</span>
                to
                <span class="font-medium">{{ pagination.to }}</span>
                of
                <span class="font-medium">{{ pagination.total }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <Link
                  v-for="(link, i) in pagination.links"
                  :key="i"
                  :href="link.url"
                  :class="[
                    link.active ? 'bg-brand-50 border-brand-500 text-brand-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    i === 0 ? 'rounded-l-md' : '',
                    i === pagination.links.length - 1 ? 'rounded-r-md' : '',
                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                  ]"
                  class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                >
                  {{ link.label }}
                </Link>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="closeDeleteModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-2">
          Delete User
        </h2>
        <p class="text-sm text-gray-600 mb-4">
          Are you sure you want to delete this user? This action cannot be undone.
        </p>

        <div class="mt-6 flex justify-end space-x-3">
          <button
            type="button"
            class="px-3 py-2 rounded-md text-gray-700 hover:text-gray-900 focus:outline-none"
            @click="closeDeleteModal"
          >
            Cancel
          </button>
          <button
            type="button"
            class="px-3 py-2 rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none"
            :class="{ 'opacity-50 cursor-not-allowed': processing }"
            :disabled="processing"
            @click="deleteUser"
          >
            <span v-if="processing">Deleting...</span>
            <span v-else>Delete User</span>
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import { watch } from 'vue'
import Layout from '@/Shared/Layout.vue'
import Modal from '@/Shared/Modal.vue'
import { usePermissions } from '@/composables/usePermissions.js'
import debounce from 'lodash/debounce'

export default {
  components: {
    Head,
    Link,
    Modal,
  },
  layout: Layout,
  setup() {
    const { canCreateUsers, canViewUsers, canEditUsers } = usePermissions()
    return { canCreateUsers, canViewUsers, canEditUsers }
  },
  props: {
    users: {
      type: Array,
      required: true,
    },
    branches: {
      type: Array,
      required: true,
    },
    roles: {
      type: Array,
      required: true,
    },
    filters: {
      type: Object,
      default: () => ({}),
    },
    pagination: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      form: {
        search: this.filters.search || '',
        role: this.filters.role || '',
        branch: this.filters.branch || '',
      },
      showDeleteModal: false,
      processing: false,
      userToDelete: null,
    }
  },
  watch: {
    form: {
      deep: true,
      handler: debounce(function() {
        this.$inertia.get('/users', this.form, {
          preserveState: true,
          preserveScroll: true,
          replace: true,
        })
      }, 300),
    },
  },
  methods: {
    getInitials(firstName, lastName) {
      if (!firstName || !lastName) return '—'
      return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
    },
    formatRoleName(roleName) {
      if (!roleName) return '—'
      return roleName.charAt(0).toUpperCase() + roleName.slice(1).replace('_', ' ')
    },
    getRoleBadgeClass(role) {
      const classes = {
        admin: 'bg-purple-100 text-purple-800',
        finance_manager: 'bg-blue-100 text-blue-800',
        accountant: 'bg-green-100 text-green-800',
        sales_staff: 'bg-yellow-100 text-yellow-800',
        inventory_supervisor: 'bg-orange-100 text-orange-800',
        procurement_staff: 'bg-teal-100 text-teal-800',
        auditor: 'bg-indigo-100 text-indigo-800',
        branch_supervisor: 'bg-pink-100 text-pink-800',
        // Legacy roles fallback
        manager: 'bg-blue-100 text-blue-800',
        cashier: 'bg-yellow-100 text-yellow-800',
        staff: 'bg-gray-100 text-gray-800',
      }
      return classes[role] || 'bg-gray-100 text-gray-800'
    },
    getBranchName(branchId) {
      if (!branchId) return '—'
      const branch = this.branches.find(b => b.id === branchId)
      return branch ? branch.name : '—'
    },
    confirmDelete(user) {
      this.userToDelete = user
      this.showDeleteModal = true
    },
    closeDeleteModal() {
      this.showDeleteModal = false
      this.userToDelete = null
    },
    deleteUser() {
      if (!this.userToDelete) return

      this.processing = true
      this.$inertia.delete(`/users/${this.userToDelete.id}`, {
        onFinish: () => {
          this.processing = false
          this.closeDeleteModal()
        },
      })
    },
  },
}
</script>
