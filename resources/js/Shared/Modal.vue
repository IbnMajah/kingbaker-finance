<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
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
        document.addEventListener('click', this.handleClickOutside)
      } else {
        document.body.style.overflow = null
        document.removeEventListener('keydown', this.handleEscape)
        document.removeEventListener('click', this.handleClickOutside)
      }
    },
  },
  methods: {
    handleEscape(e) {
      if (e.key === 'Escape' && this.show) {
        this.$emit('close')
      }
    },
    handleClickOutside(e) {
      if (this.show && !this.$el.contains(e.target)) {
        this.$emit('close')
      }
    },
  },
  beforeUnmount() {
    document.body.style.overflow = null
    document.removeEventListener('keydown', this.handleEscape)
    document.removeEventListener('click', this.handleClickOutside)
  },
}
</script>