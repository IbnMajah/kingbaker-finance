<template>
  <div>
    <Head :title="`Edit ${user.first_name} ${user.last_name}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/users">Users</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ user.first_name }} {{ user.last_name }}
      </h1>
      <p class="text-gray-600">Edit user information and permissions</p>
    </div>

    <trashed-message v-if="user.deleted_at" class="mb-6" @restore="restore">
      This user has been deleted.
    </trashed-message>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="update">
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
                  {{ getInitials(form.first_name, form.last_name) }}
                </span>
              </div>
            </div>
            <div class="ml-6 flex-grow">
              <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold">Edit Profile</h2>

              </div>
            </div>
          </div>

          <!-- Form Sections -->
          <div class="space-y-8">
            <!-- Basic Information -->
            <div>
              <h3 class="text-lg font-medium mb-4">Basic Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <text-input
                  v-model="form.first_name"
                  :error="form.errors.first_name"
                  label="First Name"
                  required
                />
                <text-input
                  v-model="form.last_name"
                  :error="form.errors.last_name"
                  label="Last Name"
                  required
                />
              </div>
            </div>

            <!-- Contact Information -->
            <div>
              <h3 class="text-lg font-medium mb-4">Contact Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <text-input
                  v-model="form.email"
                  :error="form.errors.email"
                  type="email"
                  label="Email Address"
                  required
                />
                <text-input
                  v-model="form.phone"
                  :error="form.errors.phone"
                  type="tel"
                  label="Phone Number"
                />
              </div>
            </div>

            <!-- System Access -->
            <div>
              <h3 class="text-lg font-medium mb-4">System Access</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <select-input
                    v-model="form.role_name"
                    :error="form.errors.role_name"
                    label="Role"
                    required
                  >
                    <option value="">Select Role</option>
                    <option v-for="role in roles" :key="role.id" :value="role.name">
                      {{ formatRoleName(role.name) }}
                    </option>
                  </select-input>
                  <div v-if="getSelectedRoleDescription()" class="mt-1 text-sm text-gray-500">
                    {{ getSelectedRoleDescription() }}
                  </div>
                </div>
                <div>
                  <select-input
                    v-model="form.branch_id"
                    :error="form.errors.branch_id"
                    label="Branch"
                  >
                    <option value="">Select Branch</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                      {{ branch.name }}
                    </option>
                  </select-input>
                </div>
              </div>
            </div>

            <!-- Security -->
            <div>
              <h3 class="text-lg font-medium mb-4">Security</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <text-input
                  v-model="form.password"
                  :error="form.errors.password"
                  type="password"
                  label="New Password"
                  autocomplete="new-password"
                  help="Leave blank to keep current password"
                />
                <select-input
                  v-model="form.active"
                  :error="form.errors.active"
                  label="Account Status"
                >
                  <option :value="true">Active</option>
                  <option :value="false">Inactive</option>
                </select-input>
              </div>
            </div>

            <!-- Role Information -->
            <div v-if="form.role_name" class="border rounded-md p-4 bg-gray-50">
              <h3 class="text-lg font-medium text-gray-900 mb-2">Role Information</h3>
              <p class="text-sm text-gray-600 mb-3">
                This user will have the <strong>{{ formatRoleName(form.role_name) }}</strong> role.
              </p>
              <p class="text-sm text-gray-500">
                {{ getSelectedRoleDescription() }}
              </p>
              <p class="text-sm text-gray-500 mt-2">
                <em>Permissions are automatically assigned based on the selected role.</em>
              </p>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <button
              v-if="!user.deleted_at && !user.owner"
              type="button"
              class="btn-red"
              @click="confirmDelete"
            >
              Delete User
            </button>
          </div>
          <div class="flex items-center space-x-3">
            <Link
              href="/users"
              class="text-gray-600 hover:text-gray-800"
            >
              Cancel
            </Link>
            <loading-button
              :loading="form.processing"
              class="btn-kingbaker"
              type="submit"
            >
              Update User
            </loading-button>
          </div>
        </div>
      </form>
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
import TextInput from '@/Shared/TextInput.vue'
import SelectInput from '@/Shared/SelectInput.vue'
import FileInput from '@/Shared/FileInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import TrashedMessage from '@/Shared/TrashedMessage.vue'
import Modal from '@/Shared/Modal.vue'

export default {
  components: {
    Head,
    Link,
    TextInput,
    SelectInput,
    FileInput,
    LoadingButton,
    TrashedMessage,
    Modal,
  },
  layout: Layout,
  props: {
    user: {
      type: Object,
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
  },
  data() {
    return {
      form: this.$inertia.form({
        first_name: this.user.first_name,
        last_name: this.user.last_name,
        email: this.user.email,
        phone: this.user.phone,
        role_name: this.user.current_role || '',
        branch_id: this.user.branch_id,
        password: '',
        photo: null,
        active: this.user.active,
      }),
      showDeleteModal: false,
      processing: false
    }
  },

  methods: {
    getInitials(firstName, lastName) {
      return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
    },
    update() {
      this.form.put(`/users/${this.user.id}`, {
        onSuccess: () => this.form.reset('password', 'photo'),
        onError: (errors) => {
          console.log('Validation errors:', errors);
        },
        preserveScroll: true
      })
    },
    formatRoleName(roleName) {
      if (!roleName) return ''
      return roleName.charAt(0).toUpperCase() + roleName.slice(1).replace('_', ' ')
    },
    getSelectedRoleDescription() {
      if (!this.form.role_name) return ''
      const selectedRole = this.roles.find(role => role.name === this.form.role_name)
      return selectedRole ? selectedRole.description : ''
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
    restore() {
      if (confirm('Are you sure you want to restore this user?')) {
        this.$inertia.put(`/users/${this.user.id}/restore`)
      }
    },
  },
}
</script>
