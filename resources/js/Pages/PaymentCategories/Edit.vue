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
              v-model="form.value"
              class="form-input"
              :class="{ error: form.errors.value }"
              :readonly="category.usage_count > 0"
              type="text"
            />
            <p v-if="category.usage_count > 0" class="mt-1 text-xs text-amber-600">
              This value cannot be changed because it is used by {{ category.usage_count }} payment{{ category.usage_count !== 1 ? 's' : '' }}.
            </p>
            <p v-else class="mt-1 text-xs text-gray-500">Use lowercase letters and underscores only.</p>
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
        value: this.category.value,
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
