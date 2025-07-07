<template>
  <nav aria-label="Pagination navigation" class="flex justify-center mt-6">
    <ul class="inline-flex items-center -space-x-px">
      <li
        v-for="link in links"
        :key="link.label"
        class="px-3 py-1 border rounded-md shadow-sm transition ease-in-out duration-150"
        :class="[
          link.active
            ? 'bg-blue-600 text-white cursor-default'
            : link.url
              ? 'bg-white text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-400'
              : 'bg-gray-100 text-gray-400 cursor-not-allowed'
        ]"
      >
        <a
          v-if="link.url"
          :href="link.url"
          class="block text-sm font-medium text-center"
          :aria-current="link.active ? 'page' : null"
        >
          <span v-html="link.label"></span>
        </a>
        <span
          v-else
          class="block text-sm font-medium text-center"
          aria-disabled="true"
          v-html="link.label"
        />
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: 'Pagination',
  props: {
    links: {
      type: Array,
      required: true,
      // each link: { label: string, url?: string, active?: boolean }
    },
  },
}
</script>
