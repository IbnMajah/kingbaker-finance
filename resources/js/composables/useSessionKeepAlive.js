import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

export function useSessionKeepAlive() {
    const isActive = ref(false)
    const keepAliveInterval = ref(null)
    const sessionTimeout = ref(null)
    const lastActivity = ref(Date.now())

    // Configuration
    const KEEP_ALIVE_INTERVAL = 5 * 60 * 1000 // 5 minutes
    const SESSION_WARNING_TIME = 10 * 60 * 1000 // 10 minutes before expiry
    const SESSION_TIMEOUT = 11 * 60 * 1000 // 11 minutes (just before 12 hour expiry)

    // Start session keep-alive
    const startKeepAlive = () => {
        if (isActive.value) return

        isActive.value = true

        // Set up periodic keep-alive requests
        keepAliveInterval.value = setInterval(() => {
            performKeepAlive()
        }, KEEP_ALIVE_INTERVAL)

        // Set up session timeout warning
        sessionTimeout.value = setTimeout(() => {
            showSessionWarning()
        }, SESSION_WARNING_TIME)

        // Track user activity
        setupActivityTracking()

        console.log('Session keep-alive started')
    }

    // Stop session keep-alive
    const stopKeepAlive = () => {
        if (!isActive.value) return

        isActive.value = false

        if (keepAliveInterval.value) {
            clearInterval(keepAliveInterval.value)
            keepAliveInterval.value = null
        }

        if (sessionTimeout.value) {
            clearTimeout(sessionTimeout.value)
            sessionTimeout.value = null
        }

        console.log('Session keep-alive stopped')
    }

    // Perform keep-alive request
    const performKeepAlive = async () => {
        try {
            // Make a lightweight request to keep session alive
            const response = await fetch('/api/session/keep-alive', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })

            if (response.ok) {
                lastActivity.value = Date.now()
                console.log('Session keep-alive successful')
            } else {
                console.warn('Session keep-alive failed, redirecting to login')
                redirectToLogin()
            }
        } catch (error) {
            console.error('Session keep-alive error:', error)
            // Don't redirect on network errors, just log
        }
    }

    // Show session timeout warning
    const showSessionWarning = () => {
        const warningMessage = 'Your session will expire soon. Would you like to extend it?'

        if (confirm(warningMessage)) {
            // User wants to extend session
            performKeepAlive()

            // Reset the warning timer
            sessionTimeout.value = setTimeout(() => {
                showSessionWarning()
            }, SESSION_WARNING_TIME)
        } else {
            // User declined, redirect to login
            redirectToLogin()
        }
    }

    // Redirect to login
    const redirectToLogin = () => {
        stopKeepAlive()
        router.visit('/login')
    }

    // Setup activity tracking
    const setupActivityTracking = () => {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click']

        const updateActivity = () => {
            lastActivity.value = Date.now()
        }

        events.forEach(event => {
            document.addEventListener(event, updateActivity, true)
        })

        // Cleanup function for activity tracking
        return () => {
            events.forEach(event => {
                document.removeEventListener(event, updateActivity, true)
            })
        }
    }

    // Manual session refresh (for forms)
    const refreshSession = async () => {
        try {
            const response = await fetch('/api/session/refresh', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })

            if (response.ok) {
                const data = await response.json()
                // Update CSRF token if provided
                if (data.csrf_token) {
                    updateCsrfToken(data.csrf_token)
                }
                lastActivity.value = Date.now()
                return true
            }
            return false
        } catch (error) {
            console.error('Session refresh error:', error)
            return false
        }
    }

    // Update CSRF token in meta tag and forms
    const updateCsrfToken = (newToken) => {
        // Update meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]')
        if (metaTag) {
            metaTag.setAttribute('content', newToken)
        }

        // Update all hidden CSRF input fields
        const csrfInputs = document.querySelectorAll('input[name="_token"]')
        csrfInputs.forEach(input => {
            input.value = newToken
        })

        console.log('CSRF token updated')
    }

    // Lifecycle hooks
    onMounted(() => {
        startKeepAlive()
    })

    onUnmounted(() => {
        stopKeepAlive()
    })

    return {
        isActive,
        lastActivity,
        startKeepAlive,
        stopKeepAlive,
        refreshSession,
        updateCsrfToken
    }
}
