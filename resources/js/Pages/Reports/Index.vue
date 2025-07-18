<template>
  <div>
    <Head title="Reports" />
    <h1 class="mb-8 text-3xl font-bold">Financial Reports</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Account Sheet Report Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="text-xl font-semibold mb-2">Account Sheet</h2>
            <p class="text-gray-600">View transactions by account with running balance</p>
          </div>
          <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <div class="mb-4">
          <select v-model="selectedAccount" class="form-select w-full mb-4">
            <option value="">All Accounts</option>
            <option v-for="account in accounts" :key="account.value" :value="account.value">
              {{ account.label }}
            </option>
          </select>
          <div class="h-64">
            <canvas ref="accountChart"></canvas>
          </div>
        </div>
        <div class="flex gap-2">
          <button @click="generateReport('account-sheet', 'pdf')" class="btn-kingbaker">
            Export PDF
          </button>
          <button @click="generateReport('account-sheet', 'xlsx')" class="btn-kingbaker">
            Export Excel
          </button>
        </div>
      </div>

      <!-- Expense Claims Report Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="text-xl font-semibold mb-2">Expense Claims</h2>
            <p class="text-gray-600">Track expense claims by category</p>
          </div>
          <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <div class="mb-4">
          <select v-model="selectedCategory" class="form-select w-full mb-4">
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.value" :value="category.value">
              {{ category.label }}
            </option>
          </select>
          <div class="h-64">
            <canvas ref="expenseChart"></canvas>
          </div>
        </div>
        <div class="flex gap-2">
          <button @click="generateReport('expense-claims', 'pdf')" class="btn-kingbaker">
            Export PDF
          </button>
          <button @click="generateReport('expense-claims', 'xlsx')" class="btn-kingbaker">
            Export Excel
          </button>
        </div>
      </div>

      <!-- Cash Flow Summary Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="text-xl font-semibold mb-2">Cash Flow Summary</h2>
            <p class="text-gray-600">View credits vs debits over time</p>
          </div>
          <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div class="mb-4">
          <select v-model="summaryType" class="form-select w-full mb-4">
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
          <div class="h-64">
            <canvas ref="summaryChart"></canvas>
          </div>
        </div>
        <div class="flex gap-2">
          <button @click="generateReport('cash-flow', 'pdf')" class="btn-kingbaker">
            Export PDF
          </button>
          <button @click="generateReport('cash-flow', 'xlsx')" class="btn-kingbaker">
            Export Excel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import Chart from 'chart.js/auto'

