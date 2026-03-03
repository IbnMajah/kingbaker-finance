<template>
  <div>
    <Head title="Edit Payment Category" />

    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/payment-categories">Payment Categories</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ category.label }}
      </h1>
    </div>

    <div class="max-w-lg bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="update">
        <div class="p-8 space-y-6">
          <div>
            <label class="form-label" for="label">Label:</label>
            <input
              id="label"
              v-model="form.label"
              class="form-input"
              :class="{ error: form.errors.label }"
              type="text"
            />
            <div v-if="form.errors.label" class="form-error">{{ form.errors.label }}</div>
          </div>

          <div>
            <label class="form-label" for="value">Value (slug):</label>
            <input
              id="value"
              :value="category.value"
              class="form-input bg-gray-50 text-gray-500"
              type="text"
              readonly
            />
            <p class="mt-1 text-xs text-gray-500">The slug cannot be changed after creation.</p>
          </div>

          <div>
            <label class="form-label" for="description">Description:</label>
            <input
              id="description"
              v-model="form.description"
              class="form-input"
              :class="{ error: form.errors.description }"
              type="text"
              placeholder="e.g. Payments to vendors for inventory purchases"
            />
            <p class="mt-1 text-xs text-gray-500">Shown below the category selector when creating a payment.</p>
            <div v-if="form.errors.description" class="form-error">{{ form.errors.description }}</div>
          </div>

          <div>
            <label class="form-label" for="description_placeholder">Description Placeholder:</label>
            <input
              id="description_placeholder"
              v-model="form.description_placeholder"
              class="form-input"
              :class="{ error: form.errors.description_placeholder }"
              type="text"
              placeholder="e.g. Describe the vendor payment (e.g., inventory purchase, supplies)"
            />
            <p class="mt-1 text-xs text-gray-500">Placeholder text for the description field when creating a payment.</p>
            <div v-if="form.errors.description_placeholder" class="form-error">{{ form.errors.description_placeholder }}</div>
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
            {{ form.processing ? 'Updating...' : 'Update Category' }}
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
    category: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      form: this.$inertia.form({
        label: this.category.label,
        description: this.category.description || '',
        description_placeholder: this.category.description_placeholder || '',
      }),
    }
  },
  methods: {
    update() {
      this.form.put(`/payment-categories/${this.category.id}`)
    },
  },
}
</script>
