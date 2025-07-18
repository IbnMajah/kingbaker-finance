<template>
  <div>
    <select
      :id="id"
      :value="modelValue"
      :class="[
        'block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm',
        { 'border-red-500 focus:border-red-500 focus:ring-red-500': error },
        className
      ]"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
    <div v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</div>
  </div>
</template>

<script>
export default {
  props: {
    id: {
      type: String,
      default: null,
    },
    modelValue: {
      type: [String, Number, null],
      default: null,
    },
    options: {
      type: Array,
      default: () => [],
    },
    placeholder: {
      type: String,
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