export default {
  components: {
    Head,
  },
  layout: Layout,
  props: {
    accounts: {
      type: Array,
      default: () => [],
    },
    categories: {
      type: Array,
      default: () => [],
    },
    transactions: {
      type: Array,
      default: () => [],
    },
    summary: {
      type: Object,
      default: () => ({
        total_credits: 0,
        total_debits: 0,
        net_movement: 0
      }),
    },
  },
  data() {
    return {
      selectedAccount: '',
      selectedCategory: '',
      summaryType: 'monthly',
      charts: {
        account: null,
        expense: null,
        summary: null,
      }
    }
  },
  watch: {
    selectedAccount() {
      this.updateAccountChart()
    },
    selectedCategory() {
      this.updateExpenseChart()
    },
    summaryType() {
      this.updateSummaryChart()
    }
  },
  mounted() {
    this.initCharts()
  },
  methods: {
    formatCurrency(value) {
      return 'GMD ' + Number(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    },
    getChartOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => this.formatCurrency(value)
            }
          }
        }
      }
    },
    initCharts() {
      this.updateAccountChart()
      this.updateExpenseChart()
      this.updateSummaryChart()
    },
    updateAccountChart() {
      // Initialize empty arrays if no transactions
      if (!this.transactions || !Array.isArray(this.transactions)) {
        if (this.charts.account) {
          this.charts.account.destroy();
        }
        this.charts.account = new Chart(this.$refs.accountChart, {
          type: 'bar',
          data: {
            labels: [],
            datasets: [
              {
                label: 'Credits',
                data: [],
                backgroundColor: 'rgba(16, 185, 129, 0.5)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1
              },
              {
                label: 'Debits',
                data: [],
                backgroundColor: 'rgba(239, 68, 68, 0.5)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1
              }
            ]
          },
          options: this.getChartOptions()
        });
        return;
      }

      const filteredTransactions = this.transactions.filter(t =>
        !this.selectedAccount || t.bank_account_id === this.selectedAccount
      );

      const data = {
        labels: filteredTransactions.map(t => new Date(t.transaction_date).toLocaleDateString()),
        credits: filteredTransactions.filter(t => t.type === 'credit').map(t => t.amount),
        debits: filteredTransactions.filter(t => t.type === 'debit').map(t => t.amount),
      };

      if (this.charts.account) {
        this.charts.account.destroy();
      }

      this.charts.account = new Chart(this.$refs.accountChart, {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [
            {
              label: 'Credits',
              data: data.credits,
              backgroundColor: 'rgba(16, 185, 129, 0.5)',
              borderColor: 'rgb(16, 185, 129)',
              borderWidth: 1
            },
            {
              label: 'Debits',
              data: data.debits,
              backgroundColor: 'rgba(239, 68, 68, 0.5)',
              borderColor: 'rgb(239, 68, 68)',
              borderWidth: 1
            }
          ]
        },
        options: this.getChartOptions()
      });
    },
    updateExpenseChart() {
      // Initialize empty chart if no transactions
      if (!this.transactions || !Array.isArray(this.transactions)) {
        if (this.charts.expense) {
          this.charts.expense.destroy();
        }
        this.charts.expense = new Chart(this.$refs.expenseChart, {
          type: 'doughnut',
          data: {
            labels: [],
            datasets: [{
              data: [],
              backgroundColor: [],
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
              },
            },
          }
        });
        return;
      }

      const filteredTransactions = this.transactions.filter(t =>
        t.type === 'debit' && (!this.selectedCategory || t.category === this.selectedCategory)
      );

      const categoryTotals = filteredTransactions.reduce((acc, t) => {
        acc[t.category] = (acc[t.category] || 0) + t.amount;
        return acc;
      }, {});

      if (this.charts.expense) {
        this.charts.expense.destroy();
      }

      this.charts.expense = new Chart(this.$refs.expenseChart, {
        type: 'doughnut',
        data: {
          labels: Object.keys(categoryTotals),
          datasets: [{
            data: Object.values(categoryTotals),
            backgroundColor: [
              'rgba(16, 185, 129, 0.5)',
              'rgba(239, 68, 68, 0.5)',
              'rgba(245, 158, 11, 0.5)',
              'rgba(59, 130, 246, 0.5)',
              'rgba(139, 92, 246, 0.5)',
            ],
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
            },
          },
        }
      });
    },
    updateSummaryChart() {
      // Initialize empty chart if no transactions
      if (!this.transactions || !Array.isArray(this.transactions)) {
        if (this.charts.summary) {
          this.charts.summary.destroy();
        }
        this.charts.summary = new Chart(this.$refs.summaryChart, {
          type: 'line',
          data: {
            labels: [],
            datasets: [{
              label: 'Net Cash Flow',
              data: [],
              borderColor: 'rgb(16, 185, 129)',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              fill: true,
              tension: 0.4
            }]
          },
          options: this.getChartOptions()
        });
        return;
      }

      const groupedData = this.groupTransactionsByPeriod(this.transactions, this.summaryType);

      if (this.charts.summary) {
        this.charts.summary.destroy();
      }

      this.charts.summary = new Chart(this.$refs.summaryChart, {
        type: 'line',
        data: {
          labels: groupedData.labels,
          datasets: [
            {
              label: 'Net Cash Flow',
              data: groupedData.netFlow,
              borderColor: 'rgb(16, 185, 129)',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              fill: true,
              tension: 0.4
            }
          ]
        },
        options: this.getChartOptions()
      });
    },
    groupTransactionsByPeriod(transactions, period) {
      // Group transactions by selected period (weekly, monthly, yearly)
      const grouped = transactions.reduce((acc, t) => {
        const date = new Date(t.date)
        let key

        switch (period) {
          case 'weekly':
            key = `Week ${Math.ceil((date.getDate()) / 7)} - ${date.toLocaleString('default', { month: 'short' })}`
            break
          case 'monthly':
            key = date.toLocaleString('default', { month: 'long', year: 'numeric' })
            break
          case 'yearly':
            key = date.getFullYear().toString()
            break
        }

        if (!acc[key]) {
          acc[key] = { credits: 0, debits: 0 }
        }
        if (t.type === 'credit') {
          acc[key].credits += t.amount
        } else {
          acc[key].debits += t.amount
        }
        return acc
      }, {})

      return {
        labels: Object.keys(grouped),
        netFlow: Object.values(grouped).map(v => v.credits - v.debits)
      }
    },
    generateReport(type, format) {
      const params = new URLSearchParams({
        type,
        format,
        account_id: this.selectedAccount,
        category: this.selectedCategory,
        summary_type: this.summaryType,
      })
      window.location.href = `/reports/generate?${params.toString()}`
    }
  }
}
</script>

<style>
.btn-kingbaker {
  @apply px-4 py-2 bg-brand-600 text-white rounded-md hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-colors duration-150;
}
</style>
