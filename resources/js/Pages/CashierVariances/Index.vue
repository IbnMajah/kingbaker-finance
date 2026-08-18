<template>
  <div>
    <Head title="Cashier Variances" />

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Cashier Variances</h1>
        <p class="mt-2 text-gray-600">Review shortages and surpluses calculated from recorded sales.</p>
      </div>
      <div class="flex flex-wrap gap-2 self-start">
        <a v-if="canExport" :href="exportUrl('pdf')" class="btn-indigo">PDF</a>
        <a v-if="canExport" :href="exportUrl('xlsx')" class="btn-indigo">Excel</a>
        <a v-if="canExport" :href="exportUrl('csv')" class="btn-indigo">CSV</a>
        <Link v-if="canCreate" href="/sales/create" class="btn-kingbaker">Record Sale</Link>
      </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
      <div class="rounded-lg bg-white p-5 shadow">
        <p class="text-sm text-gray-500">Shortages</p>
        <p class="mt-1 text-2xl font-semibold text-red-600">{{ money(summary.shortages) }}</p>
      </div>
      <div class="rounded-lg bg-white p-5 shadow">
        <p class="text-sm text-gray-500">Surpluses</p>
        <p class="mt-1 text-2xl font-semibold text-green-600">{{ money(summary.surpluses) }}</p>
      </div>
      <div class="rounded-lg bg-white p-5 shadow">
        <p class="text-sm text-gray-500">Net variance</p>
        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ money(summary.net_variance) }}</p>
      </div>
    </div>

    <div v-if="alerts.highValueCount || alerts.repeatedCashiers.length" class="mb-6 rounded-lg border border-yellow-200 bg-yellow-50 p-4">
      <h2 class="font-semibold text-yellow-900">Variance attention required</h2>
      <p v-if="alerts.highValueCount" class="mt-1 text-sm text-yellow-800">{{ alerts.highValueCount }} high-value approved shortage(s).</p>
      <p v-if="alerts.repeatedCashiers.length" class="mt-1 text-sm text-yellow-800">
        Repeated shortages: {{ alerts.repeatedCashiers.map(item => `${item.cashier_name} (${item.shortage_count})`).join(', ') }}
      </p>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
      <div class="rounded-lg bg-white p-5 shadow">
        <h2 class="mb-4 font-semibold text-gray-900">Daily summary</h2>
        <div class="space-y-2">
          <div class="grid grid-cols-4 gap-2 text-xs font-medium uppercase text-gray-500">
            <span>Period</span><span class="text-right">Shortage</span><span class="text-right">Surplus</span><span class="text-right">Net</span>
          </div>
          <div v-for="row in trends.daily.slice(-7).reverse()" :key="row.period" class="grid grid-cols-4 gap-2 text-sm">
            <span>{{ row.period }}</span>
            <span class="text-right text-red-600">{{ money(row.shortages) }}</span>
            <span class="text-right text-green-600">{{ money(row.surpluses) }}</span>
            <span class="text-right font-medium">{{ money(row.net) }}</span>
          </div>
          <p v-if="!trends.daily.length" class="text-sm text-gray-500">No approved variances in the last 14 days.</p>
        </div>
      </div>
      <div class="rounded-lg bg-white p-5 shadow">
        <h2 class="mb-4 font-semibold text-gray-900">Monthly summary</h2>
        <div class="space-y-2">
          <div class="grid grid-cols-4 gap-2 text-xs font-medium uppercase text-gray-500">
            <span>Period</span><span class="text-right">Shortage</span><span class="text-right">Surplus</span><span class="text-right">Net</span>
          </div>
          <div v-for="row in trends.monthly.slice(-6).reverse()" :key="row.period" class="grid grid-cols-4 gap-2 text-sm">
            <span>{{ row.period }}</span>
            <span class="text-right text-red-600">{{ money(row.shortages) }}</span>
            <span class="text-right text-green-600">{{ money(row.surpluses) }}</span>
            <span class="text-right font-medium">{{ money(row.net) }}</span>
          </div>
          <p v-if="!trends.monthly.length" class="text-sm text-gray-500">No approved variances in the last year.</p>
        </div>
      </div>
    </div>

    <form class="mb-6 rounded-lg bg-white p-4 shadow" @submit.prevent="filter">
      <div class="grid gap-3 md:grid-cols-4 xl:grid-cols-8">
        <select v-model="form.cashier" class="form-select">
          <option value="">All cashiers</option>
          <option v-for="cashier in cashiers" :key="cashier.employee_id" :value="cashier.employee_id">{{ cashier.name }}</option>
        </select>
        <select v-model="form.branch" class="form-select">
          <option value="">All branches</option>
          <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
        </select>
        <select v-model="form.shift" class="form-select">
          <option value="">All shifts</option>
          <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
        </select>
        <select v-model="form.status" class="form-select">
          <option value="">All statuses</option>
          <option v-for="status in statuses" :key="status" :value="status">{{ title(status) }}</option>
        </select>
        <select v-model="form.classification" class="form-select">
          <option value="">All classifications</option>
          <option value="shortage">Shortage</option>
          <option value="surplus">Surplus</option>
          <option value="balanced">Balanced</option>
        </select>
        <input v-model="form.from" type="date" class="form-input" aria-label="From date" />
        <input v-model="form.to" type="date" class="form-input" aria-label="To date" />
        <div class="flex gap-2">
          <button class="btn-kingbaker" type="submit">Filter</button>
          <button class="px-3 py-2 text-sm text-gray-600" type="button" @click="reset">Reset</button>
        </div>
      </div>
    </form>

    <div class="overflow-hidden rounded-lg bg-white shadow">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Date / Reference</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Cashier</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Branch / Shift</th>
              <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Variance</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in reconciliations.data" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-4">
                <Link :href="`/cashier-variances/${item.id}`" class="font-medium text-brand-600 hover:text-brand-800">
                  {{ item.sale_id ? `Sale #${item.sale_id}` : (item.pos_reference || `Variance #${item.id}`) }}
                </Link>
                <p class="text-sm text-gray-500">{{ formatDate(item.business_date) }}</p>
              </td>
              <td class="px-4 py-4">
                <Link :href="`/cashiers/${item.hr_cashier_id}`" class="text-gray-900 hover:text-brand-600">
                  {{ item.cashier_name }}
                </Link>
                <p class="text-sm text-gray-500">{{ item.cashier_employee_id }}</p>
              </td>
              <td class="px-4 py-4 text-sm text-gray-700">{{ item.branch_name }} · {{ item.shift_name }}</td>
              <td class="px-4 py-4 text-right">
                <span :class="Number(item.calculated_variance) > 0 ? 'text-red-600' : Number(item.calculated_variance) < 0 ? 'text-green-600' : 'text-gray-700'">
                  {{ money(item.calculated_variance) }}
                </span>
              </td>
              <td class="px-4 py-4">
                <badge :variant="statusVariant(item.status)">{{ title(item.status) }}</badge>
              </td>
            </tr>
            <tr v-if="!reconciliations.data.length">
              <td colspan="5" class="px-4 py-10 text-center text-gray-500">No cashier reconciliations found.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <pagination class="border-t border-gray-200 px-4 py-3" :links="reconciliations.links" />
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Badge from '@/Shared/Badge.vue'
import Layout from '@/Shared/Layout.vue'
import Pagination from '@/Shared/Pagination.vue'
import { usePermissions } from '@/composables/usePermissions.js'

