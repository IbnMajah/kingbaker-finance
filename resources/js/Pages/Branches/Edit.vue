<template>
  <div>
    <Head title="Edit Branch" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/branches">Branches</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ branch.name }}
      </h1>
      <p class="text-gray-600">Edit branch information</p>
    </div>

    <trashed-message v-if="branch.deleted_at" class="mb-6" @restore="restore">
      This branch has been deleted.
    </trashed-message>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Branch Information</h2>
      </div>

      <form @submit.prevent="update">
        <div class="p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name *</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.name ? 'border-red-500' : ''"
                placeholder="Enter branch name"
              />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
              <input
                v-model="form.location"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.location ? 'border-red-500' : ''"
                placeholder="Enter branch location"
              />
              <div v-if="form.errors.location" class="mt-1 text-sm text-red-600">{{ form.errors.location }}</div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Manager Name</label>
              <input
                v-model="form.manager_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.manager_name ? 'border-red-500' : ''"
                placeholder="Enter manager name"
              />
              <div v-if="form.errors.manager_name" class="mt-1 text-sm text-red-600">{{ form.errors.manager_name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Branch Status</label>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input
                v-model="form.phone"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.phone ? 'border-red-500' : ''"
                placeholder="Enter phone number"
              />
              <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.email ? 'border-red-500' : ''"
                placeholder="Enter email address"
              />
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
            </div>
          </div>

          <!-- Sales Targets -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Daily Sales Target</label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                <input
                  v-model="form.daily_sales_target"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.daily_sales_target ? 'border-red-500' : ''"
                  placeholder="0.00"
                />
              </div>
              <div v-if="form.errors.daily_sales_target" class="mt-1 text-sm text-red-600">{{ form.errors.daily_sales_target }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Sales Target</label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                <input
                  v-model="form.monthly_sales_target"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.monthly_sales_target ? 'border-red-500' : ''"
                  placeholder="0.00"
                />
              </div>
              <div v-if="form.errors.monthly_sales_target" class="mt-1 text-sm text-red-600">{{ form.errors.monthly_sales_target }}</div>
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
              placeholder="Enter branch description"
            ></textarea>
            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <button
            type="button"
            class="text-red-600 hover:text-red-900 focus:outline-none"
            @click="destroy"
          >
            Delete Branch
          </button>
          <div class="flex items-center space-x-3">
            <Link
              href="/branches"
              class="px-3 py-2 rounded-md text-gray-700 hover:text-gray-900 focus:outline-none"
            >
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
              {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="closeDeleteModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-2">
          Delete Branch
        </h2>
        <p class="text-sm text-gray-600 mb-4">
          Are you sure you want to delete this branch? This action cannot be undone.
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
            @click="confirmDelete"
          >
            <span v-if="processing">Deleting...</span>
            <span v-else>Delete Branch</span>
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
import TrashedMessage from '@/Shared/TrashedMessage.vue'

export default {
  components: {
    Head,
    Link,
    Modal,
    TrashedMessage,
  },
  layout: Layout,
  props: {
    branch: {
      type: Object,
      required: true,
    },
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        name: this.branch.name,
        location: this.branch.location,
        phone: this.branch.phone,
        email: this.branch.email,
        manager_name: this.branch.manager_name,
        description: this.branch.description,
        daily_sales_target: this.branch.daily_sales_target,
        monthly_sales_target: this.branch.monthly_sales_target,
        active: this.branch.active,
      }),
      showDeleteModal: false,
      processing: false,
    }
  },
  methods: {
    update() {
      this.form.put(`/branches/${this.branch.id}`)
    },
    destroy() {
      this.showDeleteModal = true
    },
    closeDeleteModal() {
      this.showDeleteModal = false
    },
    confirmDelete() {
      this.processing = true
      this.$inertia.delete(`/branches/${this.branch.id}`, {
        onFinish: () => {
          this.processing = false
          this.closeDeleteModal()
        },
      })
    },
    restore() {
      if (confirm('Are you sure you want to restore this branch?')) {
        this.$inertia.put(`/branches/${this.branch.id}/restore`)
      }
    },
  },
}
</script>