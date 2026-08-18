<template>
  <div>
    <Head title="Create Shift" />
    <h1 class="mb-6 text-3xl font-bold">Create Shift</h1>
    <form class="max-w-2xl rounded-lg bg-white p-6 shadow" @submit.prevent="submit">
      <div class="mb-5">
        <label class="mb-1 block text-sm font-medium">Name</label>
        <input v-model="form.name" class="w-full rounded-md border-gray-300" required />
        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
      </div>
      <div class="mb-5 grid gap-4 md:grid-cols-2">
        <div>
          <label class="mb-1 block text-sm font-medium">Start time</label>
          <input v-model="form.start_time" type="time" class="w-full rounded-md border-gray-300" required />
          <div v-if="form.errors.start_time" class="mt-1 text-sm text-red-600">{{ form.errors.start_time }}</div>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium">End time</label>
          <input v-model="form.end_time" type="time" class="w-full rounded-md border-gray-300" required />
          <div v-if="form.errors.end_time" class="mt-1 text-sm text-red-600">{{ form.errors.end_time }}</div>
        </div>
      </div>
      <p class="-mt-3 mb-5 text-xs text-gray-500">An end time before the start time creates an overnight shift.</p>
      <div class="mb-5">
        <label class="mb-1 block text-sm font-medium">Description</label>
        <textarea v-model="form.description" rows="3" class="w-full rounded-md border-gray-300" />
        <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
      </div>
      <label class="mb-6 flex items-center gap-2"><input v-model="form.active" type="checkbox" /> <span class="text-sm font-medium">Active</span></label>
      <div class="flex justify-end gap-3">
        <Link href="/shifts" class="rounded-md px-4 py-2 text-gray-700">Cancel</Link>
        <button class="btn-kingbaker" :disabled="form.processing">Create Shift</button>
      </div>
    </form>
  </div>
</template>

<script>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'

export default {
  components: { Head, Link },
  layout: Layout,
  setup() {
    const form = useForm({ name: '', start_time: '', end_time: '', description: '', active: true })
    return { form }
  },
  methods: {
    submit() {
      this.form.post('/shifts')
    },
  },
}
</script>
