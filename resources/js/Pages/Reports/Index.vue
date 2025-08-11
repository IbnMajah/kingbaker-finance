<template>
  <div>
    <Head title="Reports" />
    
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Financial Reports & Analytics</h1>
      <p class="mt-2 text-gray-600">Comprehensive reporting across all business modules</p>
    </div>

    <!-- Quick Stats Overview -->
    <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100 text-green-600">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(overview.totalRevenue) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-red-100 text-red-600">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Expenses</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(overview.totalExpenses) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100 text-blue-600">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Net Profit</p>
            <p class="text-2xl font-semibold" :class="overview.netProfit >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatCurrency(overview.netProfit) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Pending Items</p>
            <p class="text-2xl font-semibold text-gray-900">{{ overview.pendingItems }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Module-Based Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">

      <!-- Financial Transactions Report -->
      <div v-if="canViewTransactions" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Financial Transactions</h3>
              <p class="text-sm text-gray-500">Track all financial movements</p>
            </div>
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Account Filter</label>
              <select v-model="filters.transactions.accountId" class="w-full border border-gray-300 rounded-md px-3 py-2">
                <option value="">All Accounts</option>
                <option v-for="account in accounts" :key="account.id" :value="account.id">
                  {{ account.name }}
                </option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input v-model="filters.transactions.fromDate" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input v-model="filters.transactions.toDate" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2">
              </div>
            </div>
            <div class="h-64">
              <canvas ref="transactionsChart"></canvas>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-gray-200 bg-gray-50">
          <div class="flex gap-2">
            <button @click="generateReport('transactions', 'pdf')" class="btn-primary">
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
              PDF
            </button>
            <button @click="generateReport('transactions', 'excel')" class="btn-secondary">
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
              </svg>
              Excel
            </button>
          </div>
        </div>
      </div>

      <!-- Sales Performance Report -->
      <div v-if="canViewSales" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Sales Performance</h3>
              <p class="text-sm text-gray-500">Track sales across branches</p>
            </div>
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Branch Filter</label>
              <select v-model="filters.sales.branchId" class="w-full border border-gray-300 rounded-md px-3 py-2">
                <option value="">All Branches</option>
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                  {{ branch.name }}
                </option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                <select v-model="filters.sales.period" class="w-full border border-gray-300 rounded-md px-3 py-2">
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                  <option value="yearly">Yearly</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                <select v-model="filters.sales.year" class="w-full border border-gray-300 rounded-md px-3 py-2">
                  <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                </select>
              </div>
            </div>
            <div class="h-64">
              <canvas ref="salesChart"></canvas>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-gray-200 bg-gray-50">
          <div class="flex gap-2">
            <button @click="generateReport('sales', 'pdf')" class="btn-primary">PDF</button>
            <button @click="generateReport('sales', 'excel')" class="btn-secondary">Excel</button>
          </div>
        </div>
      </div>

      <!-- Expense Claims Report -->
      <div v-if="canViewExpenses" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Expense Claims</h3>
              <p class="text-sm text-gray-500">Track expense patterns</p>
            </div>
            <div class="p-2 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select v-model="filters.expenses.category" class="w-full border border-gray-300 rounded-md px-3 py-2">
                  <option value="">All Categories</option>
                  <option v-for="category in expenseCategories" :key="category" :value="category">
                    {{ category }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select v-model="filters.expenses.status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                  <option value="">All Statuses</option>
                  <option value="draft">Draft</option>
                  <option value="active">Active</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
            </div>
            <div class="h-64">
              <canvas ref="expensesChart"></canvas>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-gray-200 bg-gray-50">
          <div class="flex gap-2">
            <button @click="generateReport('expenses', 'pdf')" class="btn-primary">PDF</button>
            <button @click="generateReport('expenses', 'excel')" class="btn-secondary">Excel</button>
          </div>
        </div>
      </div>

      <!-- Invoices & Bills Report -->
      <div v-if="canViewInvoices || canViewBills" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Invoices & Bills</h3>
              <p class="text-sm text-gray-500">Track receivables & payables</p>
            </div>
            <div class="p-2 bg-purple-100 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select v-model="filters.invoicesBills.type" class="w-full border border-gray-300 rounded-md px-3 py-2">
                  <option value="">Both</option>
                  <option value="invoices">Invoices</option>
                  <option value="bills">Bills</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select v-model="filters.invoicesBills.status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                  <option value="">All Statuses</option>
                  <option value="pending">Pending</option>
                  <option value="paid">Paid</option>
                  <option value="overdue">Overdue</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
            </div>
            <div class="h-64">
              <canvas ref="invoicesBillsChart"></canvas>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-gray-200 bg-gray-50">
          <div class="flex gap-2">
            <button @click="generateReport('invoices-bills', 'pdf')" class="btn-primary">PDF</button>
            <button @click="generateReport('invoices-bills', 'excel')" class="btn-secondary">Excel</button>
          </div>
        </div>
      </div>

      <!-- Bank Accounts Report -->
      <div v-if="canViewBankAccounts" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Bank Accounts</h3>
              <p class="text-sm text-gray-500">Account balances & movements</p>
            </div>
            <div class="p-2 bg-indigo-100 rounded-lg">
              <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Bank Filter</label>
              <select v-model="filters.bankAccounts.bank" class="w-full border border-gray-300 rounded-md px-3 py-2">
                <option value="">All Banks</option>
                <option v-for="bank in uniqueBanks" :key="bank" :value="bank">
                  {{ bank }}
                </option>
              </select>
            </div>
            <div class="h-64">
              <canvas ref="bankAccountsChart"></canvas>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-gray-200 bg-gray-50">
          <div class="flex gap-2">
            <button @click="generateReport('bank-accounts', 'pdf')" class="btn-primary">PDF</button>
            <button @click="generateReport('bank-accounts', 'excel')" class="btn-secondary">Excel</button>
          </div>
        </div>
      </div>

      <!-- Comprehensive Cash Flow Report -->
      <div v-if="isAdmin" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Cash Flow Analysis</h3>
              <p class="text-sm text-gray-500">Complete financial overview</p>
            </div>
            <div class="p-2 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                <select v-model="filters.cashFlow.period" class="w-full border border-gray-300 rounded-md px-3 py-2">
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="yearly">Yearly</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                <select v-model="filters.cashFlow.year" class="w-full border border-gray-300 rounded-md px-3 py-2">
                  <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                </select>
              </div>
            </div>
            <div class="h-64">
              <canvas ref="cashFlowChart"></canvas>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-gray-200 bg-gray-50">
          <div class="flex gap-2">
            <button @click="generateReport('cash-flow', 'pdf')" class="btn-primary">PDF</button>
            <button @click="generateReport('cash-flow', 'excel')" class="btn-secondary">Excel</button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Chart from 'chart.js/auto'
import { usePermissions } from '@/composables/usePermissions.js'

export default {
  components: {
    Head,
  },
  layout: Layout,
  setup() {
    const {
      isAdmin,
      canViewTransactions,
      canViewSales,
      canViewExpenses,
      canViewInvoices,
      canViewBills,
      canViewBankAccounts,
    } = usePermissions()

    return {
      isAdmin,
      canViewTransactions,
      canViewSales,
      canViewExpenses,
      canViewInvoices,
      canViewBills,
      canViewBankAccounts,
    }
  },
  props: {
    overview: {
      type: Object,
      required: true,
    },
    accounts: {
      type: Array,
      default: () => [],
    },
    branches: {
      type: Array,
      default: () => [],
    },
    expenseCategories: {
      type: Array,
      default: () => [],
    },
    chartData: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    uniqueBanks() {
      return [...new Set(this.accounts.map(account => account.bank_name).filter(Boolean))]
    },
  },
  data() {
    return {
      charts: {},
      filters: {
        transactions: {
          accountId: '',
          fromDate: '',
          toDate: '',
        },
        sales: {
          branchId: '',
          period: 'monthly',
          year: new Date().getFullYear(),
        },
        expenses: {
          category: '',
          status: '',
        },
        invoicesBills: {
          type: '',
          status: '',
        },
        bankAccounts: {
          bank: '',
        },
        cashFlow: {
          period: 'monthly',
          year: new Date().getFullYear(),
        },
      },
      years: this.generateYears(),
    }
  },
  watch: {
    'filters.transactions': {
      handler: 'updateTransactionsChart',
      deep: true,
    },
    'filters.sales': {
      handler: 'updateSalesChart',
      deep: true,
    },
    'filters.expenses': {
      handler: 'updateExpensesChart',
      deep: true,
    },
    'filters.invoicesBills': {
      handler: 'updateInvoicesBillsChart',
      deep: true,
    },
    'filters.bankAccounts': {
      handler: 'updateBankAccountsChart',
      deep: true,
    },
    'filters.cashFlow': {
      handler: 'updateCashFlowChart',
      deep: true,
    },
  },
  mounted() {
    this.initializeCharts()
  },
  beforeUnmount() {
    // Destroy all charts to prevent memory leaks
    Object.values(this.charts).forEach(chart => {
      if (chart) chart.destroy()
    })
  },
  methods: {
    formatCurrency(value) {
      if (value == null) return 'GMD 0.00'
      return 'GMD ' + Number(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    },
    generateYears() {
      const currentYear = new Date().getFullYear()
      const years = []
      for (let i = currentYear - 5; i <= currentYear + 1; i++) {
        years.push(i)
      }
      return years
    },
    getChartOptions(title = '') {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          title: {
            display: !!title,
            text: title,
          },
          legend: {
            position: 'bottom',
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => this.formatCurrency(value),
            },
          },
        },
      }
    },
    initializeCharts() {
      this.$nextTick(() => {
        if (this.canViewTransactions && this.$refs.transactionsChart) {
          this.updateTransactionsChart()
        }
        if (this.canViewSales && this.$refs.salesChart) {
          this.updateSalesChart()
        }
        if (this.canViewExpenses && this.$refs.expensesChart) {
          this.updateExpensesChart()
        }
        if ((this.canViewInvoices || this.canViewBills) && this.$refs.invoicesBillsChart) {
          this.updateInvoicesBillsChart()
        }
        if (this.canViewBankAccounts && this.$refs.bankAccountsChart) {
          this.updateBankAccountsChart()
        }
        if (this.isAdmin && this.$refs.cashFlowChart) {
          this.updateCashFlowChart()
        }
      })
    },
    updateTransactionsChart() {
      if (!this.$refs.transactionsChart) return
      
      if (this.charts.transactions) {
        this.charts.transactions.destroy()
      }

      const data = this.chartData.transactions || { labels: [], credits: [], debits: [] }
      
      this.charts.transactions = new Chart(this.$refs.transactionsChart, {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [
            {
              label: 'Credits',
              data: data.credits,
              backgroundColor: 'rgba(16, 185, 129, 0.6)',
              borderColor: 'rgb(16, 185, 129)',
              borderWidth: 1,
            },
            {
              label: 'Debits',
              data: data.debits,
              backgroundColor: 'rgba(239, 68, 68, 0.6)',
              borderColor: 'rgb(239, 68, 68)',
              borderWidth: 1,
            },
          ],
        },
        options: this.getChartOptions('Transaction Flow'),
      })
    },
    updateSalesChart() {
      if (!this.$refs.salesChart) return
      
      if (this.charts.sales) {
        this.charts.sales.destroy()
      }

      const data = this.chartData.sales || { labels: [], values: [] }
      
      this.charts.sales = new Chart(this.$refs.salesChart, {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: [
            {
              label: 'Sales',
              data: data.values,
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              borderColor: 'rgb(16, 185, 129)',
              borderWidth: 2,
              fill: true,
              tension: 0.4,
            },
          ],
        },
        options: this.getChartOptions('Sales Performance'),
      })
    },
    updateExpensesChart() {
      if (!this.$refs.expensesChart) return
      
      if (this.charts.expenses) {
        this.charts.expenses.destroy()
      }

      const data = this.chartData.expenses || { labels: [], values: [] }
      
      this.charts.expenses = new Chart(this.$refs.expensesChart, {
        type: 'doughnut',
        data: {
          labels: data.labels,
          datasets: [
            {
              data: data.values,
              backgroundColor: [
                'rgba(59, 130, 246, 0.6)',
                'rgba(16, 185, 129, 0.6)',
                'rgba(245, 158, 11, 0.6)',
                'rgba(239, 68, 68, 0.6)',
                'rgba(139, 92, 246, 0.6)',
                'rgba(236, 72, 153, 0.6)',
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
            },
          },
        },
      })
    },
    updateInvoicesBillsChart() {
      if (!this.$refs.invoicesBillsChart) return
      
      if (this.charts.invoicesBills) {
        this.charts.invoicesBills.destroy()
      }

      const data = this.chartData.invoicesBills || { labels: [], invoices: [], bills: [] }
      
      this.charts.invoicesBills = new Chart(this.$refs.invoicesBillsChart, {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [
            {
              label: 'Invoices',
              data: data.invoices,
              backgroundColor: 'rgba(139, 92, 246, 0.6)',
              borderColor: 'rgb(139, 92, 246)',
              borderWidth: 1,
            },
            {
              label: 'Bills',
              data: data.bills,
              backgroundColor: 'rgba(245, 158, 11, 0.6)',
              borderColor: 'rgb(245, 158, 11)',
              borderWidth: 1,
            },
          ],
        },
        options: this.getChartOptions('Invoices vs Bills'),
      })
    },
    updateBankAccountsChart() {
      if (!this.$refs.bankAccountsChart) return
      
      if (this.charts.bankAccounts) {
        this.charts.bankAccounts.destroy()
      }

      const data = this.chartData.bankAccounts || { labels: [], values: [] }
      
      this.charts.bankAccounts = new Chart(this.$refs.bankAccountsChart, {
        type: 'pie',
        data: {
          labels: data.labels,
          datasets: [
            {
              data: data.values,
              backgroundColor: [
                'rgba(59, 130, 246, 0.6)',
                'rgba(16, 185, 129, 0.6)',
                'rgba(245, 158, 11, 0.6)',
                'rgba(239, 68, 68, 0.6)',
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
            },
          },
        },
      })
    },
    updateCashFlowChart() {
      if (!this.$refs.cashFlowChart) return
      
      if (this.charts.cashFlow) {
        this.charts.cashFlow.destroy()
      }

      const data = this.chartData.cashFlow || { labels: [], inflow: [], outflow: [], net: [] }
      
      this.charts.cashFlow = new Chart(this.$refs.cashFlowChart, {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: [
            {
              label: 'Cash Inflow',
              data: data.inflow,
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              borderColor: 'rgb(16, 185, 129)',
              borderWidth: 2,
              fill: false,
            },
            {
              label: 'Cash Outflow',
              data: data.outflow,
              backgroundColor: 'rgba(239, 68, 68, 0.1)',
              borderColor: 'rgb(239, 68, 68)',
              borderWidth: 2,
              fill: false,
            },
            {
              label: 'Net Cash Flow',
              data: data.net,
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              borderColor: 'rgb(59, 130, 246)',
              borderWidth: 2,
              fill: true,
            },
          ],
        },
        options: this.getChartOptions('Cash Flow Analysis'),
      })
    },
    generateReport(type, format) {
      const params = new URLSearchParams({
        type,
        format,
        filters: JSON.stringify(this.filters[type.replace('-', '')] || {}),
      })
      
      window.location.href = `/reports/generate?${params.toString()}`
    },
  },
}
</script>

<style scoped>
.btn-primary {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors duration-150;
}

.btn-secondary {
  @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors duration-150;
}
</style>