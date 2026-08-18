<template>
  <div>
    <div id="dropdown" />

    <odoo-menu :selected-main-menu="selectedMainMenu" />

    <top-nav-bar :auth="auth" :selected-main-menu="selectedMainMenu" />

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
import { resolveMainMenu } from '@/Utils/navigation.js'
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
  computed: {
    selectedMainMenu() {
      return resolveMainMenu(this.$page.url)
    },
  },
}
</script>
