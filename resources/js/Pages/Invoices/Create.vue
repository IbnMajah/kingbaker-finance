<template>
  <div>
    <Head title="Create Invoice" />

    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center space-x-4 mb-2">
        <Link href="/invoices" class="text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
          </svg>
        </Link>
        <h1 class="text-3xl font-bold">Create Invoice</h1>
      </div>
      <p class="text-gray-600">Generate a new customer invoice for billing and tracking</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <form @submit.prevent="store">
        <!-- Form Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold">Invoice Information</h2>
        </div>

        <!-- Form Body -->
        <div class="p-6 space-y-6">
          <!-- Invoice Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Invoice Number -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
              <div class="relative">
                <input
                  v-model="form.invoice_number"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="[
                    form.errors.invoice_number ? 'border-red-300' : 'border-gray-300',
                    invoiceNumberValidation.isValid === false ? 'border-red-500' : '',
                    invoiceNumberValidation.isValid === true ? 'border-green-500' : ''
                  ]"
                  placeholder="Auto-generated if empty"
                  @input="validateInvoiceNumber"
                  @focus="startInvoiceNumberValidation"
                  @blur="stopInvoiceNumberValidation"
                />
                <!-- Validation Status Icons -->
                <div v-if="invoiceNumberValidation.isValid === true" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div v-if="invoiceNumberValidation.isValid === false" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div v-if="invoiceNumberValidation.isValidating" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </div>
              <div v-if="form.errors.invoice_number" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_number }}</div>
              <div v-if="invoiceNumberValidation.isValid === false" class="mt-1 text-sm text-red-600">
                This invoice number already exists. Please choose a different one.
              </div>
              <div v-if="invoiceNumberValidation.isValid === true" class="mt-1 text-sm text-green-600">
                Invoice number is available.
              </div>
              <p class="mt-1 text-sm text-gray-500">Leave empty to auto-generate</p>
            </div>
            <!-- Invoice Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date *</label>
              <input
                v-model="form.invoice_date"
                type="date"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.invoice_date ? 'border-red-300' : 'border-gray-300'"
              />
              <div v-if="form.errors.invoice_date" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_date }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Invoice Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Type *</label>
              <select
                v-model="form.invoice_type"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.invoice_type ? 'border-red-300' : 'border-gray-300'"
              >
                <option value="">Select Invoice Type</option>
                <option v-for="type in invoiceTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
              <div v-if="form.errors.invoice_type" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_type }}</div>
            </div>
            <!-- Due Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <input
                v-model="form.due_date"
                type="date"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.due_date ? 'border-red-300' : 'border-gray-300'"
                :min="form.invoice_date"
              />
              <div v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</div>
            </div>
          </div>

          <!-- Customer Information -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Customer Name -->
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
              <input
                v-model="form.customer_name"
                type="text"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.customer_name ? 'border-red-300' : 'border-gray-300'"
                placeholder="Enter customer name"
                @input="searchCustomers"
                @focus="showCustomerSuggestions = true"
                @blur="hideSuggestions"
              />
              <div v-if="form.errors.customer_name" class="mt-1 text-sm text-red-600">{{ form.errors.customer_name }}</div>
              
              <!-- Customer Suggestions Dropdown -->
              <div v-if="showCustomerSuggestions && customerSuggestions.length > 0" 
                   class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                <div v-for="customer in customerSuggestions" 
                     :key="customer.id"
                     class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                     @mousedown="selectCustomer(customer)">
                  <div class="font-medium text-gray-900">{{ customer.name }}</div>
                  <div v-if="customer.email" class="text-sm text-gray-600">{{ customer.email }}</div>
                  <div v-if="customer.phone" class="text-sm text-gray-600">{{ customer.phone }}</div>
                </div>
              </div>
            </div>
              <!-- Customer Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Type *</label>
                <select
                  v-model="form.customer_type"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_type ? 'border-red-300' : 'border-gray-300'"
                >
                  <option value="">Select Customer Type</option>
                  <option v-for="type in customerTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
                <div v-if="form.errors.customer_type" class="mt-1 text-sm text-red-600">{{ form.errors.customer_type }}</div>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                  v-model="form.customer_email"
                  type="email"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_email ? 'border-red-300' : 'border-gray-300'"
                  placeholder="customer@example.com"
                />
                <div v-if="form.errors.customer_email" class="mt-1 text-sm text-red-600">{{ form.errors.customer_email }}</div>
              </div>
              <!-- Phone -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input
                  v-model="form.customer_phone"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.customer_phone ? 'border-red-300' : 'border-gray-300'"
                  placeholder="+220 123 4567"
                />
                <div v-if="form.errors.customer_phone" class="mt-1 text-sm text-red-600">{{ form.errors.customer_phone }}</div>
              </div>
            </div>
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
              <textarea
                v-model="form.customer_address"
                rows="3"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :class="form.errors.customer_address ? 'border-red-300' : 'border-gray-300'"
                placeholder="Customer address"
              ></textarea>
              <div v-if="form.errors.customer_address" class="mt-1 text-sm text-red-600">{{ form.errors.customer_address }}</div>
            </div>
          </div>

          <!-- Financial Details -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Details</h3>
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-green-800">Credit Only Module</h3>
                  <p class="mt-1 text-sm text-green-700">
                    All invoices are credit transactions. When paid, the specified bank account will be credited with the invoice amount.
                  </p>
                </div>
              </div>
            </div>
            <!-- Invoice Items -->
            <div class="col-span-full">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900">Invoice Items</h3>
                </div>
                <div class="text-right">
                  <div class="text-sm text-gray-500">Total</div>
                  <div class="text-2xl font-bold text-brand-600">GMD {{ totalAmount.toFixed(2) }}</div>
                </div>
              </div>

              <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <!-- Items List -->
                <div class="divide-y divide-gray-200">
                  <div v-for="(item, index) in form.items" :key="index" class="p-6">
                    <div class="flex items-center justify-between mb-4">
                      <h4 class="text-sm font-medium text-gray-900">Item #{{ index + 1 }}</h4>
                      <button
                        type="button"
                        @click="removeItem(index)"
                        class="text-red-600 hover:text-red-800 p-1"
                        :disabled="form.items.length <= 1"
                      >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 012 0v3a1 1 0 11-2 0V9zm4 0a1 1 0 012 0v3a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                        </svg>
                      </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                      <!-- Description -->
                      <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <input
                          v-model="item.description"
                          type="text"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                          :class="form.errors[`items.${index}.description`] ? 'border-red-300' : 'border-gray-300'"
                          placeholder="e.g., Product or service"
                          required
                        />
                        <div v-if="form.errors[`items.${index}.description`]" class="mt-1 text-sm text-red-600">
                          {{ form.errors[`items.${index}.description`] }}
                        </div>
                      </div>

                      <!-- Unit Price -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price *</label>
                        <input
                          v-model.number="item.unit_price"
                          type="number"
                          step="0.01"
                          min="0.01"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                          :class="form.errors[`items.${index}.unit_price`] ? 'border-red-300' : 'border-gray-300'"
                          placeholder="0.00"
                          required
                          @input="calculateItemTotal(index)"
                        />
                        <div v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-sm text-red-600">
                          {{ form.errors[`items.${index}.unit_price`] }}
                        </div>
                      </div>

                      <!-- Unit Measurement -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <input
                          v-model="item.unit_measurement"
                          type="text"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                          :class="form.errors[`items.${index}.unit_measurement`] ? 'border-red-300' : 'border-gray-300'"
                          placeholder="e.g., each, kg, pcs"
                        />
                        <div v-if="form.errors[`items.${index}.unit_measurement`]" class="mt-1 text-sm text-red-600">
                          {{ form.errors[`items.${index}.unit_measurement`] }}
                        </div>
                      </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                      <!-- Quantity -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input
                          v-model.number="item.quantity"
                          type="number"
                          min="0.01"
                          step="0.01"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                          :class="form.errors[`items.${index}.quantity`] ? 'border-red-300' : 'border-gray-300'"
                          placeholder="1.00"
                          required
                          @input="calculateItemTotal(index)"
                        />
                        <div v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-sm text-red-600">
                          {{ form.errors[`items.${index}.quantity`] }}
                        </div>
                      </div>
                    </div>

                    <!-- Item Total -->
                    <div class="mt-4 flex justify-end">
                      <div class="text-right">
                        <div class="text-sm text-gray-500">Item Total:</div>
                        <div class="text-lg font-bold text-blue-600">
                          GMD {{ ((item.unit_price || 0) * (item.quantity || 0)).toFixed(2) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Add Item Button -->
                <div class="px-6 py-4 border-t border-gray-200 border-dashed">
                  <button
                    type="button"
                    @click="addItem"
                    class="w-full flex items-center justify-center px-4 py-3 text-brand-600 hover:text-brand-700 text-sm font-medium border-2 border-dashed border-gray-300 rounded-lg hover:border-brand-400 transition-colors"
                  >
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    + Add Another Item
                  </button>
                </div>
              </div>
              <div v-if="form.errors.items" class="mt-1 text-sm text-red-600">{{ form.errors.items }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Bank Account (moved here from above) -->
              <!-- Bank Account -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account to Credit *</label>
                <select
                  v-model="form.bank_account_id"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.bank_account_id ? 'border-red-300' : 'border-gray-300'"
                >
                  <option value="">Select Bank Account</option>
                  <option v-for="account in bankAccounts" :key="account.value" :value="account.value">{{ account.label }}</option>
                </select>
                <div v-if="form.errors.bank_account_id" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_id }}</div>
                <p class="mt-1 text-sm text-gray-500">Account to be credited when payment is received</p>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Branch -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select
                  v-model="form.branch_id"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.branch_id ? 'border-red-300' : 'border-gray-300'"
                >
                  <option value="">Select Branch</option>
                  <option v-for="branch in branches" :key="branch.value" :value="branch.value">{{ branch.label }}</option>
                </select>
                <div v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600">{{ form.errors.branch_id }}</div>
              </div>
              <!-- Billing Period (if daily_supply) -->
              <div v-if="form.invoice_type === 'daily_supply'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Billing Period</label>
                <input
                  v-model="form.billing_period"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                  :class="form.errors.billing_period ? 'border-red-300' : 'border-gray-300'"
                  placeholder="e.g. Jan 2024"
                />
                <div v-if="form.errors.billing_period" class="mt-1 text-sm text-red-600">{{ form.errors.billing_period }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <Link href="/invoices" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
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
            <span v-else>Create Invoice</span>
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
    bankAccounts: Array,
    branches: Array,
    invoiceTypes: Array,
    customerTypes: Array,
    recurringFrequencies: Array,
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        invoice_number: '',
        customer_name: '',
        customer_email: '',
        customer_phone: '',
        customer_address: '',
        invoice_date: new Date().toISOString().split('T')[0],
        due_date: '',
        items: [
          {
            description: '',
            unit_price: '',
            unit_measurement: '',
            quantity: 1,
          }
        ],
        invoice_type: '',
        customer_type: '',
        description: '',
        notes: '',
        bank_account_id: '',
        branch_id: '',
        billing_period: '',
        is_recurring: false,
        recurring_frequency: '',
        next_invoice_date: '',
        attachment: null,
      }),
      customerSuggestions: [],
      showCustomerSuggestions: false,
      searchTimeout: null,
      invoiceNumberValidation: {
        isValid: null, // null = not validated, true = valid, false = invalid
        isValidating: false,
        isFocused: false,
      },
      invoiceNumberValidationTimeout: null,
    }
  },
  watch: {
    'form.invoice_type'(newType) {
      // Auto-set due date based on invoice type
      if (newType === 'daily_supply') {
        // Monthly billing - set due date to end of next month
        const nextMonth = new Date()
        nextMonth.setMonth(nextMonth.getMonth() + 1)
        nextMonth.setDate(new Date(nextMonth.getFullYear(), nextMonth.getMonth() + 1, 0).getDate())
        this.form.due_date = nextMonth.toISOString().split('T')[0]

        // Set billing period to current month
        const now = new Date()
        this.form.billing_period = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
      } else if (newType === 'bulk_sales') {
        // 30 days from invoice date
        const dueDate = new Date(this.form.invoice_date)
        dueDate.setDate(dueDate.getDate() + 30)
        this.form.due_date = dueDate.toISOString().split('T')[0]
      }
    },
    'form.is_recurring'(isRecurring) {
      if (isRecurring && !this.form.next_invoice_date) {
        // Set next invoice date to 1 month from invoice date by default
        const nextDate = new Date(this.form.invoice_date)
        nextDate.setMonth(nextDate.getMonth() + 1)
        this.form.next_invoice_date = nextDate.toISOString().split('T')[0]
      }
    }
  },
  computed: {
    totalAmount() {
      return this.form.items.reduce((total, item) => {
        const itemTotal = (parseFloat(item.unit_price) || 0) * (parseFloat(item.quantity) || 0)
        return total + itemTotal
      }, 0)
    }
  },
  methods: {
    async store() {
      // Validate invoice number if provided
      if (this.form.invoice_number && this.invoiceNumberValidation.isValid === false) {
        alert('Please choose a different invoice number. The current one already exists.')
        return
      }

      // Save customer first if needed
      await this.saveCustomer()
      this.form.post('/invoices')
    },
    getDescriptionPlaceholder() {
      const placeholders = {
        'bulk_sales': 'Describe the bulk order for shops (e.g., quantity, items, delivery details)',
        'credit_customer': 'Details of goods/services provided on credit to customer',
        'loans': 'Loan repayment details and terms (credit transaction)',
        'daily_supply': 'Summary of daily supplies provided to hotel/partner during billing period',
        'partner_billing': 'Services provided to partner/branch - bills owed to us',
        'other': 'Unusual payments made to the business (credit transaction)'
      }
      return placeholders[this.form.invoice_type] || 'Invoice description (credit transaction to specified bank account)'
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
      if (this.form.items.length > 1) {
        this.form.items.splice(index, 1)
      }
    },
    calculateItemTotal(index) {
      // This method triggers reactivity for computed total
      // The actual calculation is done in the template and computed property
      this.$forceUpdate()
    },
    searchCustomers() {
      // Clear existing timeout
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout)
      }

      // Set new timeout for debounced search
      this.searchTimeout = setTimeout(() => {
        if (this.form.customer_name.length >= 2) {
          this.performCustomerSearch()
        } else {
          this.customerSuggestions = []
        }
      }, 300) // 300ms debounce
    },
    async performCustomerSearch() {
      try {
        const response = await fetch(`/invoices/customers/search?q=${encodeURIComponent(this.form.customer_name)}`, {
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
        })
        const customers = await response.json()
        this.customerSuggestions = customers
        this.showCustomerSuggestions = true
      } catch (error) {
        console.error('Error searching customers:', error)
        this.customerSuggestions = []
      }
    },
    selectCustomer(customer) {
      this.form.customer_name = customer.name
      this.form.customer_email = customer.email || ''
      this.form.customer_phone = customer.phone || ''
      this.form.customer_address = customer.address || ''
      
      this.customerSuggestions = []
      this.showCustomerSuggestions = false
    },
    hideSuggestions() {
      // Use setTimeout to allow click events to fire before hiding
      setTimeout(() => {
        this.showCustomerSuggestions = false
      }, 200)
    },
    async saveCustomer() {
      if (!this.form.customer_name.trim()) return

      try {
        const response = await fetch('/invoices/customers/create-or-find', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            name: this.form.customer_name,
            email: this.form.customer_email,
            phone: this.form.customer_phone,
            address: this.form.customer_address
          })
        })

        const customer = await response.json()
        
        // Update form with the customer data
        this.form.customer_name = customer.name
        this.form.customer_email = customer.email || ''
        this.form.customer_phone = customer.phone || ''
        this.form.customer_address = customer.address || ''

        // Show success message if customer was created
        if (customer.created) {
          // You could add a toast notification here
          console.log('New customer created:', customer.name)
        }
      } catch (error) {
        console.error('Error saving customer:', error)
      }
    },
    startInvoiceNumberValidation() {
      this.invoiceNumberValidation.isFocused = true
      // Reset validation state when focusing
      this.invoiceNumberValidation.isValid = null
    },
    stopInvoiceNumberValidation() {
      this.invoiceNumberValidation.isFocused = false
      // Clear timeout when losing focus
      if (this.invoiceNumberValidationTimeout) {
        clearTimeout(this.invoiceNumberValidationTimeout)
        this.invoiceNumberValidationTimeout = null
      }
    },
    validateInvoiceNumber() {
      // Clear existing timeout
      if (this.invoiceNumberValidationTimeout) {
        clearTimeout(this.invoiceNumberValidationTimeout)
      }

      // Reset validation state
      this.invoiceNumberValidation.isValid = null
      this.invoiceNumberValidation.isValidating = false

      // Only validate if field is focused and has content
      if (!this.invoiceNumberValidation.isFocused || !this.form.invoice_number.trim()) {
        return
      }

      // Set new timeout for debounced validation
      this.invoiceNumberValidationTimeout = setTimeout(() => {
        this.performInvoiceNumberValidation()
      }, 500) // 500ms debounce
    },
    async performInvoiceNumberValidation() {
      if (!this.form.invoice_number.trim()) {
        this.invoiceNumberValidation.isValid = null
        this.invoiceNumberValidation.isValidating = false
        return
      }

      this.invoiceNumberValidation.isValidating = true

      try {
        const response = await fetch(`/invoices/validate-number?number=${encodeURIComponent(this.form.invoice_number)}`)
        const result = await response.json()
        
        this.invoiceNumberValidation.isValid = result.available
        this.invoiceNumberValidation.isValidating = false
      } catch (error) {
        console.error('Error validating invoice number:', error)
        this.invoiceNumberValidation.isValid = null
        this.invoiceNumberValidation.isValidating = false
      }
    },
  },
}
</script>