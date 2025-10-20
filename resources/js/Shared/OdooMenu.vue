<template>
  <div>
    <!-- Menu Toggle Button -->
    <button 
      @click="toggleMenu" 
      class="fixed top-2 left-4 z-50 p-3 bg-brand-600 text-white rounded-lg shadow-lg hover:bg-brand-700 hover:shadow-xl transition-all duration-200 transform hover:scale-105"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <!-- Overlay Background -->
    <div 
      v-if="isMenuOpen" 
      @click="closeMenu"
      class="fixed inset-0 z-40 bg-black bg-opacity-50"
    ></div>

    <!-- Menu Overlay -->
    <div 
      v-if="isMenuOpen"
      class="fixed top-0 left-0 z-50 w-80 h-full bg-white shadow-2xl transform transition-transform duration-300 ease-in-out"
      :class="isMenuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h2 class="text-xl font-bold text-gray-900">Menu</h2>
          </div>
          <button @click="closeMenu" class="text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Menu Items -->
        <div class="space-y-2">
          <!-- Overview -->
          <div >
            <Link 
              href="/"
              @click="selectMainMenu('overview')"
              :class="[
                'block w-full text-left px-4 py-3 rounded-lg transition-colors',
                selectedMainMenu === 'overview' ? 'bg-brand-100 text-brand-700' : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              Dashboard
            </Link>
          </div>

          <!-- Accounts & Transactions -->
          <div v-if="canAccessFinance" class="mb-6">
            <Link 
              href="/bank-accounts"
              @click="selectMainMenu('accounts')"
              :class="[
                'block w-full text-left px-4 py-3 rounded-lg transition-colors',
                selectedMainMenu === 'accounts' ? 'bg-brand-100 text-brand-700' : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              Finance
            </Link>
          </div>

          <!-- Sales -->
          <div v-if="canViewSales" class="mb-6">
            <Link 
              href="/sales"
              @click="selectMainMenu('sales')"
              :class="[
                'block w-full text-left px-4 py-3 rounded-lg transition-colors',
                selectedMainMenu === 'sales' ? 'bg-brand-100 text-brand-700' : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              Sales
            </Link>
          </div>

          <!-- Vendors -->
          <div v-if="canViewBills" class="mb-6">
            <Link 
              href="/bills"
              @click="selectMainMenu('vendors')"
              :class="[
                'block w-full text-left px-4 py-3 rounded-lg transition-colors',
                selectedMainMenu === 'vendors' ? 'bg-brand-100 text-brand-700' : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              Bills & Vendors
            </Link>
          </div>

          <!-- Expenses -->
          <div v-if="canViewExpenses" class="mb-6">
            <Link 
              href="/expense-claims"
              @click="selectMainMenu('expenses')"
              :class="[
                'block w-full text-left px-4 py-3 rounded-lg transition-colors',
                selectedMainMenu === 'expenses' ? 'bg-brand-100 text-brand-700' : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              Expenses
            </Link>
          </div>

          <!-- Reports -->
          <div v-if="canViewSales" class="mb-6">
            <Link 
              href="/reports"
              @click="selectMainMenu('reports')"
              :class="[
                'block w-full text-left px-4 py-3 rounded-lg transition-colors',
                selectedMainMenu === 'reports' ? 'bg-brand-100 text-brand-700' : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              Reports
            </Link>
          </div>

          <!-- Organization -->
          <div v-if="canViewBranches || canViewUsers" class="mb-6">
            <Link 
              href="/branches"
              @click="selectMainMenu('organization')"
              :class="[
                'block w-full text-left px-4 py-3 rounded-lg transition-colors',
                selectedMainMenu === 'organization' ? 'bg-brand-100 text-brand-700' : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              Settings
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import { usePermissions } from '@/composables/usePermissions.js'

export default {
  components: {
    Link,
  },
  setup() {
    const { 
      canAccessFinance, 
      canViewSales, 
      canViewBills, 
      canViewExpenses, 
      canViewUsers, 
      canViewBranches 
    } = usePermissions()
    return { 
      canAccessFinance, 
      canViewSales, 
      canViewBills, 
      canViewExpenses, 
      canViewUsers, 
      canViewBranches 
    }
  },
  data() {
    return {
      isMenuOpen: false,
      selectedMainMenu: null,
    }
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen
    },
    closeMenu() {
      this.isMenuOpen = false
    },
    selectMainMenu(menuKey) {
      this.selectedMainMenu = menuKey
      this.$emit('menu-selected', menuKey)
      this.closeMenu()
    }
  },
  mounted() {
    // Set initial menu based on current URL
    const currentUrl = this.$page.url.substr(1)
    if (currentUrl === '') {
      this.selectedMainMenu = 'overview'
    } else if (currentUrl.startsWith('bank-accounts') || currentUrl.startsWith('transactions') || currentUrl.startsWith('deposits')) {
      this.selectedMainMenu = 'accounts'
    } else if (currentUrl.startsWith('sales')) {
      this.selectedMainMenu = 'sales'
    } else if (currentUrl.startsWith('bills') || currentUrl.startsWith('vendors') || currentUrl.startsWith('cheque-payments')) {
      this.selectedMainMenu = 'vendors'
    } else if (currentUrl.startsWith('expense-claims') || currentUrl.startsWith('miscellaneous')) {
      this.selectedMainMenu = 'expenses'
    } else if (currentUrl.startsWith('reports')) {
      this.selectedMainMenu = 'reports'
    } else if (currentUrl.startsWith('branches') || currentUrl.startsWith('users')) {
      this.selectedMainMenu = 'organization'
    }
  }
}
</script>
