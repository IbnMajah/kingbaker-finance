import '../css/app.css'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { formatDate, formatAmount } from './utils/formatters.js'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  title: title => title ? `${title} - King Baker` : 'King Baker',
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })

    // Register global methods
    app.config.globalProperties.$formatDate = formatDate
    app.config.globalProperties.$formatAmount = formatAmount

    app.use(plugin).mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
