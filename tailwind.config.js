import defaultTheme from 'tailwindcss/defaultTheme'

export default {
  content: ['./resources/**/*.{js,vue,blade.php}'],
  theme: {
    extend: {
      colors: {
        indigo: {
          100: '#e6e8ff',
          300: '#b2b7ff',
          400: '#7886d7',
          500: '#6574cd',
          600: '#5661b3',
          800: '#2f365f',
          900: '#191e38',
        },
        brand: {
          50: '#FBF8F5',
          100: '#F2E2D0',
          200: '#E8CFA6',
          300: '#E3C7A3',
          400: '#D3AC77',
          500: '#9B672A',
          600: '#7F4F1E',
          700: '#6B3F18',
          800: '#573510',
          900: '#2B1A08'
        },
        secondary: '#9b672a',
        layer: '#D3BEB9',
      },
      fontFamily: {
        sans: ['"Cerebri Sans"', ...defaultTheme.fontFamily.sans],
      },
    },
  },
}
