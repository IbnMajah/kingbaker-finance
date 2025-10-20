<template>
  <div>
    <div id="dropdown" />
    
    <!-- Odoo Menu Component -->
    <odoo-menu @menu-selected="handleMenuSelection" />
    
    <!-- Top Navigation Bar -->
    <top-nav-bar :auth="auth" :selected-main-menu="selectedMainMenu" />
    
    <!-- Main Content -->
    <div class="min-h-screen bg-gray-50">
      <div class="px-4 py-8 md:px-8 md:py-12" scroll-region>
        <flash-messages />
        <slot />
      </div>
    </div>
  </div>
</template>

<script>
import FlashMessages from '@/Shared/FlashMessages.vue'
import OdooMenu from '@/Shared/OdooMenu.vue'
import TopNavBar from '@/Shared/TopNavBar.vue'
import { usePermissions } from '@/composables/usePermissions.js'

export default {
  components: {
    FlashMessages,
    OdooMenu,
    TopNavBar,
  },
  props: {
    auth: Object,
  },
  setup() {
    const { isAdmin, permissions } = usePermissions()
    return { isAdmin, permissions }
  },
  data() {
    return {
      selectedMainMenu: null,
    }
  },
  methods: {
    handleMenuSelection(menuKey) {
      this.selectedMainMenu = menuKey
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
