<template>
  <div>
    <Head :title="`${user.first_name} ${user.last_name}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/users">Users</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ user.first_name }} {{ user.last_name }}
      </h1>
      <p class="text-gray-600">View user details and information</p>
    </div>

    <!-- User Details Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="p-6">
        <!-- User Header -->
        <div class="flex items-center mb-6">
          <div class="h-20 w-20 flex-shrink-0">
            <img
              v-if="user.photo_path"
              :src="user.photo_path"
              class="h-20 w-20 rounded-full object-cover"
              :alt="user.first_name"
            />
            <div
              v-else
              class="h-20 w-20 rounded-full bg-brand-100 flex items-center justify-center"
            >
              <span class="text-brand-600 font-medium text-xl">
                {{ getInitials(user.first_name, user.last_name) }}
              </span>
            </div>
          </div>
          <div class="ml-6">
            <h2 class="text-2xl font-semibold">
              {{ user.first_name }} {{ user.last_name }}
            </h2>
            <div class="mt-1 flex items-center">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="getRoleBadgeClass(user.role)"
              >
                {{ formatRole(user.role) }}
              </span>
              <span
                class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="user.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              >
                {{ user.active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
        </div>

        <!-- User Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-medium mb-4">Contact Information</h3>
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-500">Email</label>
                <p class="mt-1">{{ user.email }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Phone</label>
                <p class="mt-1">{{ user.phone || '—' }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Branch</label>
                <p class="mt-1">{{ user.branch ? user.branch.name : '—' }}</p>
              </div>
            </div>
          </div>

          <div>
            <h3 class="text-lg font-medium mb-4">System Information</h3>
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-500">Role</label>
                <p class="mt-1">{{ formatRole(user.role) }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Status</label>
                <p class="mt-1">{{ user.active ? 'Active' : 'Inactive' }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Account Type</label>
                <p class="mt-1">{{ user.owner ? 'Owner' : 'Regular User' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Permissions Section -->
        <div class="mt-8" v-if="user.permissions && user.permissions.length > 0">
          <h3 class="text-lg font-medium mb-4">Permissions</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="permission in user.permissions"
              :key="permission"
              class="px-3 py-2 bg-gray-50 rounded-md text-sm"
            >
              {{ formatPermission(permission) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
        <div class="flex space-x-3">
          <Link
            :href="`/users/${user.id}/edit`"
            class="btn-kingbaker"
          >
            Edit User
          </Link>
          <button
            v-if="!user.owner && !user.deleted_at"
            @click="confirmDelete"
            class="btn-red"
          >
            Delete User
          </button>
        </div>
        <Link
          href="/users"
          class="text-gray-600 hover:text-gray-800"
        >
          <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          </svg>
          Back to Users
        </Link>
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
import Layout from '@/Shared/Layout.vue'
import Modal from '@/Shared/Modal.vue'

export default {
  components: {
    Head,
    Link,
    Modal,
  },
  layout: Layout,
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      showDeleteModal: false,
      processing: false,
    }
  },
  methods: {
    getInitials(firstName, lastName) {
      return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
    },
    formatRole(role) {
      if (!role) return '—'
      return role.charAt(0).toUpperCase() + role.slice(1)
    },
    getRoleBadgeClass(role) {
      const classes = {
        admin: 'bg-purple-100 text-purple-800',
        manager: 'bg-blue-100 text-blue-800',
        accountant: 'bg-green-100 text-green-800',
        cashier: 'bg-yellow-100 text-yellow-800',
        staff: 'bg-gray-100 text-gray-800',
      }
      return classes[role] || 'bg-gray-100 text-gray-800'
    },
    formatPermission(permission) {
      return permission
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
    },
    confirmDelete() {
      this.showDeleteModal = true
    },
    closeDeleteModal() {
      this.showDeleteModal = false
      this.processing = false
    },
    deleteUser() {
      this.processing = true
      this.$inertia.delete(`/users/${this.user.id}`, {
        onFinish: () => {
          this.processing = false
          this.closeDeleteModal()
        },
      })
    },
  },
}
</script>