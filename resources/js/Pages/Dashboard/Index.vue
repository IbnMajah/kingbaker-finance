<template>
  <div>
    <Head title="Dashboard" />
    <h1 class="mb-8 text-3xl font-bold">Dashboard</h1>

    <!-- Admin Financial Summary Cards -->
    <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <Card
        title="Total Balance"
        :icon="BanknotesIcon"
        :numberValue="formatCurrency(summary.totalBalance)"
        :subtitle="`Across ${summary.totalAccounts} accounts`"
      />
      <Card
        title="Today's Activity"
        :icon="ArrowTrendingUpIcon"
        :numberValue="formatCurrency(summary.todayDeposits - summary.todayExpenses)"
        :subtitle="getActivityBreakdown(summary.todayDeposits, summary.todayExpenses)"
      />
      <Card
        title="This Month"
        :icon="CalendarIcon"
        :numberValue="formatCurrency(summary.thisMonthDeposits - summary.thisMonthExpenses)"
        :subtitle="getActivityBreakdown(summary.thisMonthDeposits, summary.thisMonthExpenses)"
      />
    </div>

    <!-- Admin Reports Section -->
    <div v-if="isAdmin" class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold">Invoices & Bills Status</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-sm font-medium text-gray-500">Invoices</h3>
              <div class="mt-2 space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-yellow-600">Pending</span>
                  <span class="font-medium">{{ invoicesAndBills.pendingInvoices }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-red-600">Overdue</span>
                  <span class="font-medium">{{ invoicesAndBills.overdueInvoices }}</span>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-sm font-medium text-gray-500">Bills</h3>
              <div class="mt-2 space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-yellow-600">Pending</span>
                  <span class="font-medium">{{ invoicesAndBills.pendingBills }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-red-600">Overdue</span>
                  <span class="font-medium">{{ invoicesAndBills.overdueBills }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Expense Claims -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold">Pending Expense Claims</h2>
        </div>
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <div class="text-2xl font-bold">{{ expenseClaims.pendingCount }}</div>
              <div class="text-sm text-gray-500">Claims to Review</div>
            </div>
            <div>
              <div class="text-2xl font-bold">{{ formatCurrency(expenseClaims.pendingAmount) }}</div>
              <div class="text-sm text-gray-500">Total Amount</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Admin Transaction History Chart -->
    <div v-if="isAdmin" class="bg-white rounded-lg shadow mb-8">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Transaction History</h2>
      </div>
      <div class="p-6">
        <canvas ref="transactionChart" height="300"></canvas>
      </div>
    </div>

    <!-- Admin Recent Transactions & Expense Categories -->
    <div v-if="isAdmin" class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold">Recent Transactions</h2>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div v-for="transaction in recentTransactions" :key="transaction.id" class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-900">{{ transaction.description }}</div>
                <div class="text-sm text-gray-500">
                  {{ $formatDate(transaction.date) }} • {{ transaction.account }} • {{ transaction.payment_mode }}
                </div>
              </div>
              <div :class="transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                {{ transaction.type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Expense Categories -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold">Expense Categories (This Month)</h2>
        </div>
        <div class="p-6">
          <canvas ref="expenseChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Non-Admin User Dashboard -->
    <div v-if="!isAdmin" class="bg-white rounded-lg shadow p-8 text-center">
      <div class="max-w-md mx-auto">
        <svg class="mx-auto h-12 w-12 text-brand-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome, {{ $page.props.auth.user?.first_name }}!</h3>
        <p class="text-gray-600 mb-4">
          You're logged in as <strong>{{ formatRoleName(userRole) }}</strong>
        </p>
        <p class="text-gray-500 text-sm">
          Use the navigation menu to access the modules available to your role.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Chart from 'chart.js/auto'
import Card from '@/Shared/Card.vue'
import { formatterMixin } from '@/Utils/formatters'
import { usePermissions } from '@/composables/usePermissions.js'

export default {
  components: {
    Head,
    Card,
  },
  layout: Layout,
  mixins: [formatterMixin],
  setup() {
    const permissions = usePermissions()
    return { permissions }
  },
  props: {
    summary: {
      type: Object,
      required: true,
    },
    invoicesAndBills: {
      type: Object,
      required: true,
    },
    expenseClaims: {
      type: Object,
      required: true,
    },
    transactionHistory: {
      type: Array,
      required: true,
    },
    expensesByCategory: {
      type: Array,
      required: true,
    },
    recentTransactions: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      transactionChart: null,
      expenseChart: null,
    }
  },
  computed: {
    isAdmin() {
      const hasAdminRole = this.permissions.hasRole('admin')
      const isOwner = this.$page.props.auth.user?.owner
      return hasAdminRole || isOwner
    },
    userRole() {
      return this.$page.props.auth.user?.role || ''
    }
  },
  mounted() {
    if (this.isAdmin) {
      this.initTransactionChart()
      this.initExpenseChart()
    }
  },
  methods: {
    formatCurrency(value) {
      if (value == null) return 'GMD 0.00'
      return 'GMD ' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    $formatDate(date) {
      return new Date(date).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
      })
    },
    getActivityBreakdown(credits, debits) {
      return `+${this.formatCurrency(credits)} / -${this.formatCurrency(debits)}`
    },
    formatRoleName(roleName) {
      if (!roleName) return 'User'
      return roleName.charAt(0).toUpperCase() + roleName.slice(1).replace('_', ' ')
    },
    initTransactionChart() {
      const ctx = this.$refs.transactionChart.getContext('2d')
      this.transactionChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.transactionHistory.map(item => item.month),
          datasets: [
            {
              label: 'Credits',
              data: this.transactionHistory.map(item => item.credits),
              borderColor: '#10B981',
              tension: 0.4,
            },
            {
              label: 'Debits',
              data: this.transactionHistory.map(item => item.debits),
              borderColor: '#EF4444',
              tension: 0.4,
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
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: value => this.formatCurrency(value),
              },
            },
          },
        },
      })
    },
    initExpenseChart() {
      const ctx = this.$refs.expenseChart.getContext('2d')
      this.expenseChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: this.expensesByCategory.map(item => item.category),
          datasets: [{
            data: this.expensesByCategory.map(item => item.total),
            backgroundColor: [
              '#3B82F6',
              '#10B981',
              '#F59E0B',
              '#EF4444',
              '#8B5CF6',
              '#EC4899',
            ],
          }],
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
  },
}
</script>
