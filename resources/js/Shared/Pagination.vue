<template>
  <div v-if="shouldShowPagination">
    <div class="flex space-x-2">
      <!-- Previous Button -->
      <div v-if="links[0].url === null" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md text-gray-400 cursor-not-allowed" v-html="links[0].label" />
      <Link v-else class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50" :href="links[0].url" preserve-state v-html="links[0].label" />

      <!-- Page Numbers -->
      <template v-for="(link, key) in pageNumberLinks" :key="`page-${key}`">
        <div v-if="link.url === null" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md text-gray-400 cursor-not-allowed" v-html="link.label" />
        <Link v-else class="px-3 py-2 text-sm border rounded-md transition-colors" :class="link.active ? 'bg-indigo-600 border-indigo-600 text-white font-semibold' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'" :href="link.url" preserve-state v-html="link.label" />
      </template>

      <!-- Next Button -->
      <div v-if="links[links.length - 1].url === null" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md text-gray-400 cursor-not-allowed" v-html="links[links.length - 1].label" />
      <Link v-else class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50" :href="links[links.length - 1].url" preserve-state v-html="links[links.length - 1].label" />
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  components: {
    Link,
  },
  props: {
    links: Array,
  },
  computed: {
    pageNumberLinks() {
      // Return all links except the first (Previous) and last (Next)
      if (this.links.length <= 2) return []
      return this.links.slice(1, -1)
    },
    shouldShowPagination() {
      // Show pagination if:
      // 1. We have at least 3 links (Previous, at least 1 page, Next)
      // 2. AND there are multiple pages (more than just Previous, 1 page, Next)
      // Laravel pagination structure:
      // - 1 page: [Previous (disabled), 1, Next (disabled)] = 3 links
      // - 2+ pages: [Previous, 1, 2, ..., Next] = 4+ links
      // We want to show pagination when there are 2+ pages (4+ links)
      // OR when there are 3 links but at least one navigable link exists
      if (this.links.length < 3) return false
      
      // If we have 3 links, check if there's at least one navigable link (not disabled)
      // This means there are multiple pages
      if (this.links.length === 3) {
        // Check if Previous or Next is enabled (meaning there are other pages)
        return this.links[0].url !== null || this.links[2].url !== null
      }
      
      // 4+ links means we definitely have multiple pages
      return true
    },
  },
}
</script>
