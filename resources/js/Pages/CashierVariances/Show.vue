<template>
  <div>
    <Head :title="reconciliation.sale_id ? `Sale #${reconciliation.sale_id}` : `Reconciliation #${reconciliation.id}`" />

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <div class="flex items-center gap-3">
          <Link href="/cashier-variances" class="text-brand-500 hover:text-brand-700">Cashier Variances</Link>
          <span class="text-gray-400">/</span>
          <h1 class="text-3xl font-bold text-gray-900">
            {{ reconciliation.sale_id ? `Sale #${reconciliation.sale_id}` : (reconciliation.pos_reference || `Variance #${reconciliation.id}`) }}
          </h1>
        </div>
        <p class="mt-2 text-gray-600">{{ reconciliation.cashier_name }} · {{ reconciliation.branch_name }} · {{ reconciliation.shift_name }}</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button v-if="reconciliation.status === 'draft' || reconciliation.status === 'rejected'" class="btn-indigo" @click="showEdit = true">
          Edit Values
        </button>
        <button v-if="reconciliation.status === 'draft' || reconciliation.status === 'rejected'" class="btn-kingbaker" @click="submit">
          Submit
        </button>
        <button v-if="reconciliation.status === 'submitted' && canApprove" class="btn-kingbaker" @click="showApprove = true">
          Approve
        </button>
        <button v-if="reconciliation.status === 'submitted' && canApprove" class="btn-red" @click="showReject = true">
          Reject
        </button>
        <button v-if="reconciliation.status === 'approved' && canAdjust" class="btn-red" @click="showReverse = true">
          Reverse
        </button>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="space-y-6 lg:col-span-2">
        <div class="rounded-lg bg-white p-6 shadow">
          <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Closing reconciliation</h2>
            <div class="flex gap-2">
              <badge :variant="classificationVariant">{{ title(reconciliation.classification) }}</badge>
              <badge :variant="statusVariant">{{ title(reconciliation.status) }}</badge>
            </div>
          </div>
          <dl class="grid gap-6 sm:grid-cols-2">
            <div><dt class="text-sm text-gray-500">Expected total</dt><dd class="mt-1 text-xl font-semibold">{{ money(reconciliation.expected_total) }}</dd></div>
            <div><dt class="text-sm text-gray-500">Actual total</dt><dd class="mt-1 text-xl font-semibold">{{ money(reconciliation.actual_total) }}</dd></div>
            <div><dt class="text-sm text-gray-500">Calculated variance</dt><dd class="mt-1 text-xl font-semibold">{{ money(reconciliation.calculated_variance) }}</dd></div>
            <div>
              <dt class="text-sm text-gray-500">Formula</dt>
              <dd class="mt-1 text-sm text-gray-700">Expected − Actual</dd>
            </div>
          </dl>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
          <h2 class="mb-5 text-lg font-semibold">Audit trail</h2>
          <div class="space-y-4">
            <div v-for="event in reconciliation.events" :key="event.id" class="border-l-2 border-gray-200 pl-4">
              <p class="font-medium text-gray-900">{{ title(event.event_type) }}</p>
              <p class="text-sm text-gray-500">{{ event.actor?.first_name }} {{ event.actor?.last_name }} · {{ dateTime(event.created_at) }}</p>
              <p v-if="event.reason" class="mt-1 text-sm text-gray-700">{{ event.reason }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="rounded-lg bg-white p-6 shadow">
          <h2 class="mb-4 text-lg font-semibold">Details</h2>
          <dl class="space-y-4 text-sm">
            <div><dt class="text-gray-500">Cashier</dt><dd class="font-medium"><Link :href="`/cashiers/${reconciliation.hr_cashier_id}`">{{ reconciliation.cashier_name }} ({{ reconciliation.cashier_employee_id }})</Link></dd></div>
            <div><dt class="text-gray-500">Business date</dt><dd>{{ formatDate(reconciliation.business_date) }}</dd></div>
            <div v-if="reconciliation.sale_id"><dt class="text-gray-500">Sale</dt><dd><Link :href="`/sales/${reconciliation.sale_id}`">Sale #{{ reconciliation.sale_id }}</Link></dd></div>
            <div><dt class="text-gray-500">Branch / shift</dt><dd>{{ reconciliation.branch_name }} / {{ reconciliation.shift_name }}</dd></div>
            <div v-if="reconciliation.comments"><dt class="text-gray-500">Comments</dt><dd class="whitespace-pre-line">{{ reconciliation.comments }}</dd></div>
          </dl>
          <a
            v-if="reconciliation.receipt_image_path"
            :href="`/cashier-variances/${reconciliation.id}/receipt`"
            target="_blank"
            rel="noopener"
            class="mt-5 inline-block text-brand-600 hover:text-brand-800"
          >
            View POS receipt
          </a>
        </div>

        <div v-if="reconciliation.outbox_events?.length" class="rounded-lg bg-white p-6 shadow">
          <h2 class="mb-4 text-lg font-semibold">HR integration</h2>
          <div v-for="event in reconciliation.outbox_events" :key="event.id" class="mb-3 last:mb-0">
            <div class="flex items-center justify-between">
              <span class="text-sm">{{ event.event_type }}</span>
              <badge :variant="event.status === 'processed' ? 'success' : event.status === 'failed' ? 'danger' : 'warning'">
                {{ title(event.status) }}
              </badge>
            </div>
            <p v-if="event.last_error" class="mt-1 text-xs text-red-600">{{ event.last_error }}</p>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="showReject" @close="showReject = false">
      <form class="p-6" @submit.prevent="reject">
        <h2 class="text-lg font-semibold">Reject reconciliation</h2>
        <textarea-input v-model="reviewForm.reason" :error="reviewForm.errors.reason" class="mt-4" label="Reason" />
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="px-3 py-2 text-gray-600" @click="showReject = false">Cancel</button>
          <loading-button :loading="reviewForm.processing" class="btn-red" type="submit">Reject</loading-button>
        </div>
      </form>
    </Modal>

    <Modal :show="showApprove" @close="showApprove = false">
      <form class="p-6" @submit.prevent="approve">
        <h2 class="text-lg font-semibold">Approve reconciliation</h2>
        <p class="mt-2 text-sm text-gray-600">Approval posts one immutable cashier-ledger entry.</p>
        <textarea-input v-model="approvalForm.comment" :error="approvalForm.errors.comment" class="mt-4" label="Manager Comment (optional)" />
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="px-3 py-2 text-gray-600" @click="showApprove = false">Cancel</button>
          <loading-button :loading="approvalForm.processing" class="btn-kingbaker" type="submit">Approve</loading-button>
        </div>
      </form>
    </Modal>

    <Modal :show="showEdit" @close="showEdit = false">
      <form class="max-h-screen overflow-y-auto p-6" @submit.prevent="update">
        <h2 class="text-lg font-semibold">Correct expected total</h2>
        <p class="mt-2 text-sm text-gray-600">Actual total stays the sale denomination total. Variance is recalculated as Expected − Actual.</p>
        <div class="mt-4 grid gap-4">
          <text-input v-model="editForm.expected_total" :error="editForm.errors.expected_total" type="number" step="0.01" label="Expected Total" />
        </div>
        <textarea-input v-model="editForm.comments" :error="editForm.errors.comments" class="mt-4" label="Comments" />
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="px-3 py-2 text-gray-600" @click="showEdit = false">Cancel</button>
          <loading-button :loading="editForm.processing" class="btn-kingbaker" type="submit">Save Corrections</loading-button>
        </div>
      </form>
    </Modal>

    <Modal :show="showReverse" @close="showReverse = false">
      <form class="p-6" @submit.prevent="reverse">
        <h2 class="text-lg font-semibold">Reverse approved reconciliation</h2>
        <p class="mt-2 text-sm text-gray-600">An immutable offsetting ledger entry will be created.</p>
        <textarea-input v-model="reverseForm.reason" :error="reverseForm.errors.reason" class="mt-4" label="Reason" />
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="px-3 py-2 text-gray-600" @click="showReverse = false">Cancel</button>
          <loading-button :loading="reverseForm.processing" class="btn-red" type="submit">Reverse</loading-button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Badge from '@/Shared/Badge.vue'
import Layout from '@/Shared/Layout.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import Modal from '@/Shared/Modal.vue'
import TextareaInput from '@/Shared/TextareaInput.vue'
import TextInput from '@/Shared/TextInput.vue'

export default {
  components: { Badge, Head, Link, LoadingButton, Modal, TextareaInput, TextInput },
  layout: Layout,
  props: {
    reconciliation: { type: Object, required: true },
    canApprove: Boolean,
    canAdjust: Boolean,
  },
  data() {
    return {
      showReject: false,
      showReverse: false,
      showEdit: false,
      showApprove: false,
      approvalForm: this.$inertia.form({ comment: '' }),
      reviewForm: this.$inertia.form({ reason: '' }),
      reverseForm: this.$inertia.form({ reason: '' }),
      editForm: this.$inertia.form({
        expected_total: this.reconciliation.expected_total,
        comments: this.reconciliation.comments,
      }),
    }
  },
  computed: {
    classificationVariant() {
      return this.reconciliation.classification === 'shortage' ? 'danger' : this.reconciliation.classification === 'surplus' ? 'success' : 'default'
    },
    statusVariant() {
      return { approved: 'success', submitted: 'warning', rejected: 'danger', reversed: 'default' }[this.reconciliation.status] || 'info'
    },
  },
  methods: {
    submit() {
      this.$inertia.put(`/cashier-variances/${this.reconciliation.id}/submit`)
    },
    update() {
      this.editForm.post(`/cashier-variances/${this.reconciliation.id}/update`, {
        onSuccess: () => { this.showEdit = false },
      })
    },
    approve() {
      this.approvalForm.put(`/cashier-variances/${this.reconciliation.id}/approve`, {
        onSuccess: () => { this.showApprove = false },
      })
    },
    reject() {
      this.reviewForm.put(`/cashier-variances/${this.reconciliation.id}/reject`, {
        onSuccess: () => { this.showReject = false },
      })
    },
    reverse() {
      this.reverseForm.post(`/cashier-variances/${this.reconciliation.id}/reverse`, {
        onSuccess: () => { this.showReverse = false },
      })
    },
    money(value) {
      return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'GMD' }).format(Number(value || 0))
    },
    formatDate(value) {
      return new Intl.DateTimeFormat().format(new Date(value))
    },
    dateTime(value) {
      return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
    },
    title(value) {
      return value ? value.replaceAll('_', ' ').replace(/\b\w/g, letter => letter.toUpperCase()) : ''
    },
  },
}
</script>
