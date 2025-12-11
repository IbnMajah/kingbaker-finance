<template>
  <div>
    <Head title="Create Sale" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center space-x-4 mb-2">
        <Link href="/sales" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          </svg>
        </Link>
        <h1 class="text-3xl font-bold">Create Sale</h1>
      </div>
      <p class="text-gray-600">Record a new sales transaction</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="store">
        <!-- Form Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold">Sale Information</h2>
        </div>

        <!-- Form Body -->
        <div class="p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Branch -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Branch <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.branch_id"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                  form.errors.branch_id ? 'border-red-300' : 'border-gray-300'
                ]"
              >
                <option value="">Select a branch</option>
                <option v-for="branch in branches" :key="branch.value" :value="branch.value">
                  {{ branch.label }}
                </option>
              </select>
              <p v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.branch_id }}
              </p>
            </div>

            <!-- Shift -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Shift <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.shift_id"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                  form.errors.shift_id ? 'border-red-300' : 'border-gray-300'
                ]"
              >
                <option value="">Select a shift</option>
                <option v-for="shift in shifts" :key="shift.value" :value="shift.value">
                  {{ shift.label }}
                </option>
              </select>
              <p v-if="form.errors.shift_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.shift_id }}
              </p>
            </div>

            <!-- Sales Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Sales Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.sales_date"
                type="date"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                  form.errors.sales_date ? 'border-red-300' : 'border-gray-300'
                ]"
              />
              <p v-if="form.errors.sales_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.sales_date }}
              </p>
            </div>

            <!-- Total Amount Display -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Total Amount
              </label>
              <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-md">
                <div class="text-lg font-bold text-brand-600">GMD {{ totalAmount.toFixed(2) }}</div>
                <p class="text-xs text-gray-500 mt-1">Calculated from all payment methods</p>
              </div>
            </div>

            <!-- Cashier -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Cashier
              </label>
              <input
                v-model="form.cashier"
                type="text"
                placeholder="Enter cashier name"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                  form.errors.cashier ? 'border-red-300' : 'border-gray-300'
                ]"
              />
              <p v-if="form.errors.cashier" class="mt-1 text-sm text-red-600">
                {{ form.errors.cashier }}
              </p>
            </div>

            <!-- Closing Manager -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Closing Manager
              </label>
              <input
                v-model="form.closing_manager"
                type="text"
                placeholder="Enter closing manager name"
                :class="[
                  'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                  form.errors.closing_manager ? 'border-red-300' : 'border-gray-300'
                ]"
              />
              <p v-if="form.errors.closing_manager" class="mt-1 text-sm text-red-600">
                {{ form.errors.closing_manager }}
              </p>
            </div>
          </div>

          <!-- Payment Methods -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Methods</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Cash -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Cash
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">D</span>
                  </div>
                  <input
                    v-model="form.cash_amount"
                    type="text"
                    placeholder="0.00"
                    :class="[
                      'w-full pl-8 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                      form.errors.cash_amount ? 'border-red-300' : 'border-gray-300'
                    ]"
                    @input="formatNumber('cash_amount', $event)"
                    @blur="formatNumber('cash_amount', $event)"
                  />
                </div>
                <p v-if="form.errors.cash_amount" class="mt-1 text-sm text-red-600">
                  {{ form.errors.cash_amount }}
                </p>
              </div>

              <!-- Bank Transfer -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Bank Transfer
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">D</span>
                  </div>
                  <input
                    v-model="form.bank_transfer_amount"
                    type="text"
                    placeholder="0.00"
                    :class="[
                      'w-full pl-8 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                      form.errors.bank_transfer_amount ? 'border-red-300' : 'border-gray-300'
                    ]"
                    @input="formatNumber('bank_transfer_amount', $event)"
                    @blur="formatNumber('bank_transfer_amount', $event)"
                  />
                </div>
                <p v-if="form.errors.bank_transfer_amount" class="mt-1 text-sm text-red-600">
                  {{ form.errors.bank_transfer_amount }}
                </p>
              </div>

              <!-- Mobile Money - MyNafa -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Mobile Money - MyNafa
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">D</span>
                  </div>
                  <input
                    v-model="form.mobile_money_my_nafa"
                    type="text"
                    placeholder="0.00"
                    :class="[
                      'w-full pl-8 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                      form.errors.mobile_money_my_nafa ? 'border-red-300' : 'border-gray-300'
                    ]"
                    @input="formatNumber('mobile_money_my_nafa', $event)"
                    @blur="formatNumber('mobile_money_my_nafa', $event)"
                  />
                </div>
                <p v-if="form.errors.mobile_money_my_nafa" class="mt-1 text-sm text-red-600">
                  {{ form.errors.mobile_money_my_nafa }}
                </p>
              </div>

              <!-- Mobile Money - APS -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Mobile Money - APS
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">D</span>
                  </div>
                  <input
                    v-model="form.mobile_money_aps"
                    type="text"
                    placeholder="0.00"
                    :class="[
                      'w-full pl-8 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                      form.errors.mobile_money_aps ? 'border-red-300' : 'border-gray-300'
                    ]"
                    @input="formatNumber('mobile_money_aps', $event)"
                    @blur="formatNumber('mobile_money_aps', $event)"
                  />
                </div>
                <p v-if="form.errors.mobile_money_aps" class="mt-1 text-sm text-red-600">
                  {{ form.errors.mobile_money_aps }}
                </p>
              </div>

              <!-- Mobile Money - Wave -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Mobile Money - Wave
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">D</span>
                  </div>
                  <input
                    v-model="form.mobile_money_wave"
                    type="text"
                    placeholder="0.00"
                    :class="[
                      'w-full pl-8 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                      form.errors.mobile_money_wave ? 'border-red-300' : 'border-gray-300'
                    ]"
                    @input="formatNumber('mobile_money_wave', $event)"
                    @blur="formatNumber('mobile_money_wave', $event)"
                  />
                </div>
                <p v-if="form.errors.mobile_money_wave" class="mt-1 text-sm text-red-600">
                  {{ form.errors.mobile_money_wave }}
                </p>
              </div>
            </div>
          </div>

          <!-- Credit Customer Section -->
          <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">Credit Customer</h3>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
              <!-- Credit Items List -->
              <div class="divide-y divide-gray-200">
                <div v-for="(item, index) in form.credit_items" :key="index" class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-gray-900">Credit Item #{{ index + 1 }}</h4>
                    <button
                      type="button"
                      @click="removeCreditItem(index)"
                      class="text-red-600 hover:text-red-800 p-1"
                    >
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 012 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v3a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                      </svg>
                    </button>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Customer Selection -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
                      <select
                        v-model="item.customer_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`credit_items.${index}.customer_id`] ? 'border-red-300' : 'border-gray-300'"
                      >
                        <option value="">Select customer</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                          {{ customer.name }}
                        </option>
                      </select>
                      <div v-if="form.errors[`credit_items.${index}.customer_id`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`credit_items.${index}.customer_id`] }}
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Description -->
                    <div class="lg:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                      <input
                        v-model="item.description"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`credit_items.${index}.description`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="e.g., Product or service"
                        @input="updateTotalAmount"
                      />
                      <div v-if="form.errors[`credit_items.${index}.description`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`credit_items.${index}.description`] }}
                      </div>
                    </div>

                    <!-- Unit Price -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price *</label>
                      <input
                        v-model="item.unit_price"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`credit_items.${index}.unit_price`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="0.00"
                        @input="formatCreditNumber(index, 'unit_price', $event); updateTotalAmount()"
                        @blur="formatCreditNumber(index, 'unit_price', $event)"
                      />
                      <div v-if="form.errors[`credit_items.${index}.unit_price`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`credit_items.${index}.unit_price`] }}
                      </div>
                    </div>

                    <!-- Unit Measurement -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                      <input
                        v-model="item.unit_measurement"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`credit_items.${index}.unit_measurement`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="e.g., each, kg, pcs"
                      />
                      <div v-if="form.errors[`credit_items.${index}.unit_measurement`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`credit_items.${index}.unit_measurement`] }}
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <!-- Quantity -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                      <input
                        v-model="item.quantity"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        :class="form.errors[`credit_items.${index}.quantity`] ? 'border-red-300' : 'border-gray-300'"
                        placeholder="1.00"
                        @input="formatCreditNumber(index, 'quantity', $event); updateTotalAmount()"
                        @blur="formatCreditNumber(index, 'quantity', $event)"
                      />
                      <div v-if="form.errors[`credit_items.${index}.quantity`]" class="mt-1 text-sm text-red-600">
                        {{ form.errors[`credit_items.${index}.quantity`] }}
                      </div>
                    </div>
                  </div>

                  <!-- Item Total -->
                  <div class="mt-4 flex justify-end">
                    <div class="text-right">
                      <div class="text-sm text-gray-500">Item Total:</div>
                      <div class="text-lg font-bold text-blue-600">
                        GMD {{ ((parseFloat(item.unit_price) || 0) * (parseFloat(item.quantity) || 0)).toFixed(2) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add Credit Item Button -->
              <div class="px-6 py-4 border-t border-gray-200 border-dashed">
                <button
                  type="button"
                  @click="addCreditItem"
                  class="w-full flex items-center justify-center px-4 py-3 text-brand-600 hover:text-brand-700 text-sm font-medium border-2 border-dashed border-gray-300 rounded-lg hover:border-brand-400 transition-colors"
                >
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                  </svg>
                  + Add Credit Customer Item
                </button>
              </div>
            </div>
          </div>

          <!-- Depositable Amount -->
          <div class="border-t border-gray-200 pt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Depositable Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">D</span>
                  </div>
                  <input
                    v-model="form.amount"
                    type="text"
                    placeholder="0.00"
                    :class="[
                      'w-full pl-8 pr-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
                      form.errors.amount ? 'border-red-300' : 'border-gray-300'
                    ]"
                    @input="formatNumber('amount', $event)"
                    @blur="formatNumber('amount', $event)"
                  />
                </div>
                <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">
                  {{ form.errors.amount }}
                </p>
                <p class="mt-1 text-sm text-gray-500">Amount to be deposited</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/sales" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            :class="[
              'px-6 py-2 bg-brand-600 text-white font-medium rounded-md hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
              { 'opacity-50 cursor-not-allowed': form.processing }
            ]"
          >
            <span v-if="form.processing" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating...
            </span>
            <span v-else>Create Sale</span>
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
    branches: {
      type: Array,
      default: () => []
    },
    shifts: {
      type: Array,
      default: () => []
    },
    customers: {
      type: Array,
      default: () => []
    },
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        branch_id: '',
        shift_id: '',
        sales_date: new Date().toISOString().split('T')[0],
        amount: '0',
        cash_amount: '0',
        bank_transfer_amount: '0',
        mobile_money_my_nafa: '0',
        mobile_money_aps: '0',
        mobile_money_wave: '0',
        cashier: '',
        closing_manager: '',
        credit_items: [],
      }),
    }
  },
  computed: {
    totalAmount() {
      let total = 0
      
      // Add payment method amounts
      total += parseFloat(this.form.cash_amount) || 0
      total += parseFloat(this.form.bank_transfer_amount) || 0
      total += parseFloat(this.form.mobile_money_my_nafa) || 0
      total += parseFloat(this.form.mobile_money_aps) || 0
      total += parseFloat(this.form.mobile_money_wave) || 0
      
      // Add credit items totals
      if (this.form.credit_items && Array.isArray(this.form.credit_items)) {
        this.form.credit_items.forEach(item => {
          const itemTotal = (parseFloat(item.unit_price) || 0) * (parseFloat(item.quantity) || 0)
          total += itemTotal
        })
      }
      
      return total
    }
  },
  methods: {
    store() {
      // Convert string values to numbers before submitting
      const formData = {
        ...this.form.data(),
        amount: parseFloat(this.form.amount) || 0,
        total_amount: this.totalAmount,
        cash_amount: parseFloat(this.form.cash_amount) || 0,
        bank_transfer_amount: parseFloat(this.form.bank_transfer_amount) || 0,
        mobile_money_my_nafa: parseFloat(this.form.mobile_money_my_nafa) || 0,
        mobile_money_aps: parseFloat(this.form.mobile_money_aps) || 0,
        mobile_money_wave: parseFloat(this.form.mobile_money_wave) || 0,
        credit_items: this.form.credit_items.map(item => ({
          customer_id: item.customer_id,
          description: item.description,
          unit_price: parseFloat(item.unit_price) || 0,
          unit_measurement: item.unit_measurement || null,
          quantity: parseFloat(item.quantity) || 0,
        })),
      }
      
      this.form.transform(() => formData).post('/sales')
    },
    formatNumber(field, event) {
      let value = event.target.value.replace(/[^0-9.]/g, '')
      if (value === '' || value === '.') {
        this.form[field] = '0'
        return
      }
      const num = parseFloat(value)
      if (!isNaN(num)) {
        this.form[field] = num.toFixed(2)
      } else {
        this.form[field] = '0'
      }
      this.updateTotalAmount()
    },
    formatCreditNumber(index, field, event) {
      let value = event.target.value.replace(/[^0-9.]/g, '')
      if (value === '' || value === '.') {
        this.form.credit_items[index][field] = '0'
        return
      }
      const num = parseFloat(value)
      if (!isNaN(num)) {
        this.form.credit_items[index][field] = num.toFixed(2)
      } else {
        this.form.credit_items[index][field] = '0'
      }
    },
    updateTotalAmount() {
      // Force reactivity update
      this.$forceUpdate()
    },
    addCreditItem() {
      this.form.credit_items.push({
        customer_id: '',
        description: '',
        unit_price: '0',
        unit_measurement: '',
        quantity: '1',
      })
    },
    removeCreditItem(index) {
      this.form.credit_items.splice(index, 1)
      this.updateTotalAmount()
    },
  },
}
</script>
