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
            <!-- King Baker Logo -->
            <logo class="block mx-auto max-w-24 fill-white" height="12" />
            <div class="text-gray-600 text-sm">
              <p>Demba Burnafa Group</p>
              <p>Brusubi, Turntable</p>
              <p>Banjul Gambia</p>
            </div>
          </div>
          <div class="invoice-title text-right">
            <div class="text-gray-500 text-sm mb-2">FOR US, BY US</div>
            <div class="text-gray-800 text-sm space-y-1">
              <p><strong>Invoice:</strong> {{ invoice.invoice_number }}</p>
              <p><strong>Invoice Date:</strong> {{ $formatDate(invoice.invoice_date) }}</p>
              <p v-if="invoice.due_date"><strong>Due Date:</strong> {{ $formatDate(invoice.due_date) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Customer Information -->
      <div class="customer-info mb-8">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Bill To:</h3>
            <div class="space-y-1">
              <p class="font-semibold text-lg text-gray-900">{{ invoice.customer_name }}</p>
              <p v-if="invoice.customer_address" class="text-sm text-gray-600">{{ invoice.customer_address }}</p>
              <p v-if="invoice.customer_email" class="text-sm text-gray-600">{{ invoice.customer_email }}</p>
              <p v-if="invoice.customer_phone" class="text-sm text-gray-600">{{ invoice.customer_phone }}</p>
              <p v-if="invoice.customer_tax_id" class="text-sm text-gray-600">Tax ID: {{ invoice.customer_tax_id }}</p>
            </div>
          </div>
          <div class="text-right">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Invoice Details:</h3>
            <div class="space-y-1 text-sm">
              <p><strong>Type:</strong> {{ getInvoiceTypeLabel(invoice.invoice_type) }}</p>
              <p><strong>Customer Type:</strong> {{ getCustomerTypeLabel(invoice.customer_type) }}</p>
              <p v-if="invoice.billing_period"><strong>Billing Period:</strong> {{ invoice.billing_period }}</p>
              <p v-if="invoice.bank_account"><strong>Account:</strong> {{ invoice.bank_account.name }}</p>
              <p v-if="invoice.branch"><strong>Branch:</strong> {{ invoice.branch.name }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoice Items Table -->
      <div class="invoice-items mb-8">
        <div class="overflow-hidden border border-gray-200 rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DESCRIPTION</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QUANTITY</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UNIT PRICE</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TAXES</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AMOUNT</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="invoice.items && invoice.items.length > 0" v-for="(item, index) in invoice.items" :key="index">
                <td class="px-4 py-3 text-sm text-gray-900">{{ item.description || 'Service/Product' }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ parseFloat(item.quantity).toFixed(2) }} {{ item.unit_measurement || 'Units' }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $formatAmount(item.unit_price) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ item.tax_rate ? (item.tax_rate + '%') : '-' }}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $formatAmount(item.total) }}</td>
              </tr>
              <tr v-else>
                <td class="px-4 py-3 text-sm text-gray-900" colspan="5">
                  {{ invoice.description || 'Services/Products as discussed and agreed upon.' }}
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-white">
              <tr>
                <td class="px-4 py-3 text-sm font-medium text-gray-900" colspan="4">Total</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $formatAmount(invoice.amount) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
        
        <!-- Total in words -->
        <div class="mt-4 text-sm text-gray-500">
          <p>Total amount in words: <span class="font-medium">{{ amountInWords }}</span></p>
        </div>
      </div>

      <!-- Payment Information -->
      <div class="payment-info mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-2">Payment terms:</h3>
            <p class="text-sm text-gray-600">{{ invoice.payment_terms || 'End of Following Month' }}</p>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-2">Payment Communication:</h3>
            <p class="text-sm text-gray-600 font-medium">{{ invoice.payment_communication || 'PAYMENT FOR SERVICES RENDERED' }}</p>
          </div>
        </div>
        
        <div v-if="invoice.bank_account" class="mt-4">
          <h3 class="text-sm font-medium text-gray-700 mb-2">Bank Account:</h3>
          <p class="text-sm text-gray-600">{{ invoice.bank_account.account_number || '003202010191761141' }} - {{ invoice.bank_account.bank_name || 'Arab Gambian Islamic Bank' }}</p>
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
      <div class="invoice-footer border-t border-gray-200 pt-6">
        <div class="text-sm text-gray-600 space-y-2">
          <p>Terms & Conditions: https://kingbakers.net/terms</p>
          <div class="flex justify-between items-center text-xs text-gray-500">
            <div>
              <p>3733094 management@gmail.com http://kingbakers.net</p>
            </div>
            <div>
            </div>
          </div>
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
import Logo from '@/Shared/Logo.vue'

export default {
  components: {
    Head,
    Link,
    Logo,
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
  computed: {
    amountInWords() {
      return this.numberToWords(this.invoice.amount)
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
    numberToWords(num) {
      const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine']
      const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen']
      const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety']
      
      if (num === 0) return 'Zero'
      
      const convertHundreds = (n) => {
        let result = ''
        if (n > 99) {
          result += ones[Math.floor(n / 100)] + ' Hundred'
          n %= 100
          if (n > 0) result += ' '
        }
        if (n > 19) {
          result += tens[Math.floor(n / 10)]
          n %= 10
          if (n > 0) result += ' ' + ones[n]
        } else if (n > 9) {
          result += teens[n - 10]
        } else if (n > 0) {
          result += ones[n]
        }
        return result
      }
      
      const convertThousands = (n) => {
        if (n === 0) return ''
        if (n < 1000) return convertHundreds(n)
        return convertHundreds(Math.floor(n / 1000)) + ' Thousand ' + convertHundreds(n % 1000)
      }
      
      const integerPart = Math.floor(num)
      const decimalPart = Math.round((num - integerPart) * 100)
      
      let result = convertThousands(integerPart)
      if (result) result += ' Dalasi'
      
      if (decimalPart > 0) {
        if (result) result += ' and '
        result += convertHundreds(decimalPart) + ' Bututs'
      }
      
      return result || 'Zero Dalasi'
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
  .text-brand-brown { color: #9B672A !important; }
  .text-gray-800 { color: #1f2937 !important; }
  .text-gray-600 { color: #4b5563 !important; }
  .text-gray-500 { color: #6b7280 !important; }
  .text-green-600 { color: #059669 !important; }
  .text-red-600 { color: #dc2626 !important; }
  .text-blue-800 { color: #1e40af !important; }
  .text-yellow-800 { color: #92400e !important; }
  .bg-brand-brown { background-color: #9B672A !important; }

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