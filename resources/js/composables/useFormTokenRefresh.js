import { ref, onMounted } from 'vue'
import { useSessionKeepAlive } from './useSessionKeepAlive.js'

export function useFormTokenRefresh() {
    const { refreshSession, updateCsrfToken } = useSessionKeepAlive()
    const isRefreshing = ref(false)
    const lastRefresh = ref(null)

    // Configuration
    const REFRESH_INTERVAL = 30 * 60 * 1000 // 30 minutes
    const MIN_REFRESH_INTERVAL = 5 * 60 * 1000 // Minimum 5 minutes between refreshes

    // Auto-refresh token before form submission
    const ensureValidToken = async () => {
        // Check if we need to refresh
        if (shouldRefreshToken()) {
            await refreshToken()
        }
    }

    // Check if token should be refreshed
    const shouldRefreshToken = () => {
        if (!lastRefresh.value) return true

        const timeSinceLastRefresh = Date.now() - lastRefresh.value
        return timeSinceLastRefresh >= REFRESH_INTERVAL
    }

    // Refresh the CSRF token
    const refreshToken = async () => {
        if (isRefreshing.value) return false

        // Prevent too frequent refreshes
        if (lastRefresh.value && (Date.now() - lastRefresh.value) < MIN_REFRESH_INTERVAL) {
            return false
        }

        isRefreshing.value = true

        try {
            const success = await refreshSession()
            if (success) {
                lastRefresh.value = Date.now()
                console.log('Form token refreshed successfully')
                return true
            }
            return false
        } catch (error) {
            console.error('Failed to refresh form token:', error)
            return false
        } finally {
            isRefreshing.value = false
        }
    }

    // Setup automatic token refresh
    const setupAutoRefresh = () => {
        // Refresh token every 30 minutes
        const interval = setInterval(() => {
            if (shouldRefreshToken()) {
                refreshToken()
            }
        }, REFRESH_INTERVAL)

        // Cleanup function
        return () => clearInterval(interval)
    }

    // Manual token refresh (can be called from forms)
    const manualRefresh = async () => {
        return await refreshToken()
    }

    // Lifecycle
    onMounted(() => {
        // Initial token refresh if needed
        if (shouldRefreshToken()) {
            refreshToken()
        }

        // Setup auto-refresh
        const cleanup = setupAutoRefresh()

        // Return cleanup function
        return cleanup
    })

    return {
        isRefreshing,
        lastRefresh,
        ensureValidToken,
        refreshToken,
        manualRefresh,
        setupAutoRefresh
    }
}
