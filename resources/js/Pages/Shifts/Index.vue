<template>
  <div>
    <Head title="Shifts" />
    <div class="mb-8">
      <h1 class="mb-2 text-3xl font-bold">Shifts</h1>
      <p class="text-gray-600">Manage global cashier working schedules, including overnight shifts.</p>
    </div>

    <div class="mb-6 flex items-center justify-between">
      <div class="mr-4 flex-1">
        <div class="relative">
          <input
            v-model="form.search"
            type="text"
            placeholder="Search shifts..."
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

        <Link href="/shifts/create" class="btn-kingbaker">
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Shift
        </Link>
      </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Name</th>
            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Schedule</th>
            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
            <th class="px-5 py-3 text-left text-xs font-medium uppercase text-gray-500">Usage</th>
            <th class="px-5 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="shift in shifts.data" :key="shift.id">
            <td class="px-5 py-4">
              <div class="font-medium">{{ shift.name }}</div>
              <div class="max-w-md truncate text-sm text-gray-500">{{ shift.description || 'No description' }}</div>
            </td>
            <td class="px-5 py-4 text-sm">{{ shift.start_time }}–{{ shift.end_time }}<span v-if="shift.end_time <= shift.start_time" class="ml-2 text-xs text-indigo-600">overnight</span></td>
            <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs" :class="shift.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'">{{ shift.active ? 'Active' : 'Inactive' }}</span></td>
            <td class="px-5 py-4 text-sm text-gray-600">{{ shift.references_count }} reference{{ shift.references_count === 1 ? '' : 's' }}</td>
            <td class="px-5 py-4 text-right text-sm">
              <Link :href="`/shifts/${shift.id}/edit`" class="mr-4 text-brand-600">Edit</Link>
              <button class="text-red-600" @click="remove(shift)">{{ shift.references_count ? 'Deactivate' : 'Delete' }}</button>
            </td>
          </tr>
          <tr v-if="shifts.data.length === 0"><td colspan="5" class="px-5 py-8 text-center text-gray-500">No shifts found.</td></tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex justify-end gap-2">
      <Link v-for="link in shifts.links" :key="link.label" :href="link.url || '#'" preserve-state
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
  props: { shifts: Object, filters: Object },
  data() {
    return { form: { search: this.filters.search || '', status: this.filters.status || '' } }
  },
  watch: {
    form: {
      deep: true,
      handler: debounce(function () {
        router.get('/shifts', this.form, {
          preserveState: true,
          preserveScroll: true,
          replace: true,
        })
      }, 300),
    },
  },
  methods: {
    remove(shift) {
      if (window.confirm(`${shift.references_count ? 'Deactivate' : 'Delete'} ${shift.name}?`)) {
        router.delete(`/shifts/${shift.id}`, { preserveScroll: true })
      }
    },
  },
}
</script>
