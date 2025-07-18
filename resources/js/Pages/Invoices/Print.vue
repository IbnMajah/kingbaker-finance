<template>
  <div class="print-container">
    <Head :title="`Print Invoice ${invoice.invoice_number}`" />

    <!-- Print Header -->
    <div class="hidden print:block mb-4">
      <button @click="printInvoice" class="no-print bg-brand-600 text-white px-4 py-2 rounded mb-4">
        Print Invoice
      </button>
    </div>

    <!-- Invoice Document -->
    <div class="invoice-document bg-white">
      <!-- Company Header -->
      <div class="invoice-header mb-8">
        <div class="flex justify-between items-start">
          <div class="company-info">
            <h1 class="text-3xl font-bold text-brand-600 mb-2">King Bakers</h1>
            <div class="text-gray-600">
              <p>Bakery & Confectionery</p>
              <p>123 Business Street</p>
              <p>Banjul, The Gambia</p>
              <p>Tel: +220 123 4567</p>
              <p>Email: info@kingbakers.gm</p>
            </div>
          </div>
          <div class="invoice-title text-right">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">INVOICE</h2>
            <div class="text-gray-600">
              <p><strong>Invoice #:</strong> {{ invoice.invoice_number }}</p>
              <p><strong>Date:</strong> {{ $formatDate(invoice.invoice_date) }}</p>
              <p v-if="invoice.due_date"><strong>Due Date:</strong> {{ $formatDate(invoice.due_date) }}</p>
              <p><strong>Status:</strong>
                <span :class="getStatusClass(invoice.status)" class="px-2 py-1 rounded text-sm">
                  {{ getStatusLabel(invoice.status) }}
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Customer Information -->
      <div class="customer-info mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b-2 border-brand-200 pb-1">Bill To:</h3>
            <div class="space-y-1">
              <p class="font-semibold text-lg">{{ invoice.customer_name }}</p>
              <p v-if="invoice.customer_address" class="text-gray-600">{{ invoice.customer_address }}</p>
              <p v-if="invoice.customer_email" class="text-gray-600">{{ invoice.customer_email }}</p>
              <p v-if="invoice.customer_phone" class="text-gray-600">{{ invoice.customer_phone }}</p>
            </div>
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b-2 border-brand-200 pb-1">Invoice Details:</h3>
            <div class="space-y-1">
              <p><strong>Type:</strong> {{ getInvoiceTypeLabel(invoice.invoice_type) }}</p>
              <p><strong>Customer Type:</strong> {{ getCustomerTypeLabel(invoice.customer_type) }}</p>
              <p v-if="invoice.billing_period"><strong>Billing Period:</strong> {{ invoice.billing_period }}</p>
              <p v-if="invoice.bank_account"><strong>Account:</strong> {{ invoice.bank_account.name }}</p>
              <p v-if="invoice.branch"><strong>Branch:</strong> {{ invoice.branch.name }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoice Items/Description -->
      <div class="invoice-items mb-8">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 border-b-2 border-brand-200 pb-1">Description:</h3>
        <div class="bg-gray-50 p-4 rounded-md">
          <p class="text-gray-800 leading-relaxed">
            {{ invoice.description || 'Services/Products as discussed and agreed upon.' }}
          </p>
        </div>
      </div>

      <!-- Financial Summary -->
      <div class="financial-summary mb-8">
        <div class="max-w-md ml-auto">
          <div class="bg-gray-50 p-6 rounded-lg">
            <div class="space-y-3">
              <div class="flex justify-between items-center text-lg">
                <span class="font-medium">Invoice Amount:</span>
                <span class="font-bold">
                    {{ $formatAmount(invoice.amount) }}
                </span>
              </div>
              <div class="flex justify-between items-center text-lg text-green-600">
                <span class="font-medium">Amount Paid:</span>
                <span class="font-bold">
                    {{ $formatAmount(invoice.amount_paid) }}
                </span>
              </div>
              <div class="border-t border-gray-300 pt-3">
                <div class="flex justify-between items-center text-xl">
                  <span class="font-bold">Balance Due:</span>
                  <span class="font-bold" :class="remaining_amount > 0 ? 'text-red-600' : 'text-green-600'">
                    {{ $formatAmount(remaining_amount) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Information -->
      <div class="payment-info mb-8">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 border-b-2 border-brand-200 pb-1">Payment Information:</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-if="invoice.bank_account" class="bg-gray-50 p-4 rounded-md">
            <h4 class="font-semibold mb-2">Bank Transfer Details:</h4>
            <div class="text-sm space-y-1">
              <p><strong>Account Name:</strong> {{ invoice.bank_account.name }}</p>
              <p v-if="invoice.bank_account.bank_name"><strong>Bank:</strong> {{ invoice.bank_account.bank_name }}</p>
              <p v-if="invoice.bank_account.account_number"><strong>Account Number:</strong> {{ invoice.bank_account.account_number }}</p>
              <p v-if="!invoice.bank_account.account_number" class="text-gray-500 italic">Account number not specified</p>
            </div>
          </div>
          <div v-else class="bg-gray-50 p-4 rounded-md">
            <h4 class="font-semibold mb-2">Bank Transfer Details:</h4>
            <div class="text-sm space-y-1">
              <p class="text-gray-500 italic">No bank account specified for this invoice</p>
              <p class="text-gray-500 text-xs">Please contact us for payment instructions</p>
            </div>
          </div>

          <div class="bg-gray-50 p-4 rounded-md">
            <h4 class="font-semibold mb-2">Mobile Money:</h4>
            <div class="text-sm space-y-1">
              <p><strong>MyNafa:</strong> +220 123 4567</p>
              <p><strong>Wave:</strong> +220 123 4567</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Terms & Notes -->
      <!-- <div class="terms-notes mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b-2 border-brand-200 pb-1">Terms & Conditions:</h3>
            <div class="text-sm space-y-1 text-gray-600">
              <p>• Payment is due within 30 days of invoice date</p>
              <p>• All prices are in Gambian Dalasi (GMD)</p>
              <p>• Goods remain property of King Bakers until paid in full</p>
            </div>
          </div>
          <div v-if="invoice.notes">
            <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b-2 border-brand-200 pb-1">Notes:</h3>
            <div class="text-sm text-gray-600">
              <p>{{ invoice.notes }}</p>
            </div>
          </div>
        </div>
      </div> -->

      <!-- Footer -->
      <div class="invoice-footer text-center border-t-2 border-gray-200 pt-6">
        <div class="text-sm text-gray-600 space-y-1">
          <p class="font-semibold">Thank you for your business!</p>
          <p>For any questions regarding this invoice, please contact us at info@kingbakers.gm or +220 123 4567</p>
          <p class="text-xs">Generated on {{ $formatDate(new Date()) }} by {{ invoice.creator ? `${invoice.creator.first_name} ${invoice.creator.last_name}` : 'King Bakers Staff' }}</p>
        </div>
      </div>
    </div>

    <!-- Print Actions (visible only on screen) -->
    <div class="no-print mt-8 text-center space-x-4">
      <button @click="printInvoice" class="bg-brand-600 text-white px-6 py-3 rounded-md hover:bg-brand-700">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        Print Invoice
      </button>
      <Link :href="`/invoices/${invoice.id}`" class="bg-gray-600 text-white px-6 py-3 rounded-md hover:bg-gray-700">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Invoice
      </Link>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import { formatterMixin } from '@/Utils/formatters'

export default {
  components: {
    Head,
    Link,
  },
  mixins: [formatterMixin],
  layout: null, // No layout for print page
  props: {
      invoice: Object,
    remaining_amount: Number,
  },
  mounted() {
    // Auto-focus for immediate printing
    if (typeof window !== 'undefined') {
      window.focus()
      // Add keyboard shortcut for printing (Ctrl+P)
      document.addEventListener('keydown', this.handleKeydown)
    }
  },
  beforeUnmount() {
    // Clean up event listener
    if (typeof document !== 'undefined') {
      document.removeEventListener('keydown', this.handleKeydown)
    }
  },
  methods: {
    getStatusClass(status) {
      const classes = {
        'draft': 'bg-gray-200 text-gray-800',
        'sent': 'bg-blue-200 text-blue-800',
        'paid': 'bg-green-200 text-green-800',
        'partially_paid': 'bg-yellow-200 text-yellow-800',
        'overdue': 'bg-red-200 text-red-800',
        'cancelled': 'bg-gray-200 text-gray-800',
      }
      return classes[status] || 'bg-gray-200 text-gray-800'
    },
    getStatusLabel(status) {
      const labels = {
        'draft': 'Draft',
        'sent': 'Sent',
        'paid': 'Paid',
        'partially_paid': 'Partially Paid',
        'overdue': 'Overdue',
        'cancelled': 'Cancelled',
      }
      return labels[status] || status
    },
    getInvoiceTypeLabel(type) {
      const types = {
        'bulk_sales': 'Bulk Sales',
        'credit_customer': 'Credit Customer',
        'loans': 'Loans',
        'daily_supply': 'Daily Supply',
        'partner_billing': 'Partner Billing',
        'other': 'Other',
      }
      return types[type] || type
    },
    getCustomerTypeLabel(type) {
      const types = {
        'individual': 'Individual',
        'shop': 'Shop',
        'partner': 'Partner',
        'branch': 'Branch',
        'hotel': 'Hotel',
        'other': 'Other',
      }
      return types[type] || type
    },
    printInvoice() {
      if (typeof window !== 'undefined' && window.print) {
        window.print()
      } else {
        console.error('Print functionality not available')
      }
    },
    handleKeydown(e) {
      if (e.ctrlKey && e.key === 'p') {
        e.preventDefault() // Prevent default browser print dialog
        this.printInvoice()
      }
    },
  },
}
</script>

<style>
/* Print-specific styles */
.print-container {
  min-height: 100vh;
  background: #f5f5f5;
  padding: 20px;
}

.invoice-document {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px;
  box-shadow: 0 0 20px rgba(0,0,0,0.1);
  background: white;
}

/* Print media queries */
@media print {
  .print-container {
    background: white;
    padding: 0;
  }

  .invoice-document {
    box-shadow: none;
    padding: 20px;
    max-width: none;
    margin: 0;
  }

  .no-print {
    display: none !important;
  }

  /* Ensure proper page breaks */
  .invoice-header,
  .customer-info,
  .financial-summary {
    page-break-inside: avoid;
  }

  /* Adjust font sizes for print */
  body {
    font-size: 12pt;
    line-height: 1.4;
  }

  h1 { font-size: 24pt; }
  h2 { font-size: 20pt; }
  h3 { font-size: 16pt; }
  h4 { font-size: 14pt; }
}

/* Screen-only styles */
@media screen {
  .invoice-document {
    border-radius: 8px;
  }
}
</style>