<template>
  <div>
    <Head :title="branch.name" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/branches">Branches</Link>
        <span class="text-brand-400 font-medium">/</span>
        {{ branch.name }}
      </h1>
      <p class="text-gray-600">View branch details and performance</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Header Actions -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-lg font-semibold">Branch Details</h2>
        <Link
          :href="`/branches/${branch.id}/edit`"
          class="btn-kingbaker"
        >
          Edit Branch
        </Link>
      </div>

      <div class="p-6">
        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-sm font-medium text-gray-500 mb-4">Basic Information</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ branch.name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Location</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ branch.location }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="branch.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  >
                    {{ branch.active ? 'Active' : 'Inactive' }}
                  </span>
                </dd>
              </div>
            </dl>
          </div>

          <div>
            <h3 class="text-sm font-medium text-gray-500 mb-4">Contact Information</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Manager</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ branch.manager_name || '—' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ branch.phone || '—' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ branch.email || '—' }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Sales Performance -->
        <div class="mt-8 border-t border-gray-200 pt-6">
          <h3 class="text-sm font-medium text-gray-500 mb-4">Sales Performance</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Daily Sales -->
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-900">Daily Sales Target</h4>
                <span class="text-sm text-gray-500">{{ branch.daily_progress }}%</span>
              </div>
              <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full"
                  :style="{ width: `${branch.daily_progress}%` }"
                  :class="{
                    'bg-red-500': branch.daily_progress < 50,
                    'bg-yellow-500': branch.daily_progress >= 50 && branch.daily_progress < 80,
                    'bg-green-500': branch.daily_progress >= 80
                  }"
                ></div>
              </div>
              <div class="mt-2 text-sm text-gray-500">
                Target: {{ branch.daily_sales_target ? `$${branch.daily_sales_target}` : '—' }}
              </div>
            </div>

            <!-- Monthly Sales -->
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-900">Monthly Sales Target</h4>
                <span class="text-sm text-gray-500">{{ branch.monthly_progress }}%</span>
              </div>
              <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full"
                  :style="{ width: `${branch.monthly_progress}%` }"
                  :class="{
                    'bg-red-500': branch.monthly_progress < 50,
                    'bg-yellow-500': branch.monthly_progress >= 50 && branch.monthly_progress < 80,
                    'bg-green-500': branch.monthly_progress >= 80
                  }"
                ></div>
              </div>
              <div class="mt-2 text-sm text-gray-500">
                Target: {{ branch.monthly_sales_target ? `$${branch.monthly_sales_target}` : '—' }}
              </div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="mt-8 border-t border-gray-200 pt-6">
          <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
          <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ branch.description || '—' }}</p>
        </div>
      </div>
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
    branch: {
      type: Object,
      required: true,
    },
  },
}
</script>