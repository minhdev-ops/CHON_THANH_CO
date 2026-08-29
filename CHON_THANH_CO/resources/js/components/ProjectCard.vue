<script setup lang="ts">
import { computed }   from 'vue'
import type { Project } from '../types'

const props = defineProps<{ project: Project }>()

const heroImage = computed(() =>
  props.project.gallery?.[0]?.image
  || props.project.hero_image
  || '/images/projects/highway-1.jpg'
)
</script>

<template>
  <router-link
    :to="`/projects/${project.slug}`"
    class="bg-white rounded-[10px] shadow-sm hover:shadow-xl transition-shadow group overflow-hidden flex flex-col h-full border border-transparent hover:border-[#E4D8D0]"
  >
    <!-- Image with internal text overlay -->
    <div class="relative overflow-hidden aspect-[4/3] rounded-[10px]">
      <img
        :src="heroImage"
        :alt="project.name"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
        loading="lazy"
      >
      <!-- Dark gradient for readability -->
      <div class="absolute inset-0 bg-gradient-to-t from-[#16243D]/90 via-[#16243D]/30 to-transparent opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
      
      <!-- Category badge -->
      <div class="absolute bottom-6 left-6 right-6 z-20">
        <h3 class="font-bold text-white text-[18px] leading-tight mb-3 group-hover:text-[#B89B88] transition-colors duration-200 line-clamp-2">
          {{ project.name }}
        </h3>
        <p v-if="project.location || project.period" class="flex items-center gap-4 text-[13px] text-white/90">
          <span v-if="project.location" class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-[#B89B88]">location_on</span>
            {{ project.location }}
          </span>
          <span v-if="project.period" class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-[#B89B88]">calendar_month</span>
            {{ project.period }}
          </span>
        </p>
      </div>
      
      <!-- Hover View button icon -->
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-[#B89B88] rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transform scale-50 group-hover:scale-100 transition-all duration-300 shadow-xl">
        <span class="material-symbols-outlined text-[24px]">visibility</span>
      </div>
    </div>
  </router-link>
</template>
