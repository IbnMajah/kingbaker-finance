<template>
  <div>
    <Head :title="role.display_name" />

    <div class="mb-8 flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-bold mb-2">
          <Link class="text-brand-400 hover:text-brand-600" href="/roles">Roles</Link>
          <span class="text-brand-400 font-medium">/</span>
          {{ role.display_name }}
        </h1>
        <p class="text-gray-600">{{ role.description || 'No description.' }}</p>
        <div class="mt-3 flex items-center space-x-3">
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="role.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
          >
            {{ role.active ? 'Active' : 'Inactive' }}
          </span>
          <span class="text-sm text-gray-500">{{ role.users_count }} users</span>
          <span class="text-sm text-gray-500">{{ role.permissions.length }} permissions</span>
        </div>
      </div>

      <Link v-if="canAssignRoles" :href="`/roles/${role.id}/edit`" class="btn-kingbaker">Edit Role</Link>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Permissions</h2>
        <div class="text-sm text-gray-500">System name: <span class="font-mono">{{ role.name }}</span></div>
      </div>

      <div class="p-6">
        <div v-if="!role.permissions.length" class="text-gray-500">
          This role currently has no permissions.
        </div>

        <div v-else class="space-y-6">
          <div v-for="(perms, module) in groupedPermissions" :key="module" class="border rounded-md p-4">
            <div class="font-semibold text-gray-900 capitalize mb-3">{{ module }}</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div v-for="p in perms" :key="p.id" class="p-2 rounded bg-gray-50">
                <div class="text-sm font-medium text-gray-900">{{ p.display_name }}</div>
                <div class="text-xs text-gray-500">{{ p.name }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import { usePermissions } from '@/composables/usePermissions.js'

export default {
  components: { Head, Link },
  layout: Layout,
  setup() {
    const { canAssignRoles } = usePermissions()
    return { canAssignRoles }
  },
  props: {
    role: { type: Object, required: true },
  },
  computed: {
    groupedPermissions() {
      return (this.role.permissions || []).reduce((acc, p) => {
        const key = p.module || 'other'
        if (!acc[key]) acc[key] = []
        acc[key].push(p)
        return acc
      }, {})
    },
  },
}
</script>

