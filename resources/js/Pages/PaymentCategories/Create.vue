<template>
  <div>
    <Head title="Create Payment Category" />

    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/payment-categories">Payment Categories</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create
      </h1>
    </div>

    <div class="max-w-lg bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="store">
        <div class="p-8 space-y-6">
          <div>
            <label class="form-label" for="label">Label:</label>
            <input
              id="label"
              v-model="form.label"
              class="form-input"
              :class="{ error: form.errors.label }"
              type="text"
              placeholder="e.g. Vendor Payment"
              @input="generateValue"
            />
            <div v-if="form.errors.label" class="form-error">{{ form.errors.label }}</div>
          </div>

          <div>
            <label class="form-label" for="value">Value (slug):</label>
            <input
              id="value"
              v-model="form.value"
              class="form-input"
              :class="{ error: form.errors.value }"
              type="text"
              placeholder="e.g. vendor_payment"
            />
            <p class="mt-1 text-xs text-gray-500">Auto-generated from label. Use lowercase letters and underscores only.</p>
            <div v-if="form.errors.value" class="form-error">{{ form.errors.value }}</div>
          </div>
        </div>

        <div class="flex items-center justify-end px-8 py-4 bg-gray-50 border-t border-gray-100 space-x-3">
          <Link href="/payment-categories" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">
            Cancel
          </Link>
          <button
            type="submit"
            class="btn-kingbaker"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Creating...' : 'Create Category' }}
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
  data() {
    return {
      form: this.$inertia.form({
        label: '',
        value: '',
      }),
    }
  },
  methods: {
    generateValue() {
      this.form.value = this.form.label
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, '')
        .replace(/\s+/g, '_')
        .replace(/_+/g, '_')
        .replace(/^_|_$/g, '')
    },
    store() {
      this.form.post('/payment-categories')
    },
  },
}
</script>
