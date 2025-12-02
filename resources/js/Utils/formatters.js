/**
 * Format date to short format: 'DD/MM/YYYY' (e.g., '31/11/2025')
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date string
 */
export function formatDate(date) {
    if (!date) return ''

    const dateObj = new Date(date)
    if (isNaN(dateObj.getTime())) return ''

    const day = dateObj.getDate().toString().padStart(2, '0')
    const month = (dateObj.getMonth() + 1).toString().padStart(2, '0')
    const year = dateObj.getFullYear()

    return `${day}/${month}/${year}`
}

/**
 * Format amount in Gambian Dalasis with two decimal places
 * @param {number|string} amount - Amount to format
 * @returns {string} Formatted amount string
 */
export function formatAmount(amount) {
    if (amount === null || amount === undefined || amount === '') return 'GMD 0.00'

    const numAmount = parseFloat(amount)
    if (isNaN(numAmount)) return 'GMD 0.00'

    return `GMD ${numAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`
}

/**
 * Format date and time to human readable format: 'Wed, 31st May 2025, 2:30 PM'
 * @param {string|Date} dateTime - DateTime to format
 * @returns {string} Formatted datetime string
 */
export function formatDateTime(dateTime) {
    if (!dateTime) return ''

    const dateObj = new Date(dateTime)
    if (isNaN(dateObj.getTime())) return ''

    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    const months = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ]

    const dayName = days[dateObj.getDay()]
    const day = dateObj.getDate()
    const month = months[dateObj.getMonth()]
    const year = dateObj.getFullYear()

    // Add ordinal suffix to day
    const getOrdinalSuffix = (num) => {
        if (num > 3 && num < 21) return 'th'
        switch (num % 10) {
            case 1: return 'st'
            case 2: return 'nd'
            case 3: return 'rd'
            default: return 'th'
        }
    }

    // Format time
    let hours = dateObj.getHours()
    const minutes = dateObj.getMinutes()
    const ampm = hours >= 12 ? 'PM' : 'AM'
    hours = hours % 12
    hours = hours ? hours : 12 // 0 should be 12
    const formattedTime = `${hours}:${minutes.toString().padStart(2, '0')} ${ampm}`

    return `${dayName}, ${day}${getOrdinalSuffix(day)} ${month} ${year}, ${formattedTime}`
}

/**
 * Global mixin to be used in Vue components
 */
export const formatterMixin = {
    methods: {
        $formatDate: formatDate,
        $formatAmount: formatAmount,
        $formatDateTime: formatDateTime
    }
}
