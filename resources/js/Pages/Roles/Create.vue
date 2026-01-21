<template>
  <div>
    <Head title="Create Role" />

    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/roles">Roles</Link>
        <span class="text-brand-400 font-medium">/</span>
        Create
      </h1>
      <p class="text-gray-600">Create a new role and assign permissions.</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="store">
        <div class="p-6 space-y-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <text-input v-model="form.display_name" label="Display Name" :error="form.errors.display_name" required />
            <text-input
              v-model="form.name"
              label="System Name"
              :error="form.errors.name"
              required
              help="Lowercase letters, numbers, and underscores only. Example: branch_supervisor"
            />
          </div>

          <text-input
            v-model="form.description"
            label="Description"
            :error="form.errors.description"
            help="Optional"
          />

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <select-input v-model="form.active" label="Status" :error="form.errors.active">
              <option :value="true">Active</option>
              <option :value="false">Inactive</option>
            </select-input>
          </div>

          <div>
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-lg font-medium">Permissions</h3>
              <div class="text-sm text-gray-500">{{ form.permission_ids.length }} selected</div>
            </div>

            <div class="space-y-6">
              <div
                v-for="(perms, module) in permissionsByModule"
                :key="module"
                class="border rounded-md p-4"
              >
                <div class="flex items-center justify-between mb-3">
                  <div class="font-semibold text-gray-900 capitalize">{{ module }}</div>
                  <button
                    type="button"
                    class="text-sm text-brand-600 hover:text-brand-800"
                    @click="toggleModule(module)"
                  >
                    {{ isModuleFullySelected(module) ? 'Clear' : 'Select all' }}
                  </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <label
                    v-for="p in perms"
                    :key="p.id"
                    class="flex items-start space-x-3 p-2 rounded hover:bg-gray-50 cursor-pointer"
                  >
                    <input
                      type="checkbox"
                      class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                      :value="p.id"
                      v-model="form.permission_ids"
                    />
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ p.display_name }}</div>
                      <div class="text-xs text-gray-500">{{ p.name }}</div>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <div v-if="form.errors.permission_ids" class="mt-2 text-sm text-red-600">
              {{ form.errors.permission_ids }}
            </div>
          </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
          <Link href="/roles" class="text-gray-600 hover:text-gray-800">Cancel</Link>
          <loading-button :loading="form.processing" class="btn-kingbaker" type="submit">
            Create Role
          </loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import TextInput from '@/Shared/TextInput.vue'
import SelectInput from '@/Shared/SelectInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'

export default {
  components: { Head, Link, TextInput, SelectInput, LoadingButton },
  layout: Layout,
  props: {
    permissionsByModule: { type: Object, required: true },
  },
  data() {
    return {
      form: this.$inertia.form({
        name: '',
        display_name: '',
        description: '',
        active: true,
        permission_ids: [],
      }),
    }
  },
  methods: {
    store() {
      this.form.post('/roles')
    },
    modulePermissionIds(module) {
      return (this.permissionsByModule[module] || []).map((p) => p.id)
    },
    isModuleFullySelected(module) {
      const ids = this.modulePermissionIds(module)
      if (!ids.length) return false
      return ids.every((id) => this.form.permission_ids.includes(id))
    },
    toggleModule(module) {
      const ids = this.modulePermissionIds(module)
      if (!ids.length) return
      if (this.isModuleFullySelected(module)) {
        this.form.permission_ids = this.form.permission_ids.filter((id) => !ids.includes(id))
      } else {
        const set = new Set(this.form.permission_ids)
        ids.forEach((id) => set.add(id))
        this.form.permission_ids = Array.from(set)
      }
    },
  },
}
</script>

