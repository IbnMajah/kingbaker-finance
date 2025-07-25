<template>
  <div class="print-container">
    <Head :title="`Print Invoice ${invoice.invoice_number}`" />

    <!-- Print Header -->
    <div class="no-print mb-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Invoice Preview</h1>
      <div class="space-x-2">
        <button @click="printInvoice" :disabled="isPrinting" class="bg-brand-600 text-white px-4 py-2 rounded-md hover:bg-brand-700 transition-colors duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
          <svg v-if="!isPrinting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isPrinting ? 'Opening Print Dialog...' : 'Print Invoice' }}
        </button>
        <Link :href="`/invoices/${invoice.id}`" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors duration-200 flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Back to Invoice
        </Link>
      </div>
    </div>

    <!-- Invoice Document -->
    <div class="invoice-document bg-white">
      <!-- Company Header -->
      <div class="invoice-header mb-8">
        <div class="flex justify-between items-start">
          <div class="company-info">
            <h1 class="text-3xl font-bold text-brand-600 mb-2">King Baker</h1>
            <div class="text-gray-600">
              <p>Bakery & Confectionery</p>
              <p>123 Business Street</p>
              <p>Banjul, The Gambia</p>
              <p>Tel: +220 123 4567</p>
              <p>Email: info@kingbaker.gm</p>
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
              <p>• Goods remain property of King Baker until paid in full</p>
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
          <p>For any questions regarding this invoice, please contact us at info@kingbaker.gm or +220 123 4567</p>
          <p class="text-xs">Generated on {{ $formatDate(new Date()) }} by {{ invoice.creator ? `${invoice.creator.first_name} ${invoice.creator.last_name}` : 'King Baker Staff' }}</p>
        </div>
      </div>
    </div>

    <!-- Print Actions (visible only on screen) -->
    <div class="no-print mt-8 text-center space-x-4">
      <button @click="printInvoice" :disabled="isPrinting" class="bg-brand-600 text-white px-6 py-3 rounded-md hover:bg-brand-700 transition-colors duration-200 shadow-md hover:shadow-lg transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
        <svg v-if="!isPrinting" class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        <svg v-else class="w-5 h-5 inline mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ isPrinting ? 'Opening Print Dialog...' : 'Print Invoice' }}
      </button>
      <Link :href="`/invoices/${invoice.id}`" class="inline-block bg-gray-600 text-white px-6 py-3 rounded-md hover:bg-gray-700 transition-colors duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Invoice
      </Link>
    </div>

    <!-- Print Instructions -->
    <div class="no-print mt-4 text-center">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 max-w-md mx-auto">
        <div class="flex items-center justify-center mb-2">
          <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p class="text-sm font-medium text-blue-800">Print Instructions</p>
        </div>
        <div class="text-xs text-blue-700 space-y-1">
          <p>• Click the "Print Invoice" button above</p>
          <p>• Use keyboard shortcut <kbd class="px-1 py-0.5 bg-blue-200 rounded text-xs">Ctrl+P</kbd></p>
          <p>• Make sure to select the correct printer settings</p>
        </div>
      </div>
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
  data() {
    return {
      isPrinting: false,
    }
  },
  mounted() {
    // Auto-focus for immediate printing
    if (typeof window !== 'undefined') {
      window.focus()
      // Add keyboard shortcut for printing (Ctrl+P)
      document.addEventListener('keydown', this.handleKeydown)

      // Add print event listeners for better UX
      window.addEventListener('beforeprint', this.handleBeforePrint)
      window.addEventListener('afterprint', this.handleAfterPrint)
    }
  },
  beforeUnmount() {
    // Clean up event listeners
    if (typeof document !== 'undefined') {
      document.removeEventListener('keydown', this.handleKeydown)
      window.removeEventListener('beforeprint', this.handleBeforePrint)
      window.removeEventListener('afterprint', this.handleAfterPrint)
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
        this.isPrinting = true
        // Add a small delay to ensure the page is fully rendered
        setTimeout(() => {
          window.print()
          // Reset printing state after a delay
          setTimeout(() => {
            this.isPrinting = false
          }, 1000)
        }, 100)
      } else {
        // Fallback for environments where print is not available
        alert('Print functionality is not available in this browser. Please use Ctrl+P or your browser\'s print menu.')
        console.error('Print functionality not available')
      }
    },
    handleKeydown(e) {
      if (e.ctrlKey && e.key === 'p') {
        e.preventDefault() // Prevent default browser print dialog
        this.printInvoice()
      }
    },
    handleBeforePrint() {
      // You can add any pre-print logic here
      console.log('Print dialog opened for invoice:', this.invoice.invoice_number)
    },
    handleAfterPrint() {
      // You can add any post-print logic here
      console.log('Print dialog closed for invoice:', this.invoice.invoice_number)
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
    color: #000 !important;
  }

  h1 { font-size: 24pt; }
  h2 { font-size: 20pt; }
  h3 { font-size: 16pt; }
  h4 { font-size: 14pt; }

  /* Ensure colors print correctly */
  .text-brand-600 { color: #2563eb !important; }
  .text-gray-800 { color: #1f2937 !important; }
  .text-gray-600 { color: #4b5563 !important; }
  .text-green-600 { color: #059669 !important; }
  .text-red-600 { color: #dc2626 !important; }
  .text-blue-800 { color: #1e40af !important; }
  .text-yellow-800 { color: #92400e !important; }

  /* Print optimization */
  .invoice-document {
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
    print-color-adjust: exact;
  }
}

/* Screen-only styles */
@media screen {
  .invoice-document {
    border-radius: 8px;
  }
}
</style>