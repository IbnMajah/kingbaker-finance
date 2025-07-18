<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
      {
        'bg-green-100 text-green-800': variant === 'success',
        'bg-red-100 text-red-800': variant === 'danger',
        'bg-yellow-100 text-yellow-800': variant === 'warning',
        'bg-blue-100 text-blue-800': variant === 'info',
        'bg-gray-100 text-gray-800': variant === 'default',
      },
      className
    ]"
  >
    <slot />
  </span>
</template>

<script>
export default {
  props: {
    variant: {
      type: String,
      default: 'default',
      validator: value => ['success', 'danger', 'warning', 'info', 'default'].includes(value),
    },
    className: {
      type: String,
      default: '',
    },
  },
}
</script>