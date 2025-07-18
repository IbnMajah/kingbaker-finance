/**
 * Format date to human readable format: 'Wednesday, 31st May, 2025'
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date string
 */
export function formatDate(date) {
    if (!date) return ''

    const dateObj = new Date(date)
    if (isNaN(dateObj.getTime())) return ''

    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    const months = [
        'Jan.', 'Feb.', 'March', 'April', 'May', 'June',
        'July', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.'
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

    return `${dayName}, ${day}${getOrdinalSuffix(day)} ${month}, ${year}`
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
 * Global mixin to be used in Vue components
 */
export const formatterMixin = {
    methods: {
        $formatDate: formatDate,
        $formatAmount: formatAmount
    }
}