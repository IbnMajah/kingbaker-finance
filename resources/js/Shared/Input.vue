<template>
  <div>
    <input
      :id="id"
      ref="input"
      v-bind="$attrs"
      :class="[
        'border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm',
        { 'border-red-500 focus:border-red-500 focus:ring-red-500': error },
        className
      ]"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    >
    <div v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</div>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default: null,
    },
    modelValue: {
      type: [String, Number, null],
      default: null,
    },
    error: {
      type: String,
      default: null,
    },
    className: {
      type: String,
      default: '',
    },
  },
  emits: ['update:modelValue'],
}
</script>