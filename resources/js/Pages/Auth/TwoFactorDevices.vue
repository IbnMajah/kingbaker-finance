<template>
  <div>
    <Head title="Authenticator Devices" />

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Authenticator Devices</h1>
        <p class="mt-2 text-gray-600">Manage the devices that can generate verification codes for your account.</p>
      </div>
      <button
        v-if="!pendingDevice"
        type="button"
        class="btn-kingbaker self-start"
        @click="showAddForm = true"
      >
        Add Device
      </button>
    </div>

    <div v-if="pendingDevice" class="mb-8 overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-100 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Set up {{ pendingDevice.name }}</h2>
        <p class="mt-1 text-sm text-gray-600">Scan this QR code, then enter the generated six-digit code.</p>
      </div>
      <div class="grid gap-8 p-6 md:grid-cols-[auto,1fr]">
        <div>
          <img :src="pendingDevice.qrCode" alt="Authenticator setup QR code" class="h-48 w-48" />
          <p class="mt-3 text-xs text-gray-500">Manual setup key</p>
          <code class="mt-1 block max-w-48 break-all rounded bg-gray-100 px-3 py-2 text-sm text-gray-700">{{ pendingDevice.secret }}</code>
        </div>
        <form class="max-w-md space-y-5" @submit.prevent="confirmDevice">
          <text-input
            v-model="confirmForm.code"
            :error="confirmForm.errors.code"
            label="Verification Code"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="6"
            placeholder="Enter 6-digit code"
            autocomplete="one-time-code"
            autofocus
          />
          <div class="flex items-center gap-3">
            <loading-button :loading="confirmForm.processing" class="btn-kingbaker" type="submit">
              Verify and Add
            </loading-button>
            <button type="button" class="text-sm text-gray-600 hover:text-gray-900" @click="cancelPending">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-100 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-900">Registered Devices</h2>
      </div>

      <div v-if="devices.length" class="divide-y divide-gray-100">
        <div v-for="device in devices" :key="device.id" class="flex items-center justify-between gap-4 px-6 py-5">
          <div class="min-w-0">
            <p class="truncate font-medium text-gray-900">{{ device.name }}</p>
            <p class="mt-1 text-sm text-gray-500">Added {{ formatDate(device.created_at) }}</p>
          </div>
          <button type="button" class="btn-red flex-shrink-0" @click="openRemoveModal(device)">
            Remove
          </button>
        </div>
      </div>
      <div v-else class="px-6 py-10 text-center text-gray-500">
        No authenticator devices are registered.
      </div>
    </div>

    <Modal :show="showAddForm" @close="closeAddForm">
      <form class="p-6" @submit.prevent="startDeviceSetup">
        <h2 class="text-lg font-medium text-gray-900">Add Authenticator Device</h2>
        <p class="mb-5 mt-1 text-sm text-gray-600">Use a name that helps you identify this device later.</p>
        <text-input
          v-model="addForm.name"
          :error="addForm.errors.name"
          label="Device Name"
          type="text"
          maxlength="100"
          placeholder="e.g. Work phone"
          autocomplete="off"
          autofocus
        />
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="px-3 py-2 text-gray-600 hover:text-gray-900" @click="closeAddForm">Cancel</button>
          <loading-button :loading="addForm.processing" class="btn-kingbaker" type="submit">Continue</loading-button>
        </div>
      </form>
    </Modal>

    <Modal :show="Boolean(deviceToRemove)" @close="closeRemoveModal">
      <form class="p-6" @submit.prevent="removeDevice">
        <h2 class="text-lg font-medium text-gray-900">Remove Authenticator Device</h2>
        <p class="mt-2 text-sm text-gray-600">
          Remove <strong>{{ deviceToRemove?.name }}</strong>? Codes from this device will stop working immediately.
        </p>
        <p v-if="devices.length === 1" class="mt-2 text-sm font-medium text-red-600">
          This is your last device. You must set up a new authenticator before continuing.
        </p>
        <div class="mt-5">
          <text-input
            v-model="removeForm.password"
            :error="removeForm.errors.password"
            label="Current Password"
            type="password"
            autocomplete="current-password"
          />
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="px-3 py-2 text-gray-600 hover:text-gray-900" @click="closeRemoveModal">Cancel</button>
          <loading-button :loading="removeForm.processing" class="btn-red" type="submit">Remove Device</loading-button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import Modal from '@/Shared/Modal.vue'
import TextInput from '@/Shared/TextInput.vue'

export default {
  components: {
    Head,
    LoadingButton,
    Modal,
    TextInput,
  },
  layout: Layout,
  props: {
    devices: {
      type: Array,
      required: true,
    },
    pendingDevice: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      showAddForm: false,
      deviceToRemove: null,
      addForm: this.$inertia.form({ name: '' }),
      confirmForm: this.$inertia.form({ code: '' }),
      removeForm: this.$inertia.form({ password: '' }),
    }
  },
  methods: {
    startDeviceSetup() {
      this.addForm.post('/two-factor/devices', {
        onSuccess: () => {
          this.showAddForm = false
          this.addForm.reset()
        },
      })
    },
    confirmDevice() {
      this.confirmForm.post('/two-factor/devices/confirm', {
        onSuccess: () => this.confirmForm.reset(),
      })
    },
    cancelPending() {
      this.$inertia.delete('/two-factor/devices/pending')
    },
    closeAddForm() {
      this.showAddForm = false
      this.addForm.clearErrors()
    },
    openRemoveModal(device) {
      this.deviceToRemove = device
      this.removeForm.reset()
      this.removeForm.clearErrors()
    },
    closeRemoveModal() {
      this.deviceToRemove = null
      this.removeForm.reset()
      this.removeForm.clearErrors()
    },
    removeDevice() {
      this.removeForm.delete(`/two-factor/devices/${this.deviceToRemove.id}`, {
        preserveScroll: true,
        onSuccess: () => this.closeRemoveModal(),
      })
    },
    formatDate(value) {
      return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value))
    },
  },
}
</script>
