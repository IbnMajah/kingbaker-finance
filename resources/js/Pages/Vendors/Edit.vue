<template>
  <div>
    <Head title="Edit Vendor" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/vendors">Vendors</Link>
        <span class="text-brand-400 font-medium">/</span>
        Edit
      </h1>
      <p class="text-gray-600">Update vendor information</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Vendor Information</h2>
      </div>

      <form @submit.prevent="update">
        <div class="p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Name *</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.name ? 'border-red-500' : ''"
                placeholder="Enter vendor name"
              />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
              <input
                v-model="form.contact_person"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.contact_person ? 'border-red-500' : ''"
                placeholder="Enter contact person name"
              />
              <div v-if="form.errors.contact_person" class="mt-1 text-sm text-red-600">{{ form.errors.contact_person }}</div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
          </div>

          <!-- Vendor Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Type</label>
              <select
                v-model="form.vendor_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.vendor_type ? 'border-red-500' : ''"
              >
                <option value="">Select vendor type</option>
                <option v-for="type in vendorTypes" :key="type" :value="type">
                  {{ type.replace('_', ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') }}
                </option>
              </select>
              <div v-if="form.errors.vendor_type" class="mt-1 text-sm text-red-600">{{ form.errors.vendor_type }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
              <select
                v-model="form.organization_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.organization_id ? 'border-red-500' : ''"
              >
                <option value="">Select organization (optional)</option>
                <option v-for="org in organizations" :key="org.id" :value="org.id">
                  {{ org.name }}
                </option>
              </select>
              <div v-if="form.errors.organization_id" class="mt-1 text-sm text-red-600">{{ form.errors.organization_id }}</div>
            </div>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea
              v-model="form.address"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.address ? 'border-red-500' : ''"
              placeholder="Enter vendor address"
            ></textarea>
            <div v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.description ? 'border-red-500' : ''"
              placeholder="Enter vendor description or notes"
            ></textarea>
            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
          </div>

          <!-- Status -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
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
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link href="/vendors" class="text-gray-600 hover:text-gray-800">
              <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
              </svg>
              Cancel
            </Link>
            <button
              @click="destroy"
              type="button"
              class="text-red-600 hover:text-red-800"
            >
              Delete Vendor
            </button>
          </div>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-kingbaker flex items-center"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Updating...' : 'Update Vendor' }}
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
    vendor: {
      type: Object,
      required: true
    },
    organizations: {
      type: Array,
      default: () => []
    },
    vendorTypes: {
      type: Array,
      default: () => []
    }
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        name: this.vendor.name,
        contact_person: this.vendor.contact_person,
        email: this.vendor.email,
        phone: this.vendor.phone,
        address: this.vendor.address,
        vendor_type: this.vendor.vendor_type,
        description: this.vendor.description,
        organization_id: this.vendor.organization_id,
        active: this.vendor.active,
      }),
    }
  },
  methods: {
    update() {
      this.form.put(`/vendors/${this.vendor.id}`)
    },
    destroy() {
      if (confirm('Are you sure you want to delete this vendor?')) {
        this.$inertia.delete(`/vendors/${this.vendor.id}`)
      }
    },
  },
}
</script>