<script setup lang="ts">
import { computed } from 'vue'
import type { Product } from '../types'

import LazyImage from './LazyImage.vue'

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

</script>

<template>
  <router-link
    :to="`/products/${product.slug}`"
    class="group relative block overflow-hidden rounded-[20px] bg-white border border-[#E8E1DC] hover:border-[#B89B88] transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(74,64,59,0.13)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-canvas"
  >
    <!-- Desktop: vertical card -->
    <div class="hidden sm:block">
      <div class="relative overflow-hidden bg-[#F4F1EE]">
        <LazyImage
          :src="productImage"
          :alt="product.name"
          fallback-src="/images/products/geotextile-roll.jpg"
          aspect-ratio="aspect-[5/4]"
          image-class="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.05]"
        />
        <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-[#6E5B50] shadow-sm">
          {{ product.category?.name || 'Geosynthetics' }}
        </span>
        <span class="absolute bottom-4 right-4 flex h-9 w-9 items-center justify-center rounded-full bg-[#16243D] text-white opacity-0 translate-y-2 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
          <i class="fa text-[17px] fa-arrow-right"></i>
        </span>
      </div>

      <div class="p-5 md:p-6">
        <h3
          class="font-extrabold text-[21px] text-[#2A2420] group-hover:text-primary transition-colors duration-300 leading-[1.18] line-clamp-2 min-h-[2.36em]"
        >
          {{ product.name }}
        </h3>
        <div class="mt-5 border-t border-[#EEE8E3] pt-4">
          <span class="btn-primary inline-flex w-full items-center justify-center gap-2 rounded-full px-4 py-3 text-[12px] font-bold uppercase tracking-[0.12em] transition-all duration-300 group-hover:shadow-md">
            Yêu cầu báo giá
            <i class="fa text-[17px] transition-transform duration-300 group-hover:translate-x-1 fa-arrow-right"></i>
          </span>
        </div>
      </div>
    </div>

    <!-- Mobile: compact vertical card (2-col grid) -->
    <div class="sm:hidden">
      <div class="relative overflow-hidden bg-[#F4F1EE]">
        <LazyImage
          :src="productImage"
          :alt="product.name"
          fallback-src="/images/products/geotextile-roll.jpg"
          aspect-ratio="aspect-[4/3]"
          image-class="w-full h-full object-cover object-center"
        />
        <span class="absolute left-2 top-2 rounded-full bg-white/95 px-2 py-0.5 text-[11px] font-bold uppercase tracking-[0.04em] text-[#6E5B50] shadow-sm max-w-[85%] truncate">
          {{ product.category?.name || 'Geosynthetics' }}
        </span>
      </div>
      <div class="px-3 pt-2.5 pb-3">
        <h3 class="font-bold text-[14px] text-[#2A2420] leading-snug line-clamp-2 min-h-[2.6em]">
          {{ product.name }}
        </h3>
        <div class="mt-2.5 pt-2.5 border-t border-[#EEE8E3] flex items-center justify-between gap-2">
          <span class="text-[11px] font-semibold text-[#8C7B70] tracking-[0.06em] uppercase tabular-nums truncate">
            {{ product.code }}
          </span>
          <span class="shrink-0 flex items-center gap-1 text-[11px] font-bold text-primary-deep">
            Xem
            <i class="fa text-[11px] fa-arrow-right group-active:translate-x-0.5 transition-transform duration-300"></i>
          </span>
        </div>
      </div>
    </div>
  </router-link>
</template>
