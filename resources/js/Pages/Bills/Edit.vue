<template>
  <div>
    <Head title="Edit Bill" />

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-2">
        <Link class="text-brand-400 hover:text-brand-600" href="/bills">Bills</Link>
        <span class="text-brand-400 font-medium">/</span>
        Edit
      </h1>
      <p class="text-gray-600">Update bill information</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Bill Information</h2>
      </div>

      <form @submit.prevent="update">
        <div class="p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
              <select
                v-model="form.vendor_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.vendor_id ? 'border-red-500' : ''"
              >
                <option value="">Select vendor</option>
                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.name }}
                </option>
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
                placeholder="Enter bill number (optional)"
              />
              <div v-if="form.errors.bill_number" class="mt-1 text-sm text-red-600">{{ form.errors.bill_number }}</div>
            </div>
          </div>

          <!-- Dates and Amount -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bill Date *</label>
              <input
                v-model="form.bill_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bill_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.bill_date" class="mt-1 text-sm text-red-600">{{ form.errors.bill_date }}</div>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount</label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">GMD</span>
                <!-- For inventory bills: show calculated total -->
                <input
                  v-if="form.bill_type === 'inventory'"
                  :value="totalAmount"
                  type="text"
                  readonly
                  class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700"
                  placeholder="0.00"
                />
                <!-- For non-inventory bills: allow manual input -->
                <input
                  v-else
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.amount ? 'border-red-300' : 'border-gray-300'"
                  placeholder="0.00"
                />
              </div>
              <p v-if="form.bill_type === 'inventory'" class="mt-1 text-sm text-gray-500">Calculated from items below</p>
              <p v-else class="mt-1 text-sm text-gray-500">Enter the total bill amount</p>
              <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
            </div>
          </div>

          <!-- Bill Type -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Bill Type *</label>
              <select
                v-model="form.bill_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.bill_type ? 'border-red-500' : ''"
              >
                <option value="">Select bill type</option>
                <option v-for="type in billTypes" :key="type" :value="type">
                  {{ type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </option>
              </select>
              <div v-if="form.errors.bill_type" class="mt-1 text-sm text-red-600">{{ form.errors.bill_type }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Recurring Frequency</label>
              <select
                v-model="form.recurring_frequency"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.recurring_frequency ? 'border-red-500' : ''"
              >
                <option value="">Not recurring</option>
                <option v-for="frequency in recurringFrequencies" :key="frequency" :value="frequency">
                  {{ frequency.replace(/\b\w/g, l => l.toUpperCase()) }}
                </option>
              </select>
              <div v-if="form.errors.recurring_frequency" class="mt-1 text-sm text-red-600">{{ form.errors.recurring_frequency }}</div>
            </div>
          </div>

          <!-- Next Due Date (only show if recurring) -->
          <div v-if="form.recurring_frequency" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next Due Date</label>
              <input
                v-model="form.next_due_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.next_due_date ? 'border-red-500' : ''"
              />
              <div v-if="form.errors.next_due_date" class="mt-1 text-sm text-red-600">{{ form.errors.next_due_date }}</div>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.description ? 'border-red-500' : ''"
              placeholder="Enter bill description or notes"
            ></textarea>
            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
          </div>

          <!-- Bill Items (only for inventory bills) -->
          <div v-if="form.bill_type === 'inventory'" class="col-span-full">
            <label class="block text-sm font-medium text-gray-700 mb-3">Bill Items *</label>
            <div class="border border-gray-300 rounded-md overflow-hidden">
              <!-- Items Header -->
              <div class="bg-gray-50 px-4 py-3 border-b border-gray-300">
                <div class="grid grid-cols-12 gap-3 text-sm font-medium text-gray-700">
                  <div class="col-span-4">Description</div>
                  <div class="col-span-2">Unit Price (GMD)</div>
                  <div class="col-span-1">Unit</div>
                  <div class="col-span-2">Quantity</div>
                  <div class="col-span-2">Total</div>
                  <div class="col-span-1">Action</div>
                </div>
              </div>
              <!-- Items List -->
              <div class="divide-y divide-gray-200">
                <div v-for="(item, index) in form.items" :key="index" class="px-4 py-3">
                  <div class="grid grid-cols-12 gap-3 items-start">
                    <!-- Description -->
                    <div class="col-span-4">
                      <input
                        v-model="item.description"
                        type="text"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.description`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="Item description"
                      />
                      <div v-if="form.errors[`items.${index}.description`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.description`] }}
                      </div>
                    </div>
                    <!-- Unit Price -->
                    <div class="col-span-2">
                      <input
                        v-model.number="item.unit_price"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.unit_price`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="0.00"
                        @input="calculateItemTotal(index)"
                      />
                      <div v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.unit_price`] }}
                      </div>
                    </div>
                    <!-- Unit Measurement -->
                    <div class="col-span-1">
                      <input
                        v-model="item.unit_measurement"
                        type="text"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.unit_measurement`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="kg, pcs, etc."
                      />
                      <div v-if="form.errors[`items.${index}.unit_measurement`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.unit_measurement`] }}
                      </div>
                    </div>
                    <!-- Quantity -->
                    <div class="col-span-2">
                      <input
                        v-model.number="item.quantity"
                        type="number"
                        min="1"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`items.${index}.quantity`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="1"
                        @input="calculateItemTotal(index)"
                      />
                      <div v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`items.${index}.quantity`] }}
                      </div>
                    </div>
                    <!-- Total -->
                    <div class="col-span-2">
                      <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                        GMD {{ (item.unit_price * item.quantity || 0).toFixed(2) }}
                      </div>
                    </div>
                    <!-- Action -->
                    <div class="col-span-1">
                      <button
                        type="button"
                        @click="removeItem(index)"
                        class="w-full text-red-600 hover:text-red-800 px-2 py-2 text-sm"
                        :disabled="form.items.length <= 1"
                      >
                        <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 012 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v3a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Add Item Button -->
              <div class="px-4 py-3 bg-gray-50 border-t border-gray-300">
                <button
                  type="button"
                  @click="addItem"
                  class="text-brand-600 hover:text-brand-800 text-sm font-medium"
                >
                  + Add Item
                </button>
              </div>
            </div>
          </div>

          <!-- Current Image -->
          <div v-if="bill.image_path">
            <label class="block text-sm font-medium text-gray-700 mb-1">Current Bill Image</label>
            <img :src="`/storage/${bill.image_path}`" :alt="bill.bill_number || 'Bill image'" class="max-w-xs h-auto rounded-md border border-gray-300" />
          </div>

          <!-- Bill Image Upload -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ bill.image_path ? 'Replace Bill Image' : 'Bill Image' }}</label>
            <input
              @input="form.bill_image = $event.target.files[0]"
              type="file"
              accept="image/*"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-brand-500 focus:border-brand-500"
              :class="form.errors.bill_image ? 'border-red-500' : ''"
            />
            <div v-if="form.errors.bill_image" class="mt-1 text-sm text-red-600">{{ form.errors.bill_image }}</div>
            <p class="mt-1 text-sm text-gray-500">Optional: Upload an image/scan of the bill</p>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link href="/bills" class="text-gray-600 hover:text-gray-800">
              <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
              </svg>
              Cancel
            </Link>
            <button
              @click="destroy"
              type="button"
              class="text-red-600 hover:text-red-800"
            >
              Delete Bill
            </button>
          </div>
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-kingbaker flex items-center"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Updating...' : 'Update Bill' }}
          </button>
        </div>
      </form>
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
    bill: {
      type: Object,
      required: true
    },
    vendors: {
      type: Array,
      default: () => []
    },
    billTypes: {
      type: Array,
      default: () => []
    },
    recurringFrequencies: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    totalAmount() {
      return this.form.items.reduce((total, item) => {
        const unitPrice = parseFloat(item.unit_price) || 0
        const quantity = parseFloat(item.quantity) || 0
        return total + (unitPrice * quantity)
      }, 0).toFixed(2)
    },
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        vendor_id: this.bill.vendor_id,
        bill_number: this.bill.bill_number,
        bill_date: this.bill.bill_date ? new Date(this.bill.bill_date).toISOString().split('T')[0] : '',
        due_date: this.bill.due_date ? new Date(this.bill.due_date).toISOString().split('T')[0] : '',
        amount: this.bill.amount,
        bill_type: this.bill.bill_type,
        description: this.bill.description,
        recurring_frequency: this.bill.recurring_frequency,
        next_due_date: this.bill.next_due_date ? new Date(this.bill.next_due_date).toISOString().split('T')[0] : '',
        bill_image: null,
        items: this.bill.items && this.bill.items.length > 0 ? this.bill.items.map(item => ({
          ...item,
          unit_measurement: item.unit_measurement || ''
        })) : [
          {
            description: '',
            unit_price: '',
            unit_measurement: '',
            quantity: 1,
          }
        ],
      }),
    }
  },
  methods: {
    update() {
      this.form.put(`/bills/${this.bill.id}`)
    },
    destroy() {
      if (confirm('Are you sure you want to delete this bill?')) {
        this.$inertia.delete(`/bills/${this.bill.id}`)
      }
    },
    addItem() {
      this.form.items.push({
        description: '',
        unit_price: '',
        unit_measurement: '',
        quantity: 1,
      })
    },
    removeItem(index) {
      this.form.items.splice(index, 1)
    },
    calculateItemTotal(item) {
      const unitPrice = parseFloat(item.unit_price) || 0
      const quantity = parseFloat(item.quantity) || 0
      return (unitPrice * quantity).toFixed(2)
    },
  },
}
</script>