export default {
  components: { Badge, Head, Link, Pagination },
  layout: Layout,
  props: {
    reconciliations: { type: Object, required: true },
    filters: { type: Object, required: true },
    summary: { type: Object, required: true },
    alerts: { type: Object, required: true },
    trends: { type: Object, required: true },
    cashiers: { type: Array, required: true },
    branches: { type: Array, required: true },
    shifts: { type: Array, required: true },
  },
  setup() {
    const { hasPermission } = usePermissions()
    return { hasPermission }
  },
  data() {
    return {
      statuses: ['draft', 'submitted', 'approved', 'rejected', 'reversed'],
      form: {
        cashier: this.filters.cashier || '',
        branch: this.filters.branch || '',
        shift: this.filters.shift || '',
        status: this.filters.status || '',
        classification: this.filters.classification || '',
        from: this.filters.from || '',
        to: this.filters.to || '',
      },
    }
  },
  computed: {
    canCreate() {
      return this.hasPermission('create_cashier_variances')
    },
    canExport() {
      return this.hasPermission('export_cashier_variances')
    },
  },
  methods: {
    filter() {
      this.$inertia.get('/cashier-variances', this.form, { preserveState: true, replace: true })
    },
    reset() {
      Object.keys(this.form).forEach(key => { this.form[key] = '' })
      this.filter()
    },
    money(value) {
      return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'GMD' }).format(Number(value || 0))
    },
    formatDate(value) {
      return new Intl.DateTimeFormat().format(new Date(value))
    },
    title(value) {
      return value.charAt(0).toUpperCase() + value.slice(1)
    },
    statusVariant(status) {
      return { approved: 'success', submitted: 'warning', rejected: 'danger', reversed: 'default' }[status] || 'info'
    },
    exportUrl(format) {
      const params = new URLSearchParams({ ...this.form, format })
      return `/cashier-variances/export?${params.toString()}`
    },
  },
}
</script>
