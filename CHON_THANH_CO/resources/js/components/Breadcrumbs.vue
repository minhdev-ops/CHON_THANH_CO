<script setup lang="ts">
export interface BreadcrumbItem {
  label: string
  to?: string
}

defineProps<{
  items: BreadcrumbItem[]
}>()
</script>

<template>
  <nav aria-label="Breadcrumb" class="flex text-on-surface-variant text-sm mb-8 reveal">
    <ol class="inline-flex items-center space-x-1 md:space-x-2">
      <li v-for="(item, i) in items" :key="i">
        <div class="flex items-center">
          <span v-if="i > 0" class="material-symbols-outlined text-sm mx-1">chevron_right</span>
          <router-link
            v-if="item.to"
            :to="item.to"
            class="hover:text-brand-orange transition-colors duration-200"
          >{{ item.label }}</router-link>
          <span v-else class="text-on-surface font-semibold">{{ item.label }}</span>
        </div>
      </li>
    </ol>
  </nav>
</template>
