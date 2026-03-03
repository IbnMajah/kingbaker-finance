<template>
  <div>
    <Head :title="`Edit Payment ${payment.payment_number}`" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/cheque-payments">Payments</Link>
        <span class="text-brand-400 font-medium">/</span>
        <Link :href="`/cheque-payments/${payment.id}`" class="text-brand-400 hover:text-brand-600">{{ payment.payment_number }}</Link>
        <span class="text-brand-400 font-medium">/</span>
        Edit
      </h1>
      <p class="text-gray-600">Update payment details and information</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Payment Information</h2>
        <p class="text-sm text-gray-600 mt-1">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 mr-2">DEBIT ONLY</span>
          All payments are debit transactions. The specified bank account will be debited with the payment amount.
        </p>
      </div>

      <form @submit.prevent="submit">
        <div class="p-6 space-y-6">
          <!-- Payment Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Number</label>
              <input
                :value="payment.payment_number"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                disabled
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
              <input
                v-model="form.payment_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.payment_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.payment_date" class="mt-1 text-sm text-red-600">{{ form.errors.payment_date }}</div>
            </div>
          </div>

          <!-- Payee Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payee *</label>
              <input
                v-model="form.payee"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.payee ? 'border-red-500' : ''"
                placeholder="Who is receiving the payment?"
              />
              <div v-if="form.errors.payee" class="mt-1 text-sm text-red-600">{{ form.errors.payee }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Category *</label>
              <select
                v-model="form.payment_category"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.payment_category ? 'border-red-500' : ''"
              >
                <option value="">Select Payment Category</option>
                <option v-for="category in paymentCategories" :key="category.value" :value="category.value">{{ category.label }}</option>
              </select>
              <div v-if="form.errors.payment_category" class="mt-1 text-sm text-red-600">{{ form.errors.payment_category }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Mode *</label>
              <select
                v-model="form.payment_mode"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.payment_mode ? 'border-red-500' : ''"
              >
                <option value="">Select Payment Mode</option>
                <option v-for="mode in paymentModes" :key="mode.value" :value="mode.value">{{ mode.label }}</option>
              </select>
              <div v-if="form.errors.payment_mode" class="mt-1 text-sm text-red-600">{{ form.errors.payment_mode }}</div>
            </div>
          </div>

          <!-- Phone Number for vendor_payment -->
          <div v-if="form.payment_category === 'vendor_payment'" class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Individual Payment Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input
                  v-model="form.phone_number"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.phone_number ? 'border-red-500' : ''"
                  placeholder="Payee phone number"
                />
                <div v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600">{{ form.errors.phone_number }}</div>
              </div>
            </div>
          </div>

          <!-- Bill Information (when category is bill) -->
          <div v-if="form.payment_category === 'bill'" class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bill Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bill Type *</label>
                <select
                  v-model="form.bill_type"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.bill_type ? 'border-red-500' : ''"
                >
                  <option value="">Select Bill Type</option>
                  <option v-for="type in billTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
                <div v-if="form.errors.bill_type" class="mt-1 text-sm text-red-600">{{ form.errors.bill_type }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                <select
                  v-model="form.vendor_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.vendor_id ? 'border-red-500' : ''"
                >
                  <option value="">Select Vendor</option>
                  <option v-for="vendor in vendors" :key="vendor.value" :value="vendor.value">{{ vendor.label }}</option>
                </select>
                <div v-if="form.errors.vendor_id" class="mt-1 text-sm text-red-600">{{ form.errors.vendor_id }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bill Number</label>
                <input
                  v-model="form.bill_number"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.bill_number ? 'border-red-500' : ''"
                  placeholder="Bill reference number"
                />
                <div v-if="form.errors.bill_number" class="mt-1 text-sm text-red-600">{{ form.errors.bill_number }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input
                  v-model="form.due_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.due_date ? 'border-red-500' : ''"
                />
                <div v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recurring</label>
                <div class="flex items-center mt-2">
                  <input
                    v-model="form.is_recurring"
                    type="checkbox"
                    class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 text-sm text-gray-700">This is a recurring payment</label>
                </div>
              </div>

              <div v-if="form.is_recurring">
                <label class="block text-sm font-medium text-gray-700 mb-1">Recurring Frequency *</label>
                <select
                  v-model="form.recurring_frequency"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.recurring_frequency ? 'border-red-500' : ''"
                >
                  <option value="">Select Frequency</option>
                  <option v-for="frequency in recurringFrequencies" :key="frequency.value" :value="frequency.value">{{ frequency.label }}</option>
                </select>
                <div v-if="form.errors.recurring_frequency" class="mt-1 text-sm text-red-600">{{ form.errors.recurring_frequency }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bill Image</label>
                <input
                  @input="form.bill_image = $event.target.files[0]"
                  type="file"
                  accept="image/*,application/pdf"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.bill_image ? 'border-red-500' : ''"
                />
                <div v-if="form.errors.bill_image" class="mt-1 text-sm text-red-600">{{ form.errors.bill_image }}</div>
              </div>
            </div>

            <!-- Line Items (for inventory bill type) -->
            <div v-if="form.bill_type === 'inventory'" class="mt-6">
              <div class="flex items-center justify-between mb-3">
                <h4 class="text-md font-medium text-gray-900">Line Items</h4>
                <button
                  type="button"
                  @click="addItem"
                  class="inline-flex items-center px-3 py-1.5 border border-brand-300 text-sm font-medium rounded-md text-brand-700 bg-brand-50 hover:bg-brand-100"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add Item
                </button>
              </div>
              <div v-if="form.errors.items" class="mb-2 text-sm text-red-600">{{ form.errors.items }}</div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Unit Price</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Unit</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-20">Qty</th>
                      <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Total</th>
                      <th class="px-3 py-2 w-10"></th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="(item, index) in form.items" :key="index">
                      <td class="px-3 py-2">
                        <input v-model="item.description" type="text" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-brand-500 focus:border-brand-500" placeholder="Item description" />
                      </td>
                      <td class="px-3 py-2">
                        <input v-model="item.unit_price" type="number" step="0.01" min="0" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-brand-500 focus:border-brand-500" placeholder="0.00" />
                      </td>
                      <td class="px-3 py-2">
                        <input v-model="item.unit_measurement" type="text" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-brand-500 focus:border-brand-500" placeholder="e.g. kg" />
                      </td>
                      <td class="px-3 py-2">
                        <input v-model="item.quantity" type="number" step="0.01" min="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-brand-500 focus:border-brand-500" placeholder="1" />
                      </td>
                      <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">
                        {{ ((item.unit_price || 0) * (item.quantity || 0)).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                      </td>
                      <td class="px-3 py-2">
                        <button v-if="form.items.length > 1" type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="bg-gray-50">
                      <td colspan="4" class="px-3 py-2 text-right text-sm font-semibold text-gray-700">Grand Total:</td>
                      <td class="px-3 py-2 text-right text-sm font-bold text-gray-900">
                        {{ itemsTotal.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                      </td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <!-- Financial Details -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                <div class="relative">
                  <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                    :class="[form.errors.amount ? 'border-red-500' : '', isInventoryBill ? 'bg-gray-50' : '']"
                    :readonly="isInventoryBill"
                  />
                </div>
                <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
                <p v-if="isInventoryBill" class="mt-1 text-sm text-gray-500">Auto-calculated from line items</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account to Debit *</label>
                <select
                  v-model="form.bank_account_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.bank_account_id ? 'border-red-500' : ''"
                >
                  <option value="">Select Bank Account</option>
                  <option v-for="account in bankAccounts" :key="account.value" :value="account.value">{{ account.label }}</option>
                </select>
                <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
              </div>
            </div>
          </div>

          <!-- Reference Information -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reference Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div v-if="form.payment_mode === 'cheque'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cheque Number</label>
                <input v-model="form.cheque_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500" :class="form.errors.cheque_number ? 'border-red-500' : ''" />
                <div v-if="form.errors.cheque_number" class="mt-1 text-sm text-red-600">{{ form.errors.cheque_number }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                <input v-model="form.reference_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500" :class="form.errors.reference_number ? 'border-red-500' : ''" />
                <div v-if="form.errors.reference_number" class="mt-1 text-sm text-red-600">{{ form.errors.reference_number }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select v-model="form.branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500" :class="form.errors.branch_id ? 'border-red-500' : ''">
                  <option value="">Select Branch</option>
                  <option v-for="branch in branches" :key="branch.value" :value="branch.value">{{ branch.label }}</option>
                </select>
                <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Image</label>
                <input type="file" @input="form.receipt_image = $event.target.files[0]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500" :class="form.errors.receipt_image ? 'border-red-500' : ''" />
                <div v-if="form.errors.receipt_image" class="mt-1 text-sm text-red-600">{{ form.errors.receipt_image }}</div>
                <div v-if="payment.receipt_image_path" class="mt-2">
                  <img :src="`/storage/${payment.receipt_image_path}`" alt="Current Receipt" class="max-w-xs rounded-lg shadow-sm" />
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Details</h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                <textarea v-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500" :class="form.errors.description ? 'border-red-500' : ''"></textarea>
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
                <textarea v-model="form.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500" :class="form.errors.notes ? 'border-red-500' : ''" placeholder="Internal notes (optional)"></textarea>
                <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link :href="`/cheque-payments/${payment.id}`" class="text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="btn-kingbaker flex items-center">
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Updating...' : 'Update Payment' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'

export default {
  components: {
    Head,
    Link,
  },
  layout: Layout,
  props: {
    payment: Object,
    bankAccounts: Array,
    branches: Array,
    vendors: Array,
    paymentCategories: Array,
    paymentModes: Array,
    recurringFrequencies: Array,
    billTypes: Array,
  },
  data() {
    const bill = this.payment.bill
    const existingItems = bill?.items?.length
      ? bill.items.map(i => ({
          description: i.description,
          unit_price: i.unit_price,
          unit_measurement: i.unit_measurement || '',
          quantity: i.quantity,
        }))
      : [{ description: '', unit_price: '', unit_measurement: '', quantity: '' }]

    return {
      form: useForm({
        payee: this.payment.payee,
        phone_number: this.payment.phone_number || '',
        amount: this.payment.amount,
        payment_date: this.payment.payment_date ? this.payment.payment_date.substring(0, 10) : '',
        payment_category: this.payment.payment_category,
        payment_mode: this.payment.payment_mode,
        bank_account_id: this.payment.bank_account_id,
        branch_id: this.payment.branch_id || '',
        vendor_id: this.payment.vendor_id || '',
        is_recurring: this.payment.is_recurring || false,
        recurring_frequency: this.payment.recurring_frequency || '',
        cheque_number: this.payment.cheque_number || '',
        reference_number: this.payment.reference_number || '',
        description: this.payment.description,
        notes: this.payment.notes || '',
        receipt_image: null,
        bill_type: bill?.bill_type || '',
        bill_number: bill?.bill_number || '',
        due_date: bill?.due_date ? bill.due_date.substring(0, 10) : '',
        bill_image: null,
        items: existingItems,
      }),
    }
  },
  computed: {
    isInventoryBill() {
      return this.form.payment_category === 'bill' && this.form.bill_type === 'inventory'
    },
    itemsTotal() {
      return this.form.items.reduce((sum, item) => sum + ((item.unit_price || 0) * (item.quantity || 0)), 0)
    },
  },
  watch: {
    itemsTotal(newVal) {
      if (this.isInventoryBill) {
        this.form.amount = newVal > 0 ? newVal : ''
      }
    },
  },
  methods: {
    submit() {
      this.form.put(`/cheque-payments/${this.payment.id}`, {
        preserveScroll: true,
      })
    },
    addItem() {
      this.form.items.push({ description: '', unit_price: '', unit_measurement: '', quantity: '' })
    },
    removeItem(index) {
      this.form.items.splice(index, 1)
    },
  },
}
</script>
