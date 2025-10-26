<template>
  <div class="bg-white border-b h-16 border-gray-200 shadow-sm sticky top-0 z-30">
    <div class="px-6 py-4">
      <div class="flex items-center justify-between">
        <!-- Children Menu Items -->
        <div class="flex items-center space-x-6">
          <!-- Overview Children -->
          <div v-if="selectedMainMenu === 'overview'" class="flex ml-16 items-center space-x-4">
            <Link
              href="/"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                isUrl('') ? 'bg-brand-100 text-brand-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Dashboard
            </Link>
          </div>

          <!-- Accounts & Transactions Children -->
          <div v-if="selectedMainMenu === 'accounts'" class="flex ml-16 items-center space-x-4">
            <Link
              v-if="hasPermission('view_bank_accounts')"
              href="/bank-accounts"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('bank-accounts') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Accounts
            </Link>
            <Link
              v-if="hasPermission('view_transactions')"
              href="/transactions"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('transactions') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Transactions
            </Link>
            <Link
              v-if="hasPermission('view_deposits')"
              href="/deposits"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('deposits') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Deposits
            </Link>
          </div>

          <!-- Sales Children -->
          <div v-if="selectedMainMenu === 'sales'" class="flex ml-16 items-center space-x-4">
            <Link
              v-if="hasPermission('view_sales')"
              href="/sales"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('sales') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Sales
            </Link>
            <Link
              v-if="hasPermission('view_invoices')"
              href="/invoices"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('invoices') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Invoices
            </Link>
            <Link
              v-if="hasPermission('view_contacts')"
              href="/contacts"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('contacts') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Customers
            </Link>
          </div>

          <!-- Vendors Children -->
          <div v-if="selectedMainMenu === 'vendors'" class="flex ml-16 items-center space-x-4">
            <Link
              v-if="hasPermission('view_bills')"
              href="/bills"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('bills') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Bills
            </Link>
            <Link
              v-if="hasPermission('view_vendors')"
              href="/vendors"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('vendors') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Vendors
            </Link>
            <Link
              v-if="hasPermission('view_cheque_payments')"
              href="/cheque-payments"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('cheque-payments') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Cheque Payments
            </Link>
          </div>

          <!-- Expenses Children -->
          <div v-if="selectedMainMenu === 'expenses'" class="flex ml-16 items-center space-x-4">
            <Link
              v-if="hasPermission('view_expenses')"
              href="/expense-claims"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                isUrl('expense-claims') ? 'bg-brand-100 text-brand-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Expense Claims
            </Link>
            <Link
              v-if="hasPermission('view_finance')"
              href="/miscellaneous"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-all duration-200',
                isUrl('miscellaneous') ? 'bg-brand-100 text-brand-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Miscellaneous
            </Link>
          </div>

          <!-- Reports Children -->
          <div v-if="selectedMainMenu === 'reports'" class="flex ml-16 items-center space-x-4">
            <Link
              v-if="hasPermission('view_reports')"
              href="/reports"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('reports') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Reports
            </Link>

          </div>

          <!-- Organization Children -->
          <div v-if="selectedMainMenu === 'organization'" class="flex ml-16 items-center space-x-4">
            <Link
              v-if="hasPermission('access_settings')"
              href="/branches"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('branches') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Branches
            </Link>
            <Link
              v-if="hasPermission('view_users')"
              href="/users"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isUrl('users') ? 'bg-brand-100 text-brand-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              ]"
            >
              Users
            </Link>
          </div>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-4">
          <div class="text-sm text-gray-600">{{ auth.user.account.name }}</div>
          <dropdown placement="bottom-end">
            <template #default>
              <div class="group flex items-center cursor-pointer select-none">
                <div class="mr-1 text-gray-700 group-hover:text-brand-600 focus:text-brand-600 whitespace-nowrap">
                  <span>{{ auth.user.first_name }}</span>
                  <span class="hidden md:inline">&nbsp;{{ auth.user.last_name }}</span>
                </div>
                <icon class="w-5 h-5 fill-gray-700 group-hover:fill-brand-600 focus:fill-brand-600" name="cheveron-down" />
              </div>
            </template>
            <template #dropdown>
              <div class="mt-2 py-2 text-sm bg-white rounded shadow-xl">
                <Link class="block px-6 py-2 hover:text-white hover:bg-brand-500" :href="`/users/${auth.user.id}/edit`">My Profile</Link>
                <Link v-if="isAdmin" class="block px-6 py-2 hover:text-white hover:bg-brand-500" href="/users">Manage Users</Link>
                <Link class="block px-6 py-2 w-full text-left hover:text-white hover:bg-brand-500" href="/logout" method="delete" as="button">Logout</Link>
              </div>
            </template>
          </dropdown>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'
import Dropdown from '@/Shared/Dropdown.vue'
import { usePermissions } from '@/composables/usePermissions.js'

export default {
  components: {
    Dropdown,
    Icon,
    Link,
  },
  props: {
    auth: Object,
    selectedMainMenu: String,
  },
  setup() {
    const { isAdmin, hasPermission } = usePermissions()
    return { isAdmin, hasPermission }
  },
  methods: {
    isUrl(...urls) {
      let currentUrl = this.$page.url.substr(1)
      if (urls[0] === '') {
        return currentUrl === ''
      }
      return urls.filter((url) => currentUrl.startsWith(url)).length
    },
  },
}
</script>
