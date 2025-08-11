import '../css/app.css'
import { createApp, h } from 'vue'
import { createInertiaApp, router } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import { formatDate, formatAmount } from './Utils/formatters.js'
import { usePermissionsStore } from './stores/permissions.js'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  title: title => title ? `${title} - King Baker` : 'King Baker',
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
    const pinia = createPinia()

    // Register global methods
    app.config.globalProperties.$formatDate = formatDate
    app.config.globalProperties.$formatAmount = formatAmount

    // Add Pinia to the app FIRST
    app.use(plugin)
      .use(pinia)

    // Initialize permissions store AFTER Pinia is added to the app
    const permissionsStore = usePermissionsStore()

    if (props.initialPage.props.auth?.user) {
      permissionsStore.setUser({
        user: props.initialPage.props.auth.user,
        roles: props.initialPage.props.auth.userRoles || [],
        permissions: props.initialPage.props.auth.userPermissions || []
      })
    }

    // Make permissions available globally always (even if no user)
    app.config.globalProperties.$permissions = permissionsStore

    // Listen for page navigation to update permissions when auth data changes
    router.on('success', (event) => {
      const authData = event.detail.page.props.auth
      if (authData?.user && (!permissionsStore.user || permissionsStore.user.id !== authData.user.id)) {
        // User logged in or switched, update permissions
        permissionsStore.setUser({
          user: authData.user,
          roles: authData.userRoles || [],
          permissions: authData.userPermissions || []
        })
      } else if (!authData?.user && permissionsStore.user) {
        // User logged out, clear permissions
        permissionsStore.clearUser()
      }
    })

    app.mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
