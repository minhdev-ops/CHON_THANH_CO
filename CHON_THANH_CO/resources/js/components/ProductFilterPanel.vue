<script setup lang="ts">
import { computed } from 'vue'
import type { Category, Application } from '../types'
import { t } from '../i18n'

const props = withDefaults(
  defineProps<{
    categories: Category[]
    applications: Application[]
    strengthBuckets: { label: string; min: number; max: number | null; code: string }[]
    categoriesError?: string | null
    applicationsError?: string | null
  }>(),
  { categoriesError: null, applicationsError: null },
)

const searchQuery = defineModel<string>('searchQuery', { default: '' })
const selectedCategories = defineModel<string[]>('selectedCategories', { default: () => [] })
const selectedApplications = defineModel<string[]>('selectedApplications', { default: () => [] })
const selectedStrength = defineModel<string | null>('selectedStrength', { default: null })

const emit = defineEmits<{
  (e: 'retry-categories'): void
  (e: 'retry-applications'): void
}>()

const toggle = (list: string[], value: string) => {
  const i = list.indexOf(value)
  if (i >= 0) list.splice(i, 1)
  else list.push(value)
}

const hasCategoryError = computed(() => !!props.categoriesError && !props.categories.length)
const hasApplicationError = computed(() => !!props.applicationsError && !props.applications.length)
</script>

<template>
  <div class="space-y-7">
    <!-- Search -->
    <div>
      <div class="flex items-center gap-2 mb-3">
        <span class="font-mono text-[13px] font-bold text-primary tracking-[0.2em]">01</span>
        <span class="text-[13px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.searchLabel') }}</span>
      </div>
      <div class="relative">
        <i class="fa fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[16px]"></i>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('products.searchPlaceholder')"
          class="w-full bg-canvas border border-outline-variant pl-9 pr-3 py-2.5 text-[16px] md:text-[15px] text-text-main outline-none focus:border-text-main transition-colors duration-200 placeholder:text-text-muted/60 rounded-none"
        />
      </div>
    </div>

    <!-- Category -->
    <div v-if="!hasCategoryError">
      <div class="flex items-center gap-2 mb-3">
        <span class="font-mono text-[13px] font-bold text-primary tracking-[0.2em]">02</span>
        <span class="text-[13px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.categoryFilter') }}</span>
      </div>
      <ul class="space-y-0.5">
        <li v-for="cat in categories" :key="cat.slug">
          <label class="group flex items-center gap-3 cursor-pointer py-1.5 max-md:py-2 max-md:min-h-[44px] px-1 -mx-1 hover:bg-canvas transition-colors duration-200">
            <span class="relative flex items-center justify-center shrink-0 w-4 h-4 max-md:w-5 max-md:h-5">
              <input
                type="checkbox"
                class="peer appearance-none w-4 h-4 max-md:w-5 max-md:h-5 border border-outline bg-canvas checked:bg-primary checked:border-primary transition-colors duration-200 cursor-pointer rounded-[2px]"
                :checked="selectedCategories.includes(cat.slug)"
                @change="toggle(selectedCategories, cat.slug)"
              >
              <i class="fa fa-check absolute text-canvas text-[12px] font-bold opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
            </span>
            <span class="text-[15px] text-text-secondary group-hover:text-text-main transition-colors duration-200 font-medium flex-grow leading-tight">{{ cat.name }}</span>
            <span v-if="cat.products_count" class="font-mono text-[12px] text-text-muted font-bold tabular-nums">{{ String(cat.products_count).padStart(2, '0') }}</span>
          </label>
        </li>
      </ul>
      <div v-if="categoriesError" class="mt-2 text-[11px] text-text-muted">
        {{ categoriesError }} <button class="underline hover:text-text-main" @click="emit('retry-categories')">{{ t('common.retry') }}</button>
      </div>
    </div>

    <!-- Application -->
    <div v-if="!hasApplicationError">
      <div class="flex items-center gap-2 mb-3">
        <span class="font-mono text-[13px] font-bold text-primary tracking-[0.2em]">03</span>
        <span class="text-[13px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.applicationFilter') }}</span>
      </div>
      <ul class="space-y-0.5 max-h-48 max-md:max-h-none overflow-y-auto max-md:overflow-visible custom-scrollbar pr-2">
        <li v-for="appItem in applications" :key="appItem.slug">
          <label class="group flex items-center gap-3 cursor-pointer py-1.5 max-md:py-2 max-md:min-h-[44px] px-1 -mx-1 hover:bg-canvas transition-colors duration-200">
            <span class="relative flex items-center justify-center shrink-0 w-4 h-4 max-md:w-5 max-md:h-5">
              <input
                type="checkbox"
                class="peer appearance-none w-4 h-4 max-md:w-5 max-md:h-5 border border-outline bg-canvas checked:bg-primary checked:border-primary transition-colors duration-200 cursor-pointer rounded-[2px]"
                :checked="selectedApplications.includes(appItem.slug)"
                @change="toggle(selectedApplications, appItem.slug)"
              >
              <i class="fa fa-check absolute text-canvas text-[12px] font-bold opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
            </span>
            <span class="text-[15px] text-text-secondary group-hover:text-text-main transition-colors duration-200 font-medium leading-tight">{{ appItem.name }}</span>
          </label>
        </li>
      </ul>
      <div v-if="applicationsError" class="mt-2 text-[11px] text-text-muted">
        {{ applicationsError }} <button class="underline hover:text-text-main" @click="emit('retry-applications')">{{ t('common.retry') }}</button>
      </div>
    </div>

    <!-- Strength -->
    <div>
      <div class="flex items-center gap-2 mb-3">
        <span class="font-mono text-[13px] font-bold text-primary tracking-[0.2em]">04</span>
        <span class="text-[13px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.strengthFilter') }}</span>
      </div>
      <ul class="space-y-0.5">
        <li v-for="s in strengthBuckets" :key="s.label">
          <label class="group flex items-center gap-3 cursor-pointer py-2.5 md:py-1.5 max-md:min-h-[44px] px-1 -mx-1 hover:bg-canvas transition-colors duration-200">
            <span class="relative flex items-center justify-center shrink-0 w-4 h-4 max-md:w-5 max-md:h-5">
              <input
                type="radio"
                name="strength-bucket"
                class="peer appearance-none w-4 h-4 max-md:w-5 max-md:h-5 border border-outline bg-canvas checked:bg-primary checked:border-primary transition-colors duration-200 cursor-pointer rounded-full"
                :checked="selectedStrength === s.label"
                @change="selectedStrength = s.label"
              >
              <span class="absolute w-1.5 h-1.5 rounded-full bg-canvas opacity-0 peer-checked:opacity-100 pointer-events-none"></span>
            </span>
            <span class="font-mono text-[12px] font-bold text-primary tracking-[0.15em] w-5">{{ s.code }}</span>
            <span class="text-[15px] text-text-secondary group-hover:text-text-main transition-colors duration-200 font-medium leading-tight flex-grow">{{ s.label }}</span>
          </label>
        </li>
      </ul>
    </div>
  </div>
</template>
