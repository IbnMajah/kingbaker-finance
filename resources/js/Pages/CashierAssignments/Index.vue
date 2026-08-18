<template>
  <div>
    <Head title="Cashier Assignments" />

    <div class="mb-8">
      <h1 class="mb-2 text-3xl font-bold">Cashiers</h1>
      <p class="text-gray-600">Assign cached HR cashiers to Finance branches and default shifts.</p>
      <p class="mt-2 text-sm" :class="cache.stale ? 'text-amber-700' : 'text-green-700'">
        {{ cacheMessage }}
      </p>
    </div>

    <div class="mb-6 flex items-center justify-between">
      <div class="mr-4 flex-1">
        <div class="relative">
          <input
            v-model="form.search"
            type="text"
            placeholder="Search cashiers..."
            class="w-full rounded-md border border-gray-300 py-2 pl-10 pr-4 focus:border-brand-500 focus:outline-none focus:ring-brand-500"
          />
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="flex items-center space-x-4">
        <select
          v-model="form.status"
          class="rounded-md border border-gray-300 py-2 pl-3 pr-8 focus:border-brand-500 focus:outline-none focus:ring-brand-500"
        >
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        <button class="btn-kingbaker" :disabled="syncing" @click="sync">
          {{ syncing ? 'Refreshing…' : 'Refresh from HR' }}
        </button>
      </div>
    </div>

    <div class="overflow-x-auto rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Cashier</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">HR branch</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Finance branch</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Default shift</th>
            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="cashier in cashiers.data" :key="cashier.id" :class="{ 'bg-gray-50 opacity-75': cashier.status !== 'active' }">
            <td class="px-4 py-3">
              <Link :href="`/cashiers/${cashier.id}`" class="font-medium text-brand-600 hover:text-brand-800">{{ cashier.name }}</Link>
              <div class="text-xs text-gray-500">{{ cashier.employee_id }} · {{ cashier.email || 'No email' }}</div>
              <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs" :class="cashier.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'">
                {{ cashier.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-700">
              {{ cashier.hr_branch_name || 'Unassigned' }}
              <span v-if="cashier.hr_branch_code" class="block text-xs text-gray-500">{{ cashier.hr_branch_code }}</span>
            </td>
            <td class="px-4 py-3">
              <select v-model="cashier.branch_id" :disabled="cashier.status !== 'active'" class="w-full rounded-md border-gray-300 text-sm disabled:bg-gray-100">
                <option :value="null">Not assigned</option>
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
              </select>
            </td>
            <td class="px-4 py-3">
              <select v-model="cashier.default_shift_id" :disabled="cashier.status !== 'active'" class="w-full rounded-md border-gray-300 text-sm disabled:bg-gray-100">
                <option :value="null">No default</option>
                <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                  {{ shift.name }} ({{ shortTime(shift.start_time) }}–{{ shortTime(shift.end_time) }})
                </option>
              </select>
            </td>
            <td class="px-4 py-3 text-right">
              <button v-if="cashier.status === 'active'" class="text-sm font-medium text-brand-600 hover:text-brand-800" @click="save(cashier)">Save</button>
              <span v-else class="text-xs text-gray-500">Historical</span>
            </td>
          </tr>
          <tr v-if="cashiers.data.length === 0">
            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No cached cashiers match these filters.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex justify-end gap-2">
      <Link v-for="link in cashiers.links" :key="link.label" :href="link.url || '#'" preserve-state
        class="rounded border px-3 py-2 text-sm" :class="{ 'bg-brand-600 text-white': link.active, 'pointer-events-none opacity-50': !link.url }"
        v-html="link.label" />
    </div>
  </div>
</template>

<script>
import { Head, Link, router } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'
import Layout from '@/Shared/Layout.vue'

export default {
  components: { Head, Link },
  layout: Layout,
  props: {
    cashiers: Object,
    branches: Array,
    shifts: Array,
    filters: Object,
    cache: Object,
  },
  data() {
    return {
      form: { search: this.filters.search || '', status: this.filters.status || '' },
      syncing: false,
    }
  },
  computed: {
    cacheMessage() {
      if (!this.cache.last_synced_at) return 'Cashier cache has never been refreshed; cached rows remain available.'
      const age = Math.max(0, Math.round(this.cache.age_seconds / 60))
      return `${this.cache.stale ? 'Cache may be stale' : 'Cache is current'} · refreshed ${age} minute${age === 1 ? '' : 's'} ago`
    },
  },
  watch: {
    form: {
      deep: true,
      handler: debounce(function () {
        router.get('/cashier-assignments', this.form, {
          preserveState: true,
          preserveScroll: true,
          replace: true,
        })
      }, 300),
    },
  },
  methods: {
    shortTime(time) {
      return String(time).slice(0, 5)
    },
    save(cashier) {
      router.put(`/cashier-assignments/${cashier.id}`, {
        branch_id: cashier.branch_id,
        default_shift_id: cashier.default_shift_id,
      }, { preserveScroll: true })
    },
    sync() {
      this.syncing = true
      router.post('/cashier-assignments/sync', {}, {
        preserveScroll: true,
        onFinish: () => { this.syncing = false },
      })
    },
  },
}
</script>
