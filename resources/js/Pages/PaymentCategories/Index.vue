<template>
  <div>
    <Head title="Payment Categories" />

    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">Payment Categories</h1>
      <p class="text-gray-600">Manage payment categories for cheque payments</p>
    </div>

    <div class="flex items-center justify-between mb-6">
      <div class="flex-1 mr-4">
        <div class="relative">
          <input
            v-model="form.search"
            type="text"
            placeholder="Search categories..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <Link
        href="/payment-categories/create"
        class="btn-kingbaker"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Category
      </Link>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value (Slug)</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ category.label }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <code class="text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ category.value }}</code>
            </td>
            <td class="px-6 py-4">
              <div v-if="category.description" class="text-sm text-gray-700 max-w-xs truncate">{{ category.description }}</div>
              <span v-else class="text-sm text-gray-400 italic">Not set</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="text-sm text-gray-500">{{ category.usage_count }} payment{{ category.usage_count !== 1 ? 's' : '' }}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center justify-end space-x-3">
                <Link
                  :href="`/payment-categories/${category.id}/edit`"
                  class="text-brand-600 hover:text-brand-900"
                >
                  Edit
                </Link>
                <button
                  @click="confirmDelete(category)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="categories.data.length === 0">
            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
              No categories found. <Link href="/payment-categories/create" class="text-brand-600 hover:text-brand-900">Create one</Link>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="categories.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ categories.from }}</span>
                to
                <span class="font-medium">{{ categories.to }}</span>
                of
                <span class="font-medium">{{ categories.total }}</span>
                results
              </p>
            </div>
            <div class="flex space-x-2">
              <Link
                v-if="categories.prev_page_url"
                :href="categories.prev_page_url"
                class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                preserve-state
              >
                Previous
              </Link>
              <Link
                v-if="categories.next_page_url"
                :href="categories.next_page_url"
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

    <Modal :show="showDeleteModal" @close="closeDeleteModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-2">Delete Category</h2>
        <p class="text-sm text-gray-600 mb-4">
          Are you sure you want to delete <strong>{{ categoryToDelete?.label }}</strong>? This action cannot be undone.
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
            @click="deleteCategory"
          >
            {{ processing ? 'Deleting...' : 'Delete Category' }}
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
import debounce from 'lodash/debounce'

export default {
  components: {
    Head,
    Link,
    Modal,
  },
  layout: Layout,
  props: {
    categories: {
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
      },
      showDeleteModal: false,
      processing: false,
      categoryToDelete: null,
    }
  },
  watch: {
    form: {
      deep: true,
      handler: debounce(function () {
        this.$inertia.get('/payment-categories', this.form, {
          preserveState: true,
          preserveScroll: true,
          replace: true,
        })
      }, 300),
    },
  },
  methods: {
    confirmDelete(category) {
      this.categoryToDelete = category
      this.showDeleteModal = true
    },
    closeDeleteModal() {
      this.showDeleteModal = false
      this.categoryToDelete = null
    },
    deleteCategory() {
      if (!this.categoryToDelete) return

      this.processing = true
      this.$inertia.delete(`/payment-categories/${this.categoryToDelete.id}`, {
        onSuccess: () => {
          this.processing = false
          this.closeDeleteModal()
        },
        onError: () => {
          this.processing = false
        },
        onFinish: () => {
          this.processing = false
        },
      })
    },
  },
}
</script>
