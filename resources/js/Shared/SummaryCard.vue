<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-start">
      <div>
        <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
        <div class="mt-1 text-3xl font-semibold">{{ amount }}</div>
      </div>
      <div :class="[
        'flex items-center text-sm',
        trend === 'up' ? 'text-green-600' : 'text-red-600'
      ]">
        <svg
          v-if="trend === 'up'"
          class="w-5 h-5"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z"
            clip-rule="evenodd"
          />
        </svg>
        <svg
          v-else
          class="w-5 h-5"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z"
            clip-rule="evenodd"
          />
        </svg>
        <span class="ml-1">{{ percentage }}%</span>
      </div>
    </div>
    <div v-if="subtitle" class="mt-1 text-sm text-gray-500">
      {{ subtitle }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      required: true,
    },
    amount: {
      type: [String, Number],
      required: true,
    },
    trend: {
      type: String,
      default: 'up',
      validator: value => ['up', 'down'].includes(value),
    },
    percentage: {
      type: Number,
      default: 0,
    },
    subtitle: {
      type: String,
      default: null,
    },
  },
}
</script>