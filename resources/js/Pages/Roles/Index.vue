<template>
  <div>
    <Head title="Roles" />

    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold mb-2">Roles</h1>
        <p class="text-gray-600">Create roles and control which permissions each role has.</p>
      </div>
      <Link v-if="canAssignRoles" href="/roles/create" class="btn-kingbaker">
        New Role
      </Link>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Users</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Permissions</th>
              <th class="px-6 py-3" />
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="font-medium text-gray-900">{{ role.display_name }}</div>
                <div v-if="role.description" class="text-xs text-gray-500 mt-1">{{ role.description }}</div>
              </td>
              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="role.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ role.active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">{{ role.users_count }}</td>
              <td class="px-6 py-4 text-sm text-gray-700">{{ role.permissions_count }}</td>
              <td class="px-6 py-4 text-right space-x-3">
                <Link v-if="canAssignRoles" class="text-brand-600 hover:text-brand-800" :href="`/roles/${role.id}`">View</Link>
                <Link v-if="canAssignRoles" class="text-brand-600 hover:text-brand-800" :href="`/roles/${role.id}/edit`">Edit</Link>
              </td>
            </tr>
            <tr v-if="!roles.length">
              <td colspan="5" class="px-6 py-10 text-center text-gray-500">No roles found.</td>
            </tr>
          </tbody>
        </table>
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
    roles: { type: Array, required: true },
  },
}
</script>

