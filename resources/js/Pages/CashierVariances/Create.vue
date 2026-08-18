<template>
  <div>
    <Head title="Record Cashier Reconciliation" />

    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Record Cashier Reconciliation</h1>
      <p class="mt-2 text-gray-600">
        Select a recorded sale. Expected total comes from the sale; enter the actual amount counted at closing.
      </p>
    </div>

    <form class="overflow-hidden rounded-lg bg-white shadow" @submit.prevent="store">
      <div class="space-y-8 p-6">
        <div>
          <label class="form-label">Sale</label>
          <select v-model="form.sale_id" class="form-select" :class="{ error: form.errors.sale_id }">
            <option value="">Select a sale</option>
            <option v-for="sale in sales" :key="sale.id" :value="sale.id">
              {{ sale.sales_date }} · {{ sale.cashier }} · {{ sale.branch }} / {{ sale.shift }} · Expected {{ formatMoney(sale.total_amount) }}
            </option>
          </select>
          <div v-if="form.errors.sale_id" class="form-error">{{ form.errors.sale_id }}</div>
          <p v-if="!sales.length" class="mt-2 text-sm text-gray-500">
            No unreconciled sales with an assigned cashier. Record the sale first.
          </p>
        </div>

        <div v-if="selectedSale" class="grid gap-6 md:grid-cols-3">
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Expected total</p>
            <p class="mt-1 text-xl font-semibold">{{ formatMoney(selectedSale.total_amount) }}</p>
          </div>
          <text-input
            v-model="form.actual_total"
            :error="form.errors.actual_total"
            label="Actual Reconciled Total"
            type="number"
            step="0.01"
            min="0"
          />
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Expected − Actual</p>
            <p class="mt-1 text-xl font-semibold">{{ formatMoney(calculatedVariance) }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ classificationLabel }}</p>
          </div>
        </div>

        <textarea-input v-model="form.comments" :error="form.errors.comments" label="Comments" />
      </div>

      <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <Link href="/cashier-variances" class="text-gray-600 hover:text-gray-900">Cancel</Link>
        <div class="flex gap-3">
          <loading-button :loading="form.processing && !form.submit" class="btn-indigo" type="button" @click="save(false)">
            Save Draft
          </loading-button>
          <loading-button :loading="form.processing && form.submit" class="btn-kingbaker" type="button" @click="save(true)">
            Submit for Approval
          </loading-button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import TextareaInput from '@/Shared/TextareaInput.vue'
import TextInput from '@/Shared/TextInput.vue'

export default {
  components: { Head, Link, LoadingButton, TextareaInput, TextInput },
  layout: Layout,
  props: {
    sales: { type: Array, required: true },
  },
  data() {
    return {
      form: this.$inertia.form({
        sale_id: '',
        actual_total: '',
        comments: '',
        submit: false,
      }),
    }
  },
  computed: {
    selectedSale() {
      return this.sales.find(sale => String(sale.id) === String(this.form.sale_id))
    },
    calculatedVariance() {
      if (!this.selectedSale) return 0
      return Math.round(((Number(this.selectedSale.total_amount) || 0) - (Number(this.form.actual_total) || 0)) * 100) / 100
    },
    classificationLabel() {
      if (this.calculatedVariance > 0) return 'Shortage'
      if (this.calculatedVariance < 0) return 'Surplus'
      return 'Balanced'
    },
  },
  methods: {
    save(submit) {
      this.form.submit = submit
      this.form.post('/cashier-variances')
    },
    formatMoney(value) {
      return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'GMD' }).format(Number(value || 0))
    },
  },
}
</script>
