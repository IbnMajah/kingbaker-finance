<template>
  <div>
    <!-- Overview -->
    <div @click="toggle('overview')" class="flex items-center justify-between cursor-pointer mb-2 font-semibold text-xs text-[#9B672A] uppercase tracking-wider select-none">
      Overview
      <svg :class="open.overview ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>
    <transition name="fade">
      <div v-show="open.overview">
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/">
            <svg class="mr-2 w-4 h-4" :class="isUrl('') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
            <div :class="isUrl('') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Dashboard</div>
          </Link>
        </div>
      </div>
    </transition>

    <!-- Accounts & Transactions -->
    <div @click="toggle('accounts')" class="flex items-center justify-between cursor-pointer mb-2 font-semibold text-xs text-[#9B672A] uppercase tracking-wider select-none">
      Accounts & Transactions
      <svg :class="open.accounts ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>
    <transition name="fade">
      <div v-show="open.accounts">
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/bank-accounts">
            <svg class="mr-2 w-4 h-4" :class="isUrl('bank-accounts') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M11.5 1L2 6v2h20V6m-5 4H2v10h19m-3-7v2h2v-2m-18 2h2v-2H2m3 2h2v-2H5m3 2h2v-2H8m3 2h2v-2h-2m3 2h2v-2h-2m3 2h2v-2h-2Z"/>
            </svg>
            <div :class="isUrl('bank-accounts') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Accounts</div>
          </Link>
        </div>
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/transactions">
            <svg class="mr-2 w-4 h-4" :class="isUrl('transactions') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/>
            </svg>
            <div :class="isUrl('transactions') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Transactions</div>
          </Link>
        </div>
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/deposits">
            <svg class="mr-2 w-4 h-4" :class="isUrl('deposits') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
            </svg>
            <div :class="isUrl('deposits') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Deposits</div>
          </Link>
        </div>
      </div>
    </transition>

    <!-- Sales & Invoicing -->
    <div @click="toggle('sales')" class="flex items-center justify-between cursor-pointer mb-2 font-semibold text-xs text-[#9B672A] uppercase tracking-wider select-none">
      Sales & Invoicing
      <svg :class="open.sales ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>
    <transition name="fade">
      <div v-show="open.sales">
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/sales">
            <svg class="mr-2 w-4 h-4" :class="isUrl('sales') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M18 17H6v-2h12v2zm0-4H6v-2h12v2zm0-4H6V7h12v2zM3 22l1.5-1.5L6 22l1.5-1.5L9 22l1.5-1.5L12 22l1.5-1.5L15 22l1.5-1.5L18 22l1.5-1.5L21 22V2l-1.5 1.5L18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2 4.5 3.5 3 2v20z"/>
            </svg>
            <div :class="isUrl('sales') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Sales</div>
          </Link>
        </div>
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/invoices">
            <svg class="mr-2 w-4 h-4" :class="isUrl('invoices') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
            </svg>
            <div :class="isUrl('invoices') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Invoices</div>
          </Link>
        </div>
      </div>
    </transition>

    <!-- Vendors & Purchases -->
    <div @click="toggle('vendors')" class="flex items-center justify-between cursor-pointer mb-2 font-semibold text-xs text-[#9B672A] uppercase tracking-wider select-none">
      Vendors & Purchases
      <svg :class="open.vendors ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>
    <transition name="fade">
      <div v-show="open.vendors">
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/vendors">
            <svg class="mr-2 w-4 h-4" :class="isUrl('vendors') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
            </svg>
            <div :class="isUrl('vendors') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Vendors</div>
          </Link>
        </div>
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/bills">
            <svg class="mr-2 w-4 h-4" :class="isUrl('bills') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11zM8 9h2v2H8V9zm0 3h2v2H8v-2zm0 3h8v2H8v-2z"/>
            </svg>
            <div :class="isUrl('bills') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Bills</div>
          </Link>
        </div>
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/cheque-payments">
            <svg class="mr-2 w-4 h-4" :class="isUrl('cheque-payments') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
            </svg>
            <div :class="isUrl('cheque-payments') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Cheque Payments</div>
          </Link>
        </div>
      </div>
    </transition>

    <!-- Expenses & Claims -->
    <div @click="toggle('expenses')" class="flex items-center justify-between cursor-pointer mb-2 font-semibold text-xs text-[#9B672A] uppercase tracking-wider select-none">
      Expenses & Claims
      <svg :class="open.expenses ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>
    <transition name="fade">
      <div v-show="open.expenses">
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/expense-claims">
            <svg class="mr-2 w-4 h-4" :class="isUrl('expense-claims') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
            </svg>
            <div :class="isUrl('expense-claims') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Expense Claims</div>
          </Link>
        </div>
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/miscellaneous">
            <svg class="mr-2 w-4 h-4" :class="isUrl('miscellaneous') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
            </svg>
            <div :class="isUrl('miscellaneous') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Miscellaneous</div>
          </Link>
        </div>
      </div>
    </transition>

    <!-- Organization -->
    <div @click="toggle('organization')" class="flex items-center justify-between cursor-pointer mb-2 font-semibold text-xs text-[#9B672A] uppercase tracking-wider select-none">
      Organization
      <svg :class="open.organization ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>
    <transition name="fade">
      <div v-show="open.organization">
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/branches">
            <svg class="mr-2 w-4 h-4" :class="isUrl('branches') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/>
            </svg>
            <div :class="isUrl('branches') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Branches</div>
          </Link>
        </div>
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/users">
            <svg class="mr-2 w-4 h-4" :class="isUrl('users') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <div :class="isUrl('users') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Users</div>
          </Link>
        </div>
      </div>
    </transition>

    <!-- Reports & Analytics -->
    <div @click="toggle('reports')" class="flex items-center justify-between cursor-pointer mb-2 font-semibold text-xs text-[#9B672A] uppercase tracking-wider select-none">
      Reports & Analytics
      <svg :class="open.reports ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>
    <transition name="fade">
      <div v-show="open.reports">
        <div class="mb-4">
          <Link class="group flex items-center py-3" href="/reports">
            <svg class="mr-2 w-4 h-4" :class="isUrl('reports') ? 'fill-white' : 'fill-brand-400 group-hover:fill-white'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/>
            </svg>
            <div :class="isUrl('reports') ? 'text-white' : 'text-brand-300 group-hover:text-white'">Reports</div>
          </Link>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'

export default {
  components: {
    Icon,
    Link,
  },
  data() {
    return {
      open: {
        overview: true,
        accounts: false,
        sales: false,
        vendors: false,
        expenses: false,
        organization: false,
        reports: false,
      },
    }
  },
  methods: {
    isUrl(...urls) {
      let currentUrl = this.$page.url.substr(1)
      if (urls[0] === '') {
        return currentUrl === ''
      }
      return urls.filter((url) => currentUrl.startsWith(url)).length
    },
    toggle(group) {
      this.open[group] = !this.open[group]
    },
  },
}
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: all 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  max-height: 0;
}
.fade-enter-to, .fade-leave-from {
  opacity: 1;
  max-height: 1000px;
}
</style>
