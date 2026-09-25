<template>
  <div>
    <Head :title="cashier.name" />

    <div class="mb-8">
      <div class="flex items-center gap-3">
        <Link href="/cashier-assignments" class="text-brand-500 hover:text-brand-700">Cashiers</Link>
        <span class="text-gray-400">/</span>
        <h1 class="text-3xl font-bold text-gray-900">{{ cashier.name }}</h1>
      </div>
      <p class="mt-2 text-gray-600">
        {{ cashier.employee_id }}
        <span v-if="cashier.email"> · {{ cashier.email }}</span>
        · {{ cashier.branch?.name || cashier.hr_branch_name || 'Unassigned branch' }}
        <span v-if="cashier.default_shift"> · {{ cashier.default_shift.name }}</span>
      </p>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-lg bg-white p-5 shadow">
        <p class="text-sm text-gray-500">Sales recorded</p>
        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ summary.sales_count }}</p>
      </div>
      <div class="rounded-lg bg-white p-5 shadow">
        <p class="text-sm text-gray-500">Sale totals</p>
        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ money(summary.total_amount) }}</p>
      </div>
      <div class="rounded-lg bg-white p-5 shadow">
        <p class="text-sm text-gray-500">Shortages</p>
        <p class="mt-1 text-2xl font-semibold text-red-600">{{ money(summary.shortages) }}</p>
      </div>
      <div class="rounded-lg bg-white p-5 shadow">
        <p class="text-sm text-gray-500">Surpluses</p>
        <p class="mt-1 text-2xl font-semibold text-green-600">{{ money(summary.surpluses) }}</p>
      </div>
    </div>

    <div class="mb-8 overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Sales</h2>
        <p class="mt-1 text-sm text-gray-500">All sales recorded for this cashier, including calculated shortage or surplus.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Branch / Shift</th>
              <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Expected</th>
              <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Actual</th>
              <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Variance</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm">
                <Link :href="`/sales/${sale.id}`" class="font-medium text-brand-600 hover:text-brand-800">
                  {{ formatDate(sale.sales_date) }}
                </Link>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">
                {{ sale.branch?.name || '—' }} · {{ sale.shift?.name || '—' }}
              </td>
              <td class="px-4 py-3 text-right text-sm">{{ sale.expected_total === null ? '—' : money(sale.expected_total) }}</td>
              <td class="px-4 py-3 text-right text-sm">{{ money(sale.total_amount) }}</td>
              <td class="px-4 py-3 text-right text-sm font-medium" :class="varianceClass(sale.variance)">
                <Link v-if="sale.reconciliation_id" :href="`/cashier-variances/${sale.reconciliation_id}`">
                  {{ sale.variance === null ? '—' : money(sale.variance) }}
                </Link>
                <span v-else>{{ sale.variance === null ? '—' : money(sale.variance) }}</span>
              </td>
              <td class="px-4 py-3 text-sm">
                <span v-if="sale.classification" class="mr-2 capitalize" :class="varianceClass(sale.variance)">{{ sale.classification }}</span>
                <span class="capitalize text-gray-500">{{ sale.status || 'Unreconciled' }}</span>
              </td>
            </tr>
            <tr v-if="!sales.data.length">
              <td colspan="6" class="px-4 py-10 text-center text-gray-500">No sales have been recorded for this cashier.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <pagination v-if="sales.links" class="border-t border-gray-200 px-4 py-3" :links="sales.links" />
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Variance ledger</h2>
        <p class="mt-1 text-sm text-gray-500">Approved shortage and surplus entries posted from this cashier's sales.</p>
      </div>
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
            <tr v-for="entry in ledger.data" :key="entry.id">
              <td class="px-4 py-3 text-sm">{{ formatDate(entry.entry_date) }}</td>
              <td class="px-4 py-3 text-sm capitalize">{{ title(entry.entry_type) }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ entry.branch_name }} · {{ entry.shift_name }}</td>
              <td class="max-w-md px-4 py-3 text-sm text-gray-600">{{ entry.reason || '—' }}</td>
              <td class="px-4 py-3 text-right text-sm font-medium" :class="varianceClass(entry.variance_amount)">
                {{ money(entry.variance_amount) }}
              </td>
            </tr>
            <tr v-if="!ledger.data.length">
              <td colspan="5" class="px-4 py-10 text-center text-gray-500">No approved ledger entries yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <pagination v-if="ledger.links" class="border-t border-gray-200 px-4 py-3" :links="ledger.links" />
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Pagination from '@/Shared/Pagination.vue'

export default {
  components: { Head, Link, Pagination },
  layout: Layout,
  props: {
    cashier: { type: Object, required: true },
    summary: { type: Object, required: true },
    sales: { type: Object, required: true },
    ledger: { type: Object, required: true },
  },
  methods: {
    money(value) {
      return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'GMD' }).format(Number(value || 0))
    },
    formatDate(value) {
      return value ? new Intl.DateTimeFormat().format(new Date(value)) : '—'
    },
    title(value) {
      return value ? value.replaceAll('_', ' ') : ''
    },
    varianceClass(value) {
      const amount = Number(value || 0)
      return amount > 0 ? 'text-red-600' : amount < 0 ? 'text-green-600' : 'text-gray-700'
    },
  },
}
</script>
