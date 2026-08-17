<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50"
    @click.self="$emit('close')"
  >
    <div class="relative bg-white rounded-lg shadow-lg max-w-md w-full mx-4" @click.stop>
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    show: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['close'],
  watch: {
    show(value) {
      if (value) {
        document.body.style.overflow = 'hidden'
        document.addEventListener('keydown', this.handleEscape)
      } else {
        document.body.style.overflow = null
        document.removeEventListener('keydown', this.handleEscape)
      }
    },
  },
  methods: {
    handleEscape(e) {
      if (e.key === 'Escape' && this.show) {
        this.$emit('close')
      }
    },
  },
  beforeUnmount() {
    document.body.style.overflow = null
    document.removeEventListener('keydown', this.handleEscape)
  },
}
</script>