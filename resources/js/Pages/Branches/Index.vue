<template>
  <div>
    <Head title="Branches" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Branches</h1>
      <p class="text-gray-600">Manage your business locations and their performance</p>
    </div>

    <!-- Actions and Filters -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex-1 mr-4">
        <div class="relative">
          <input
            v-model="form.search"
            type="text"
            placeholder="Search branches..."
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
          v-model="form.status"
          class="pl-3 pr-8 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
        >
          <option :value="null">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        <Link
          href="/branches/create"
          class="btn-kingbaker"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Branch
        </Link>
      </div>
    </div>

    <!-- Branches Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales Progress</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="branch in branches.data" :key="branch.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ branch.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ branch.location }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ branch.manager_name || '—' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ branch.phone || '—' }}</div>
              <div class="text-sm text-gray-500">{{ branch.email || '—' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex flex-col space-y-1">
                <div class="flex items-center">
                  <span class="text-xs text-gray-500 w-16">Daily</span>
                  <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-brand-500 rounded-full"
                      :style="{ width: `${branch.daily_progress}%` }"
                      :class="{ 'bg-red-500': branch.daily_progress < 50, 'bg-yellow-500': branch.daily_progress >= 50 && branch.daily_progress < 80 }"
                    ></div>
                  </div>
                  <span class="ml-2 text-xs text-gray-500">{{ branch.daily_progress }}%</span>
                </div>
                <div class="flex items-center">
                  <span class="text-xs text-gray-500 w-16">Monthly</span>
                  <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-brand-500 rounded-full"
                      :style="{ width: `${branch.monthly_progress}%` }"
                      :class="{ 'bg-red-500': branch.monthly_progress < 50, 'bg-yellow-500': branch.monthly_progress >= 50 && branch.monthly_progress < 80 }"
                    ></div>
                  </div>
                  <span class="ml-2 text-xs text-gray-500">{{ branch.monthly_progress }}%</span>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="branch.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              >
                {{ branch.active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center justify-end space-x-3">
                <Link
                  :href="`/branches/${branch.id}`"
                  class="text-brand-600 hover:text-brand-900"
                >
                  View
                </Link>
                <Link
                  v-if="!branch.deleted_at"
                  :href="`/branches/${branch.id}/edit`"
                  class="text-brand-600 hover:text-brand-900"
                >
                  Edit
                </Link>
                <button
                  v-if="!branch.deleted_at"
                  @click="confirmDelete(branch)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
                <button
                  v-if="branch.deleted_at"
                  @click="restore(branch)"
                  class="text-green-600 hover:text-green-900"
                >
                  Restore
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="branches.data.length === 0">
            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
              No branches found. <Link href="/branches/create" class="text-brand-600 hover:text-brand-900">Create one</Link>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="branches.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <Link
              :href="branches.prev_page_url"
              :class="[!branches.prev_page_url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50']"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white"
            >
              Previous
            </Link>
            <Link
              :href="branches.next_page_url"
              :class="[!branches.next_page_url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50']"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white"
            >
              Next
            </Link>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ branches.from }}</span>
                to
                <span class="font-medium">{{ branches.to }}</span>
                of
                <span class="font-medium">{{ branches.total }}</span>
                results
              </p>
            </div>
            <div>
              <div class="flex space-x-2">
                <Link
                  v-if="branches.prev_page_url"
                  :href="branches.prev_page_url"
                  class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                  preserve-state
                >
                  Previous
                </Link>
                <Link
                  v-if="branches.next_page_url"
                  :href="branches.next_page_url"
                  class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                  preserve-state
                >
                  Next
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
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
            @click="deleteBranch"
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
import { watch } from 'vue'
import Layout from '@/Shared/Layout.vue'
import Modal from '@/Shared/Modal.vue'
import debounce from 'lodash/debounce'

export default {
  components: {
    Head,
    Link,
    Modal,
  },
  layout: Layout,
  props: {
    branches: {
      type: Object,
      required: true,
    },
    filters: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        status: this.filters.status,
      },
      showDeleteModal: false,
      processing: false,
      branchToDelete: null,
    }
  },
  watch: {
    form: {
      deep: true,
      handler: debounce(function() {
        this.$inertia.get('/branches', this.form, {
          preserveState: true,
          preserveScroll: true,
          replace: true,
        })
      }, 300),
    },
  },
  methods: {
    confirmDelete(branch) {
      this.branchToDelete = branch
      this.showDeleteModal = true
    },
    closeDeleteModal() {
      this.showDeleteModal = false
      this.branchToDelete = null
    },
    deleteBranch() {
      if (!this.branchToDelete) return

      this.processing = true
      this.$inertia.delete(`/branches/${this.branchToDelete.id}`, {
        preserveState: true,
        onError: () => {
          this.processing = false
        },
        onFinish: () => {
          this.processing = false
          this.closeDeleteModal()
        },
      })
    },
    restore(branch) {
      if (confirm('Are you sure you want to restore this branch?')) {
        this.$inertia.put(`/branches/${branch.id}/restore`)
      }
    },
  },
}
</script>