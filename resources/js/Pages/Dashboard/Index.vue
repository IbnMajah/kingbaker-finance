<template>
  <div>
    <Head title="Dashboard" />
    <h1 class="mb-8 text-3xl font-bold">Financial Dashboard</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
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

    <!-- Invoices & Bills Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
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

    <!-- Transaction History Chart -->
    <div class="bg-white rounded-lg shadow mb-8">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold">Transaction History</h2>
      </div>
      <div class="p-6">
        <canvas ref="transactionChart" height="300"></canvas>
      </div>
    </div>

    <!-- Recent Transactions & Expense Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
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
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Chart from 'chart.js/auto'
import Card from '@/Shared/Card.vue'
import { formatterMixin } from '@/Utils/formatters'

export default {
  components: {
    Head,
    Card,
  },
  layout: Layout,
  mixins: [formatterMixin],
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
  mounted() {
    this.initTransactionChart()
    this.initExpenseChart()
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
