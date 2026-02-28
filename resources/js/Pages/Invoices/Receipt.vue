<template>
  <div class="print-container">
    <Head :title="`Receipt - Invoice ${invoice.invoice_number}`" />

    <!-- Print Header (screen only) -->
    <div class="no-print mb-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Receipt Preview</h1>
      <div class="space-x-2 flex">
        <button @click="printReceipt" :disabled="isPrinting" class="bg-brand-600 text-white px-4 py-2 rounded-md hover:bg-brand-700 transition-colors duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
          <svg v-if="!isPrinting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isPrinting ? 'Opening Print Dialog...' : 'Print Receipt' }}
        </button>
        <Link :href="`/invoices/${invoice.id}`" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors duration-200 flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Back to Invoice
        </Link>
      </div>
    </div>

    <!-- Receipt Document -->
    <div class="receipt-document bg-white">
      <!-- Company Header -->
      <div class="receipt-header mb-8">
        <div class="flex justify-between items-start">
          <div class="company-info">
            <logo class="block mx-auto max-w-24 fill-white" height="12" />
            <div class="text-gray-600 text-sm">
              <p>Demba Burnafa Group</p>
              <p>Brusubi, Turntable</p>
              <p>Banjul Gambia</p>
            </div>
          </div>
          <div class="text-right">
            <h2 class="text-2xl font-bold text-[#9B672A] mb-2">RECEIPT</h2>
            <div class="text-gray-500 text-sm mb-2">FOR US, BY US</div>
            <div class="text-gray-800 text-sm space-y-1">
              <p><strong>Receipt For:</strong> {{ invoice.invoice_number }}</p>
              <p><strong>Invoice Date:</strong> {{ $formatDate(invoice.invoice_date) }}</p>
              <p><strong>Receipt Date:</strong> {{ $formatDate(receiptDate) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Badge -->
      <div class="mb-8 flex justify-center">
        <span
          class="inline-block px-6 py-2 rounded-full text-sm font-bold tracking-wider uppercase"
          :class="isPaid ? 'bg-green-100 text-green-800 border-2 border-green-300' : 'bg-yellow-100 text-yellow-800 border-2 border-yellow-300'"
        >
          {{ isPaid ? 'PAID IN FULL' : 'PARTIALLY PAID' }}
        </span>
      </div>

      <!-- Customer Information -->
      <div class="customer-info mb-8">
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Received From:</h3>
            <div class="space-y-1">
              <p class="font-semibold text-lg text-gray-900">{{ invoice.customer_name }}</p>
              <p v-if="invoice.customer_address" class="text-sm text-gray-600">{{ invoice.customer_address }}</p>
              <p v-if="invoice.customer_email" class="text-sm text-gray-600">{{ invoice.customer_email }}</p>
              <p v-if="invoice.customer_phone" class="text-sm text-gray-600">{{ invoice.customer_phone }}</p>
            </div>
          </div>
          <div class="text-right">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Details:</h3>
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
      <div class="receipt-items mb-8">
        <div class="overflow-hidden border border-gray-200 rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DESCRIPTION</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QUANTITY</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UNIT PRICE</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DISCOUNT</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TAXES</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AMOUNT</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="invoice.items && invoice.items.length > 0" v-for="(item, index) in invoice.items" :key="index">
                <td class="px-4 py-3 text-sm text-gray-900">{{ item.description || 'Service/Product' }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ parseFloat(item.quantity).toFixed(2) }} {{ item.unit_measurement || 'Units' }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $formatAmount(item.unit_price) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">
                  <span v-if="item.discount > 0" class="text-red-600">-{{ $formatAmount(item.discount) }}</span>
                  <span v-else>-</span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ item.tax_rate ? (item.tax_rate + '%') : '-' }}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $formatAmount(item.total) }}</td>
              </tr>
              <tr v-else>
                <td class="px-4 py-3 text-sm text-gray-900" colspan="6">
                  {{ invoice.description || 'Services/Products as discussed and agreed upon.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Payment Summary -->
      <div class="mb-8">
        <div class="overflow-hidden border border-gray-200 rounded-lg">
          <table class="min-w-full">
            <tbody class="divide-y divide-gray-200">
              <tr class="bg-white">
                <td class="px-4 py-3 text-sm font-medium text-gray-700">Invoice Total</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ $formatAmount(invoice.amount) }}</td>
              </tr>
              <tr class="bg-white">
                <td class="px-4 py-3 text-sm font-medium text-green-700">Amount Paid</td>
                <td class="px-4 py-3 text-sm font-bold text-green-700 text-right">{{ $formatAmount(invoice.amount_paid) }}</td>
              </tr>
              <tr v-if="balanceDue > 0" class="bg-white">
                <td class="px-4 py-3 text-sm font-medium text-red-700">Balance Due</td>
                <td class="px-4 py-3 text-sm font-bold text-red-700 text-right">{{ $formatAmount(balanceDue) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Amount in words -->
        <div class="mt-4 text-sm text-gray-500">
          <p>Amount paid in words: <span class="font-medium">{{ amountPaidInWords }}</span></p>
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

      <!-- Footer -->
      <div class="receipt-footer border-t border-gray-200 pt-6">
        <div class="text-sm text-gray-600 space-y-2">
          <p class="text-center text-gray-500 italic">Thank you for your payment.</p>
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

    <!-- Print Actions (screen only) -->
    <div class="no-print mt-8 text-center space-x-4">
      <button @click="printReceipt" :disabled="isPrinting" class="bg-brand-600 text-white px-6 py-3 rounded-md hover:bg-brand-700 transition-colors duration-200 shadow-md hover:shadow-lg transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
        <svg v-if="!isPrinting" class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        <svg v-else class="w-5 h-5 inline mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ isPrinting ? 'Opening Print Dialog...' : 'Print Receipt' }}
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
          <p>Click the "Print Receipt" button above</p>
          <p>Use keyboard shortcut <kbd class="px-1 py-0.5 bg-blue-200 rounded text-xs">Ctrl+P</kbd></p>
          <p>Make sure to select the correct printer settings</p>
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
  layout: null,
  props: {
    invoice: Object,
  },
  data() {
    return {
      isPrinting: false,
    }
  },
  computed: {
    isPaid() {
      return this.invoice.status === 'paid'
    },
    balanceDue() {
      return (this.invoice.amount || 0) - (this.invoice.amount_paid || 0)
    },
    receiptDate() {
      return new Date().toISOString().split('T')[0]
    },
    amountPaidInWords() {
      return this.numberToWords(this.invoice.amount_paid)
    },
  },
  mounted() {
    if (typeof window !== 'undefined') {
      window.focus()
      document.addEventListener('keydown', this.handleKeydown)
      window.addEventListener('beforeprint', this.handleBeforePrint)
      window.addEventListener('afterprint', this.handleAfterPrint)
    }
  },
  beforeUnmount() {
    if (typeof document !== 'undefined') {
      document.removeEventListener('keydown', this.handleKeydown)
      window.removeEventListener('beforeprint', this.handleBeforePrint)
      window.removeEventListener('afterprint', this.handleAfterPrint)
    }
  },
  methods: {
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
    printReceipt() {
      if (typeof window !== 'undefined' && window.print) {
        this.isPrinting = true
        setTimeout(() => {
          window.print()
          setTimeout(() => {
            this.isPrinting = false
          }, 1000)
        }, 100)
      } else {
        alert('Print functionality is not available in this browser. Please use Ctrl+P or your browser\'s print menu.')
      }
    },
    handleKeydown(e) {
      if (e.ctrlKey && e.key === 'p') {
        e.preventDefault()
        this.printReceipt()
      }
    },
    handleBeforePrint() {},
    handleAfterPrint() {},
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
.print-container {
  min-height: 100vh;
  background: #f5f5f5;
  padding: 20px;
}

.receipt-document {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px;
  box-shadow: 0 0 20px rgba(0,0,0,0.1);
  background: white;
}

@media print {
  .print-container {
    background: white;
    padding: 0;
  }

  .receipt-document {
    box-shadow: none;
    padding: 20px;
    max-width: none;
    margin: 0;
  }

  .no-print {
    display: none !important;
  }

  .receipt-header,
  .customer-info,
  .receipt-items {
    page-break-inside: avoid;
  }

  body {
    font-size: 12pt;
    line-height: 1.4;
    color: #000 !important;
  }

  h1 { font-size: 24pt; }
  h2 { font-size: 20pt; }
  h3 { font-size: 16pt; }
  h4 { font-size: 14pt; }

  .text-brand-brown { color: #9B672A !important; }
  .text-gray-800 { color: #1f2937 !important; }
  .text-gray-600 { color: #4b5563 !important; }
  .text-gray-500 { color: #6b7280 !important; }
  .text-green-600 { color: #059669 !important; }
  .text-green-700 { color: #047857 !important; }
  .text-green-800 { color: #065f46 !important; }
  .text-red-600 { color: #dc2626 !important; }
  .text-red-700 { color: #b91c1c !important; }
  .text-yellow-800 { color: #92400e !important; }

  .receipt-document {
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
    print-color-adjust: exact;
  }
}

@media screen {
  .receipt-document {
    border-radius: 8px;
  }
}
</style>
