<template>
  <div>
    <Head :title="`${cashierName || employeeId} Variance Ledger`" />

    <div class="mb-8">
      <Link href="/cashier-variances" class="text-brand-500 hover:text-brand-700">Cashier Variances</Link>
      <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ cashierName || employeeId }}</h1>
      <p class="mt-1 text-gray-600">Permanent variance ledger · {{ employeeId }}</p>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
      <div class="rounded-lg bg-white p-5 shadow"><p class="text-sm text-gray-500">Total shortages</p><p class="mt-1 text-2xl font-semibold text-red-600">{{ money(summary.shortages) }}</p></div>
      <div class="rounded-lg bg-white p-5 shadow"><p class="text-sm text-gray-500">Total surpluses</p><p class="mt-1 text-2xl font-semibold text-green-600">{{ money(summary.surpluses) }}</p></div>
      <div class="rounded-lg bg-white p-5 shadow"><p class="text-sm text-gray-500">Net variance</p><p class="mt-1 text-2xl font-semibold">{{ money(summary.net_variance) }}</p></div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Type</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Branch / Shift</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Reason</th>
              <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="entry in entries.data" :key="entry.id">
              <td class="px-4 py-4 text-sm">{{ formatDate(entry.entry_date) }}</td>
              <td class="px-4 py-4"><badge :variant="variant(entry.variance_amount)">{{ title(entry.entry_type) }}</badge></td>
              <td class="px-4 py-4 text-sm">{{ entry.branch_name }} · {{ entry.shift_name }}</td>
              <td class="max-w-md px-4 py-4 text-sm text-gray-600">{{ entry.reason || '—' }}</td>
              <td class="px-4 py-4 text-right font-medium" :class="Number(entry.variance_amount) > 0 ? 'text-red-600' : 'text-green-600'">
                {{ money(entry.variance_amount) }}
              </td>
            </tr>
            <tr v-if="!entries.data.length"><td colspan="5" class="px-4 py-10 text-center text-gray-500">No approved ledger entries.</td></tr>
          </tbody>
        </table>
      </div>
      <pagination class="border-t border-gray-200 px-4 py-3" :links="entries.links" />
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Badge from '@/Shared/Badge.vue'
import Layout from '@/Shared/Layout.vue'
import Pagination from '@/Shared/Pagination.vue'

export default {
  components: { Badge, Head, Link, Pagination },
  layout: Layout,
  props: {
    employeeId: { type: String, required: true },
    cashierName: { type: String, default: '' },
    summary: { type: Object, required: true },
    entries: { type: Object, required: true },
  },
  methods: {
    money(value) {
      return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'GMD' }).format(Number(value || 0))
    },
    formatDate(value) {
      return new Intl.DateTimeFormat().format(new Date(value))
    },
    title(value) {
      return value.charAt(0).toUpperCase() + value.slice(1)
    },
    variant(value) {
      return Number(value) > 0 ? 'danger' : Number(value) < 0 ? 'success' : 'default'
    },
  },
}
</script>
