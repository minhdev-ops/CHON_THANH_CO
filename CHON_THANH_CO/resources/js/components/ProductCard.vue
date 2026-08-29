<script setup lang="ts">
import { computed } from 'vue'
import type { Product } from '../types'

const props = withDefaults(
  defineProps<{
    product: Product
    variant?: 'featured' | 'catalog'
    showCategory?: boolean
    showStrength?: boolean
  }>(),
  { variant: 'featured', showCategory: true, showStrength: true },
)

const productImage = computed(() =>
  props.product.image || '/images/products/geotextile-roll.jpg',
)

const isCatalog = computed(() => props.variant === 'catalog')
</script>

<template>
  <router-link
    :to="`/products/${product.slug}`"
    class="group relative block bg-surface-1 border border-outline-variant hover:border-text-main transition-all duration-500 overflow-hidden card-shine"
  >
    <div
      class="relative overflow-hidden bg-canvas"
      :class="isCatalog ? 'aspect-[4/3]' : 'aspect-[4/3]'"
    >
      <img
        :src="productImage"
        :alt="product.name"
        class="w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700 ease-out"
        loading="lazy"
      >
      <div class="absolute inset-0 bg-gradient-to-t from-text-main/15 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

      <div
        v-if="showStrength && product.strength_label"
        class="absolute bottom-0 left-0 bg-primary/90 backdrop-blur-sm text-canvas px-3 py-1.5 text-[10px] font-bold tracking-[0.18em] uppercase tabular-nums"
        style="background: rgba(22, 36, 61, 0.9);"
      >
        {{ product.strength_label }}
      </div>

      <div v-if="showCategory" class="absolute top-3 left-3">
        <span
          class="inline-flex items-center gap-1 bg-canvas/90 backdrop-blur-md border border-text-main/20 px-2.5 py-1 text-[9px] font-bold text-text-main uppercase tracking-[0.18em]"
        >
          {{ product.category?.name || 'Geosynthetics' }}
        </span>
      </div>

      <div
        class="absolute top-3 right-3 w-9 h-9 bg-primary text-white flex items-center justify-center opacity-0 group-hover:opacity-100 group-hover:rotate-[-45deg] transition-all duration-500"
      >
        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
      </div>
    </div>

    <div class="p-6 border-t border-outline-variant flex flex-col">
      <h3
        class="font-extrabold text-[19px] text-text-main mb-3 group-hover:text-primary transition-colors duration-300 leading-snug line-clamp-2 min-h-[2.6em]"
      >
        {{ product.name }}
      </h3>

      <div class="mt-auto pt-4 flex items-center justify-between border-t border-outline-variant/60">
        <span class="text-[12px] font-bold text-text-muted tracking-[0.18em] uppercase tabular-nums">
          {{ product.code }}
        </span>
        <span
          class="inline-flex items-center gap-1.5 text-[12px] font-bold text-text-main group-hover:text-primary tracking-[0.15em] uppercase transition-colors duration-300"
        >
          Chi tiết
          <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform duration-300">arrow_forward</span>
        </span>
      </div>
    </div>
  </router-link>
</template>
