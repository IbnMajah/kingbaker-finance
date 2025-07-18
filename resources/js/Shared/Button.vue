<template>
  <button
    :type="type"
    :class="[
      'inline-flex items-center justify-center px-4 py-2 border rounded-md font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150',
      {
        'opacity-75 cursor-not-allowed': disabled || loading,
        'bg-brand-600 border-brand-600 text-white hover:bg-brand-500 focus:bg-brand-700 focus:ring-brand-500': variant === 'primary' && !disabled,
        'bg-white border-gray-300 text-gray-700 hover:text-gray-500 focus:ring-brand-500': variant === 'outline' && !disabled,
        'bg-red-600 border-red-600 text-white hover:bg-red-500 focus:bg-red-700 focus:ring-red-500': variant === 'danger' && !disabled,
        'bg-gray-200 border-gray-200 text-gray-700 cursor-not-allowed': disabled,
      },
      className
    ]"
    :disabled="disabled || loading"
    @click="$emit('click', $event)"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-3 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>
    <slot />
  </button>
</template>

<script>
export default {
  props: {
    type: {
      type: String,
      default: 'button',
    },
    variant: {
      type: String,
      default: 'primary',
      validator: value => ['primary', 'outline', 'danger'].includes(value),
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    className: {
      type: String,
      default: '',
    },
  },
  emits: ['click'],
}
</script>