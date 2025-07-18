<template>
  <label :for="htmlFor" class="block text-sm font-medium text-gray-700">
    <slot />
  </label>
</template>

<script>
export default {
  props: {
    htmlFor: {
      type: String,
      default: null,
    },
  },
}
</script>