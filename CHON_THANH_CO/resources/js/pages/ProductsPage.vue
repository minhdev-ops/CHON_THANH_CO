<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import type { Product } from '../types'
import PageHeader from '../components/PageHeader.vue'
import ErrorState from '../components/ErrorState.vue'
import CTABanner from '../components/CTABanner.vue'
import { t } from '../i18n'
import { fallbackProducts, fallbackCategories, fallbackApplications } from '../types/fallback'

const route = useRoute()

const { data: categoriesData, error: categoriesError, load: loadCategories } = useApiData(() => api.categories(), () => ({ data: fallbackCategories }))
const { data: applicationsData, error: applicationsError, load: loadApplications } = useApiData(() => api.applications(), () => ({ data: fallbackApplications }))

const categories = computed(() => categoriesData.value?.data ?? fallbackCategories)
const applications = computed(() => applicationsData.value?.data ?? fallbackApplications)

const strengthBuckets = computed(() => [
  { label: t('products.strengthLow'), min: 0, max: 50, code: 'L' },
  { label: t('products.strengthMid'), min: 50, max: 100, code: 'M' },
  { label: t('products.strengthHigh'), min: 100, max: null as number | null, code: 'H' },
])

const initialProducts: Product[] = [...fallbackProducts]
const products = ref<Product[]>(initialProducts)

onMounted(async () => {
  applyCategoryFromRoute((route.query.category as string) ?? null)
  await loadMore(true)
  await nextTick()
  window.scrollTo({ top: 0, behavior: 'instant' as ScrollBehavior })
})
const productsLoading = ref(false)
const loadingMore = ref(false)
const loadError = ref<string | null>(null)
const nextCursor = ref<number | null>(null)
const hasMore = computed(() => nextCursor.value !== null && nextCursor.value !== undefined)

const selectedCategories = ref<string[]>([])
const selectedApplications = ref<string[]>([])
const selectedStrength = ref<string | null>(null)
const searchQuery = ref('')
const sortBy = ref<'name' | 'strength' | 'code'>('name')
const viewMode = ref<'grid' | 'index'>('grid')
const mobileFiltersOpen = ref(false)

const toggle = (list: string[], value: string) => {
  const i = list.indexOf(value)
  if (i >= 0) list.splice(i, 1)
  else list.push(value)
}

const activeFilterCount = computed(
  () => selectedCategories.value.length + selectedApplications.value.length + (selectedStrength.value ? 1 : 0) + (searchQuery.value ? 1 : 0)
)

const buildParams = (cursor?: number | null) => {
  const strength = strengthBuckets.value.find((b) => b.label === selectedStrength.value)
  return {
    limit: 50,
    cursor: cursor ?? undefined,
    category: selectedCategories.value.length ? selectedCategories.value.join(',') : undefined,
    application: selectedApplications.value.length ? selectedApplications.value.join(',') : undefined,
    strength_min: strength ? strength.min : undefined,
    strength_max: strength?.max ?? undefined,
  }
}

const loadMore = async (reset = false) => {
  if (reset) {
    nextCursor.value = null
    loadError.value = null
    productsLoading.value = true
  } else {
    loadingMore.value = true
  }
  try {
    const res = await api.products({ ...buildParams(reset ? null : nextCursor.value) })
    let list = res.data ?? []
    if (reset) {
      products.value = list.length ? list : filteredFallbackProducts.value
    } else {
      products.value.push(...list)
    }
    nextCursor.value = res.next_cursor
  } catch (e) {
    loadError.value = e instanceof Error ? e.message : t('common.errorGeneric')
    if (reset) products.value = filteredFallbackProducts.value
  } finally {
    productsLoading.value = false
    loadingMore.value = false
  }
}

const filteredFallbackProducts = computed(() => {
  let list = [...fallbackProducts]
  if (selectedCategories.value.length) {
    list = list.filter((p) => p.category && selectedCategories.value.includes(p.category.slug))
  }
  if (selectedApplications.value.length) {
    list = list.filter((p) => p.applications?.some((a) => selectedApplications.value.includes(a.slug)))
  }
  if (selectedStrength.value) {
    const s = strengthBuckets.value.find((b) => b.label === selectedStrength.value)
    if (s) {
      list = list.filter((p) => {
        const max = Number(p.strength_max) || Number(p.strength_min) || 0
        const min = Number(p.strength_min) || 0
        return max >= s.min && (s.max == null || min <= s.max)
      })
    }
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter((p) => p.name.toLowerCase().includes(q) || (p.code || '').toLowerCase().includes(q))
  }
  if (sortBy.value === 'name') list.sort((a, b) => a.name.localeCompare(b.name))
  if (sortBy.value === 'code') list.sort((a, b) => (a.code || '').localeCompare(b.code || ''))
  return list
})

watch([selectedCategories, selectedApplications, selectedStrength], () => loadMore(true), { deep: true })

const applyCategoryFromRoute = (slug: string | null) => {
  if (slug && !selectedCategories.value.includes(slug)) {
    selectedCategories.value.push(slug)
  }
}

watch(
  () => route.query.category,
  (slug) => applyCategoryFromRoute(typeof slug === 'string' ? slug : null),
)

const clearFilters = () => {
  selectedCategories.value = []
  selectedApplications.value = []
  selectedStrength.value = null
  searchQuery.value = ''
}

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.products') }
])

const paddedIndex = (i: number) => String(i + 1).padStart(2, '0')

// ── Editorial layout ─────────────────────────────────────────────
// Each "group" is 4 products. We render:
//   Group header (label) + Row 1: [8,4] image cards + Row 2: catalog strip [4,4,4]
const GROUP_SIZE = 4

// Returns the layout for product at index `i` in the global list.
const groupMeta = (i: number) => {
  const group = Math.floor(i / GROUP_SIZE)
  const posInGroup = i % GROUP_SIZE
  // pos 0 = big feature (col-span-8), pos 1 = side (col-span-4), pos 2,3 = catalog strip
  if (posInGroup === 0) return { type: 'feature', col: 'md:col-span-8' as const, aspect: 'aspect-[16/9]' as const }
  if (posInGroup === 1) return { type: 'side', col: 'md:col-span-4' as const, aspect: 'aspect-[4/3]' as const }
  return { type: 'catalog', col: 'md:col-span-3' as const, aspect: 'aspect-[4/3]' as const }
}

const isFirstInGroup = (i: number) => i % GROUP_SIZE === 0
const groupNumber = (i: number) => String(Math.floor(i / GROUP_SIZE) + 1).padStart(2, '0')
const groupLabel = (i: number) => {
  const n = Math.floor(i / GROUP_SIZE)
  const labels = ['B—01 / FOUNDATION', 'B—02 / SEPARATION', 'B—03 / REINFORCEMENT', 'B—04 / PROTECTION', 'B—05 / DRAINAGE', 'B—06 / CONTAINMENT', 'B—07 / EROSION', 'B—08 / SPECIAL']
  return labels[n] || `B—${String(n + 1).padStart(2, '0')}`
}

const featuredProduct = computed(() => products.value[0])
const gridProducts = computed(() => products.value.slice(1))

// Split gridProducts into groups of 4
const productGroups = computed(() => {
  const result: { label: string; items: { product: Product; globalIndex: number }[] }[] = []
  for (let i = 0; i < gridProducts.value.length; i += GROUP_SIZE) {
    const items = gridProducts.value.slice(i, i + GROUP_SIZE).map((p, idx) => ({ product: p, globalIndex: i + idx + 1 }))
    result.push({ label: groupLabel(i), items })
  }
  return result
})

// Format strength range nicely: e.g. "100–200 kN/m"
const formatStrength = (p: Product) => {
  const min = p.strength_min
  const max = p.strength_max
  if (min && max) return `${min}–${max}`
  if (min) return `${min}+`
  if (max) return `≤${max}`
  return p.strength_label || '—'
}

// Tints for catalog strip cards (rotate through palette)
const catalogTint = (i: number) => {
  const tints = [
    'bg-canvas border-outline-variant',
    'bg-surface-1 border-outline-variant',
    'bg-text-main text-canvas border-text-main',
    'bg-primary/8 border-primary/40 text-text-main',
  ]
  return tints[i % tints.length]
}
</script>

<template>
  <div>
    <PageHeader :title="t('nav.products')" :breadcrumbs="breadcrumbs" />

    <!-- ═══ EDITORIAL MASTHEAD ═══ -->
    <section class="relative border-b border-outline-variant bg-canvas overflow-hidden">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="grid grid-cols-12 gap-x-6 gap-y-8 items-end">
          <!-- Left: editorial title -->
          <div class="col-span-12 md:col-span-7 reveal">
            <div class="flex items-center gap-3 mb-5">
              <span class="font-mono text-[11px] font-bold text-primary tracking-[0.18em] uppercase">M—01</span>
              <span class="w-8 h-px bg-primary"></span>
              <span class="kicker">{{ t('products.eyebrow') }}</span>
            </div>
            <h1 class="font-sans text-[44px] md:text-[68px] lg:text-[80px] text-text-main font-bold leading-[0.92] tracking-[-0.035em] mb-5">
              {{ t('products.titlePart1') }}<br>
              <span class="italic font-normal text-primary">{{ t('products.titlePart2') }}</span>
            </h1>
            <p class="text-text-secondary text-[15px] md:text-[17px] leading-[1.7] max-w-xl">
              {{ t('products.subtitle') }}
            </p>
          </div>

          <!-- Right: 3-cell stat panel -->
          <div class="col-span-12 md:col-span-5 reveal reveal-delay-1">
            <div class="grid grid-cols-3 border-l-2 border-text-main divide-x divide-outline-variant">
              <div class="pl-5 pr-2 py-1">
                <div class="font-mono text-[11px] font-bold text-text-muted tracking-[0.2em] uppercase mb-3">/01</div>
                <div class="font-sans text-[42px] md:text-[52px] font-bold text-text-main leading-none tabular-nums">{{ String(products.length).padStart(2, '0') }}</div>
                <div class="text-[10px] text-text-secondary font-bold tracking-[0.18em] uppercase mt-2">{{ t('products.statTotal') }}</div>
              </div>
              <div class="pl-5 pr-2 py-1">
                <div class="font-mono text-[11px] font-bold text-text-muted tracking-[0.2em] uppercase mb-3">/02</div>
                <div class="font-sans text-[42px] md:text-[52px] font-bold text-primary leading-none tabular-nums">{{ String(categories.length).padStart(2, '0') }}</div>
                <div class="text-[10px] text-text-secondary font-bold tracking-[0.18em] uppercase mt-2">{{ t('products.statCategory') }}</div>
              </div>
              <div class="pl-5 pr-2 py-1">
                <div class="font-mono text-[11px] font-bold text-text-muted tracking-[0.2em] uppercase mb-3">/03</div>
                <div class="font-sans text-[42px] md:text-[52px] font-bold text-olive leading-none tabular-nums">{{ String(applications.length).padStart(2, '0') }}</div>
                <div class="text-[10px] text-text-secondary font-bold tracking-[0.18em] uppercase mt-2">{{ t('products.statApplication') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <main class="max-w-max-width mx-auto w-full px-margin-mobile md:px-margin-desktop py-10 md:py-14">
      <!-- Mobile filter trigger -->
      <div class="lg:hidden mb-6 flex items-center gap-3">
        <button
          class="flex-1 inline-flex items-center justify-center gap-2 bg-text-main text-canvas border border-text-main py-3.5 text-[12px] font-bold uppercase tracking-[0.18em] rounded-none"
          @click="mobileFiltersOpen = !mobileFiltersOpen"
        >
          <span class="material-symbols-outlined text-[18px]">tune</span>
          <span>{{ t('products.filter') }}</span>
          <span v-if="activeFilterCount" class="bg-primary text-canvas text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center ml-1">{{ activeFilterCount }}</span>
        </button>
      </div>

      <div class="grid grid-cols-12 gap-6 lg:gap-10">
        <!-- ═══ FILTER RAIL ═══ -->
        <aside class="col-span-12 lg:col-span-3">
          <div
            class="lg:sticky lg:top-28 border border-outline-variant bg-surface-1"
            :class="{ 'hidden lg:block': !mobileFiltersOpen, 'block': mobileFiltersOpen }"
          >
            <!-- Rail header -->
            <div class="px-5 py-4 bg-text-main text-canvas flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em]">/FILTER</span>
                <span class="text-[10px] font-bold tracking-[0.2em] uppercase opacity-70">{{ t('products.filter') }}</span>
              </div>
              <span v-if="activeFilterCount" class="font-mono text-[10px] font-bold bg-primary text-canvas rounded-full w-5 h-5 flex items-center justify-center tabular-nums">{{ activeFilterCount }}</span>
            </div>

            <div class="p-5 space-y-7">
              <!-- Search -->
              <div>
                <div class="flex items-center gap-2 mb-3">
                  <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em]">01</span>
                  <span class="text-[10px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.searchLabel') }}</span>
                </div>
                <div class="relative">
                  <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[16px]">search</span>
                  <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="t('products.searchPlaceholder')"
                    class="w-full bg-canvas border border-outline-variant pl-9 pr-3 py-2.5 text-[13px] text-text-main outline-none focus:border-text-main transition-colors duration-200 placeholder:text-text-muted/60 rounded-none"
                  />
                </div>
              </div>

              <!-- Category -->
              <div v-if="!(categoriesError && !categories.length)">
                <div class="flex items-center gap-2 mb-3">
                  <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em]">02</span>
                  <span class="text-[10px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.categoryFilter') }}</span>
                </div>
                <ul class="space-y-0.5">
                  <li v-for="cat in categories" :key="cat.slug">
                    <label class="group flex items-center gap-3 cursor-pointer py-1.5 px-1 -mx-1 hover:bg-canvas transition-colors duration-200">
                      <span class="relative flex items-center justify-center shrink-0 w-4 h-4">
                        <input
                          type="checkbox"
                          class="peer appearance-none w-4 h-4 border border-outline bg-canvas checked:bg-text-main checked:border-text-main transition-colors duration-200 cursor-pointer rounded-[2px]"
                          :checked="selectedCategories.includes(cat.slug)"
                          @change="toggle(selectedCategories, cat.slug)"
                        >
                        <span class="material-symbols-outlined absolute text-canvas text-[12px] font-bold opacity-0 peer-checked:opacity-100 pointer-events-none">check</span>
                      </span>
                      <span class="text-[13px] text-text-secondary group-hover:text-text-main transition-colors duration-200 font-medium flex-grow leading-tight">{{ cat.name }}</span>
                      <span v-if="cat.products_count" class="font-mono text-[10px] text-text-muted font-bold tabular-nums">{{ String(cat.products_count).padStart(2, '0') }}</span>
                    </label>
                  </li>
                </ul>
                <div v-if="categoriesError" class="mt-2 text-[11px] text-text-muted">
                  {{ categoriesError }} <button class="underline hover:text-text-main" @click="loadCategories">{{ t('common.retry') }}</button>
                </div>
              </div>

              <!-- Application -->
              <div v-if="!(applicationsError && !applications.length)">
                <div class="flex items-center gap-2 mb-3">
                  <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em]">03</span>
                  <span class="text-[10px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.applicationFilter') }}</span>
                </div>
                <ul class="space-y-0.5">
                  <li v-for="app in applications" :key="app.slug">
                    <label class="group flex items-center gap-3 cursor-pointer py-1.5 px-1 -mx-1 hover:bg-canvas transition-colors duration-200">
                      <span class="relative flex items-center justify-center shrink-0 w-4 h-4">
                        <input
                          type="checkbox"
                          class="peer appearance-none w-4 h-4 border border-outline bg-canvas checked:bg-text-main checked:border-text-main transition-colors duration-200 cursor-pointer rounded-[2px]"
                          :checked="selectedApplications.includes(app.slug)"
                          @change="toggle(selectedApplications, app.slug)"
                        >
                        <span class="material-symbols-outlined absolute text-canvas text-[12px] font-bold opacity-0 peer-checked:opacity-100 pointer-events-none">check</span>
                      </span>
                      <span class="text-[13px] text-text-secondary group-hover:text-text-main transition-colors duration-200 font-medium leading-tight">{{ app.name }}</span>
                    </label>
                  </li>
                </ul>
                <div v-if="applicationsError" class="mt-2 text-[11px] text-text-muted">
                  {{ applicationsError }} <button class="underline hover:text-text-main" @click="loadApplications">{{ t('common.retry') }}</button>
                </div>
              </div>

              <!-- Strength -->
              <div>
                <div class="flex items-center gap-2 mb-3">
                  <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em]">04</span>
                  <span class="text-[10px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.strengthFilter') }}</span>
                </div>
                <ul class="space-y-0.5">
                  <li v-for="s in strengthBuckets" :key="s.label">
                    <label class="group flex items-center gap-3 cursor-pointer py-1.5 px-1 -mx-1 hover:bg-canvas transition-colors duration-200">
                      <span class="relative flex items-center justify-center shrink-0 w-4 h-4">
                        <input
                          type="radio"
                          name="strength-bucket"
                          class="peer appearance-none w-4 h-4 border border-outline bg-canvas checked:bg-text-main checked:border-text-main transition-colors duration-200 cursor-pointer rounded-full"
                          :checked="selectedStrength === s.label"
                          @change="selectedStrength = s.label"
                        >
                        <span class="absolute w-1.5 h-1.5 rounded-full bg-canvas opacity-0 peer-checked:opacity-100 pointer-events-none"></span>
                      </span>
                      <span class="font-mono text-[10px] font-bold text-primary tracking-[0.15em] w-5">{{ s.code }}</span>
                      <span class="text-[13px] text-text-secondary group-hover:text-text-main transition-colors duration-200 font-medium leading-tight flex-grow">{{ s.label }}</span>
                    </label>
                  </li>
                </ul>
              </div>

              <!-- Actions -->
              <div class="pt-5 border-t border-outline-variant space-y-2">
                <button class="w-full bg-text-main text-canvas py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-primary-deep transition-colors duration-300 rounded-none" @click="loadMore(true)">
                  {{ t('products.apply') }}
                </button>
                <button
                  v-if="activeFilterCount > 0"
                  class="w-full bg-canvas text-text-main border border-outline-variant py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-outline-variant/40 transition-colors duration-300 rounded-none"
                  @click="clearFilters"
                >
                  {{ t('products.clear') }}
                </button>
              </div>
            </div>
          </div>
        </aside>

        <!-- ═══ MAIN GRID ═══ -->
        <section class="col-span-12 lg:col-span-9">
          <!-- Toolbar -->
          <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 pb-4 border-b border-text-main gap-4 reveal">
            <div class="flex items-baseline gap-4">
              <div class="font-sans text-[56px] md:text-[80px] font-bold text-text-main/10 leading-[0.85] tabular-nums select-none">{{ String(products.length).padStart(3, '0') }}</div>
              <div class="flex flex-col gap-1">
                <span class="text-[10px] font-mono font-bold text-primary tracking-[0.2em] uppercase">/MỤC LỤC</span>
                <span class="text-[18px] font-bold text-text-main tracking-[-0.01em]">{{ t('products.all') }}</span>
              </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
              <div class="flex items-center border border-outline-variant bg-canvas">
                <button
                  class="px-3 py-2 transition-colors duration-200"
                  :class="viewMode === 'grid' ? 'bg-text-main text-canvas' : 'text-text-muted hover:text-text-main'"
                  @click="viewMode = 'grid'"
                  :title="t('products.viewGrid')"
                >
                  <span class="material-symbols-outlined text-[16px]">grid_view</span>
                </button>
                <button
                  class="px-3 py-2 transition-colors duration-200 border-l border-outline-variant"
                  :class="viewMode === 'index' ? 'bg-text-main text-canvas' : 'text-text-muted hover:text-text-main'"
                  @click="viewMode = 'index'"
                  :title="t('products.viewList')"
                >
                  <span class="material-symbols-outlined text-[16px]">view_list</span>
                </button>
              </div>
              <select v-model="sortBy" class="bg-canvas border border-outline-variant px-3 py-2 text-[11px] font-bold text-text-main uppercase tracking-[0.15em] focus:outline-none focus:border-text-main cursor-pointer rounded-none">
                <option value="name">A–Z</option>
                <option value="code">{{ t('products.sortCode') }}</option>
              </select>
            </div>
          </div>

          <!-- Active filter chips -->
          <div v-if="activeFilterCount > 0" class="flex flex-wrap items-center gap-2 mb-8 reveal">
            <span class="text-[10px] font-mono font-bold text-text-muted tracking-[0.2em] uppercase">/ACTIVE</span>
            <button
              v-for="cat in selectedCategories" :key="'c-'+cat"
              class="inline-flex items-center gap-1.5 bg-text-main text-canvas pl-2.5 pr-1.5 py-1 text-[10px] font-bold uppercase tracking-[0.15em] hover:bg-primary-deep transition-colors duration-200"
              @click="toggle(selectedCategories, cat)"
            >
              <span>{{ categories.find(c => c.slug === cat)?.name || cat }}</span>
              <span class="material-symbols-outlined text-[12px]">close</span>
            </button>
            <button
              v-for="app in selectedApplications" :key="'a-'+app"
              class="inline-flex items-center gap-1.5 bg-primary text-canvas pl-2.5 pr-1.5 py-1 text-[10px] font-bold uppercase tracking-[0.15em] hover:bg-primary-deep transition-colors duration-200"
              @click="toggle(selectedApplications, app)"
            >
              <span>{{ applications.find(a => a.slug === app)?.name || app }}</span>
              <span class="material-symbols-outlined text-[12px]">close</span>
            </button>
            <button
              v-if="selectedStrength"
              class="inline-flex items-center gap-1.5 bg-olive text-canvas pl-2.5 pr-1.5 py-1 text-[10px] font-bold uppercase tracking-[0.15em] hover:bg-olive-light transition-colors duration-200"
              @click="selectedStrength = null"
            >
              <span>{{ selectedStrength }}</span>
              <span class="material-symbols-outlined text-[12px]">close</span>
            </button>
            <button class="text-[10px] font-bold text-text-muted tracking-[0.18em] uppercase underline hover:text-text-main transition-colors ml-2" @click="clearFilters">{{ t('products.clearAll') }}</button>
          </div>

          <!-- Loading skeleton -->
          <div v-if="productsLoading" class="grid grid-cols-12 gap-4">
            <div v-for="i in 6" :key="i" class="col-span-12 sm:col-span-6 lg:col-span-4 h-80 bg-canvas animate-shimmer border border-outline-variant"></div>
          </div>

          <!-- Error -->
          <div v-else-if="loadError && !products.length" class="bg-surface-1 border border-outline-variant p-8">
            <ErrorState :message="loadError" @retry="loadMore(true)" />
          </div>

          <!-- ═══ EDITORIAL GROUPED GRID ═══ -->
          <div v-else-if="featuredProduct && viewMode === 'grid'" class="space-y-16">
            <!-- HERO FEATURE PRODUCT -->
            <div class="reveal">
              <router-link
                :to="`/products/${featuredProduct.slug}`"
                class="group block relative bg-text-main text-canvas overflow-hidden border border-text-main"
              >
                <div class="grid grid-cols-1 lg:grid-cols-12">
                  <div class="lg:col-span-7 relative aspect-[16/10] lg:aspect-auto lg:min-h-[480px] overflow-hidden">
                    <img
                      :src="featuredProduct.image || '/images/products/geotextile-roll.jpg'"
                      :alt="featuredProduct.name"
                      class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.04] transition-transform duration-[1400ms] ease-out"
                      loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-r from-text-main/30 via-transparent to-text-main/85"></div>
                    <div class="absolute top-5 left-5 inline-flex items-center gap-2 bg-primary text-canvas px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.2em]">
                      <span class="material-symbols-outlined text-[12px] fill">star</span>
                      <span>{{ t('products.featured') }}</span>
                    </div>
                    <div class="absolute bottom-5 left-5 font-sans text-[80px] md:text-[120px] font-bold text-canvas/15 leading-none select-none tabular-nums">01</div>
                  </div>
                  <div class="lg:col-span-5 px-7 py-8 lg:px-10 lg:py-12 flex flex-col justify-between relative">
                    <div class="absolute top-5 right-5 font-mono text-[10px] font-bold text-primary tracking-[0.2em]">P/001</div>
                    <div>
                      <span class="inline-block text-[10px] font-bold text-primary tracking-[0.25em] uppercase mb-4">{{ featuredProduct.category?.name || 'Geosynthetics' }}</span>
                      <h2 class="font-sans text-[28px] md:text-[34px] lg:text-[38px] font-bold leading-[1.05] tracking-[-0.025em] mb-5 group-hover:text-primary transition-colors duration-500">
                        {{ featuredProduct.name }}
                      </h2>
                      <p class="text-canvas/70 text-[14px] leading-[1.7] line-clamp-3">{{ featuredProduct.description }}</p>
                    </div>
                    <div class="mt-8 pt-6 border-t border-canvas/15">
                      <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                          <div class="text-[9px] font-mono font-bold text-canvas/50 tracking-[0.2em] uppercase mb-1.5">/CODE</div>
                          <div class="font-mono text-[16px] font-bold tabular-nums">{{ featuredProduct.code || '—' }}</div>
                        </div>
                        <div>
                          <div class="text-[9px] font-mono font-bold text-canvas/50 tracking-[0.2em] uppercase mb-1.5">/STRENGTH</div>
                          <div class="font-mono text-[16px] font-bold text-primary tabular-nums">{{ formatStrength(featuredProduct) }}</div>
                        </div>
                      </div>
                      <div class="inline-flex items-center gap-2 text-[11px] font-bold tracking-[0.2em] uppercase text-primary group-hover:gap-3 transition-all duration-300">
                        <span>{{ t('products.viewDetail') }}</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                      </div>
                    </div>
                  </div>
                </div>
              </router-link>
            </div>

            <!-- PRODUCT GROUPS -->
            <div v-for="(group, gi) in productGroups" :key="group.label" class="space-y-5 reveal">
              <!-- Group divider label -->
              <div class="flex items-center gap-4 pb-3 border-b-2 border-text-main">
                <span class="font-mono text-[11px] font-bold text-primary tracking-[0.25em] uppercase tabular-nums">{{ group.label }}</span>
                <span class="flex-grow h-px bg-outline-variant"></span>
                <span class="font-mono text-[10px] font-bold text-text-muted tracking-[0.2em] uppercase tabular-nums">{{ group.items.length }} ITEMS</span>
              </div>

              <!-- Row 1: feature [8] + side [4] -->
              <div class="grid grid-cols-12 gap-4">
                <!-- Feature card (item 0) -->
                <router-link
                  v-if="group.items[0]"
                  :to="`/products/${group.items[0].product.slug}`"
                  class="group block relative bg-canvas border border-outline-variant hover:border-text-main transition-colors duration-500 md:col-span-8"
                >
                  <div class="relative w-full overflow-hidden bg-surface-0 aspect-[16/9]">
                    <img
                      :src="group.items[0].product.image || '/images/products/geotextile-roll.jpg'"
                      :alt="group.items[0].product.name"
                      class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-[1000ms] ease-out"
                      loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-text-main/65 via-text-main/15 to-transparent"></div>
                    <div class="absolute top-3 left-3 inline-flex items-center gap-1.5 bg-canvas/95 backdrop-blur text-text-main px-2.5 py-1 text-[9px] font-mono font-bold tracking-[0.18em] uppercase">
                      <span>{{ group.items[0].product.category?.name || '—' }}</span>
                    </div>
                    <div class="absolute top-3 right-3 font-sans text-[44px] md:text-[64px] font-bold text-canvas leading-none select-none tabular-nums mix-blend-difference opacity-90">{{ paddedIndex(group.items[0].globalIndex) }}</div>
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                      <h3 class="font-sans text-canvas text-[20px] md:text-[26px] lg:text-[30px] font-bold leading-[1.1] tracking-[-0.02em] mb-3 group-hover:text-primary transition-colors duration-300 max-w-xl">
                        {{ group.items[0].product.name }}
                      </h3>
                      <div class="flex items-center gap-4 text-canvas/80 text-[11px] font-mono tracking-[0.15em] uppercase">
                        <span>{{ group.items[0].product.code || '—' }}</span>
                        <span class="w-px h-3 bg-canvas/40"></span>
                        <span class="text-primary">{{ formatStrength(group.items[0].product) }} kN/m</span>
                      </div>
                    </div>
                  </div>
                </router-link>

                <!-- Side card (item 1) -->
                <router-link
                  v-if="group.items[1]"
                  :to="`/products/${group.items[1].product.slug}`"
                  class="group block relative bg-canvas border border-outline-variant hover:border-text-main transition-colors duration-500 md:col-span-4"
                >
                  <div class="relative w-full overflow-hidden bg-surface-0 aspect-[4/3]">
                    <img
                      :src="group.items[1].product.image || '/images/products/geotextile-roll.jpg'"
                      :alt="group.items[1].product.name"
                      class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-[1000ms] ease-out"
                      loading="lazy"
                    >
                    <div class="absolute top-3 right-3 font-sans text-[40px] font-bold text-canvas leading-none select-none tabular-nums mix-blend-difference opacity-90">{{ paddedIndex(group.items[1].globalIndex) }}</div>
                    <div v-if="group.items[1].product.strength_label" class="absolute bottom-3 left-3 inline-flex items-center gap-1.5 bg-text-main/90 backdrop-blur text-canvas px-2 py-1 text-[9px] font-mono font-bold tracking-[0.15em] uppercase">
                      <span class="w-1 h-1 bg-primary"></span>
                      <span>{{ formatStrength(group.items[1].product) }} kN/m</span>
                    </div>
                  </div>
                  <div class="p-4 border-t border-outline-variant bg-canvas">
                    <span class="text-[9px] font-mono font-bold text-primary tracking-[0.2em] uppercase mb-1.5 block">{{ group.items[1].product.category?.name || '—' }}</span>
                    <h3 class="font-sans text-[14px] font-bold text-text-main leading-snug line-clamp-2 group-hover:text-primary transition-colors duration-300 mb-2">
                      {{ group.items[1].product.name }}
                    </h3>
                    <div class="flex items-center justify-between pt-2 border-t border-outline-variant/60">
                      <span class="font-mono text-[10px] font-bold text-text-muted tracking-[0.1em] tabular-nums">{{ group.items[1].product.code || '—' }}</span>
                      <span class="material-symbols-outlined text-[14px] text-text-muted group-hover:text-text-main group-hover:translate-x-0.5 transition-all duration-300">arrow_forward</span>
                    </div>
                  </div>
                </router-link>
              </div>

              <!-- Row 2: catalog strip [4,4,4] -->
              <div class="grid grid-cols-12 gap-3">
                <router-link
                  v-for="(item, idx) in group.items.slice(2)" :key="item.product.slug"
                  :to="`/products/${item.product.slug}`"
                  class="group block relative border transition-all duration-500 hover:border-text-main md:col-span-4 reveal"
                  :class="catalogTint(idx)"
                  :style="{ transitionDelay: `${idx * 60}ms` }"
                >
                  <div class="p-5 md:p-6">
                    <div class="flex items-start justify-between mb-4">
                      <span class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase" :class="idx === 2 ? 'text-text-muted' : 'opacity-70'">{{ item.product.category?.name || '—' }}</span>
                      <span class="font-sans text-[24px] font-bold leading-none select-none tabular-nums" :class="idx === 2 ? 'text-text-main/15' : (idx === 4 ? 'text-canvas/30' : 'text-primary/30')">{{ paddedIndex(item.globalIndex) }}</span>
                    </div>
                    <h3 class="font-sans text-[16px] md:text-[18px] font-bold leading-snug tracking-[-0.01em] mb-4 group-hover:text-primary transition-colors duration-300 line-clamp-2">
                      {{ item.product.name }}
                    </h3>
                    <div class="pt-4 border-t" :class="idx === 2 ? 'border-outline-variant' : 'border-canvas/15'">
                      <div class="flex items-center justify-between mb-3">
                        <span class="font-mono text-[10px] font-bold tracking-[0.1em] tabular-nums" :class="idx === 2 ? 'text-text-secondary' : 'opacity-70'">{{ item.product.code || '—' }}</span>
                        <span class="font-mono text-[10px] font-bold tabular-nums" :class="idx === 2 ? 'text-primary' : 'text-primary'">{{ formatStrength(item.product) }} kN/m</span>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-[9px] font-bold tracking-[0.18em] uppercase" :class="idx === 2 ? 'text-text-muted' : 'opacity-70'">{{ t('products.detail') }}</span>
                        <span class="material-symbols-outlined text-[14px] group-hover:translate-x-0.5 transition-transform duration-300">arrow_forward</span>
                      </div>
                    </div>
                  </div>
                </router-link>
                <!-- Pad if group < 4 items -->
                <div v-for="n in (GROUP_SIZE - 2 - group.items.slice(2).length)" :key="'pad-'+n" class="hidden md:block md:col-span-4"></div>
              </div>
            </div>
          </div>

          <!-- ═══ INDEX VIEW (table-style catalog) ═══ -->
          <div v-else-if="products.length && viewMode === 'index'" class="bg-canvas border border-outline-variant">
            <!-- Sticky header -->
            <div class="grid grid-cols-12 gap-3 px-4 md:px-6 py-3 bg-text-main text-canvas text-[10px] font-bold tracking-[0.2em] uppercase sticky top-28 z-20">
              <div class="col-span-1">№</div>
              <div class="col-span-12 md:col-span-2">Code</div>
              <div class="col-span-12 md:col-span-5">Product</div>
              <div class="col-span-6 md:col-span-2">Category</div>
              <div class="col-span-3 md:col-span-1 text-right">Strength</div>
              <div class="col-span-3 md:col-span-1 text-right">View</div>
            </div>
            <div class="divide-y divide-outline-variant">
              <router-link
                v-for="(product, i) in products" :key="product.slug"
                :to="`/products/${product.slug}`"
                class="grid grid-cols-12 gap-3 items-center px-4 md:px-6 py-4 hover:bg-surface-1 transition-colors duration-200 group reveal"
              >
                <div class="col-span-1 font-mono text-[14px] md:text-[16px] font-bold text-text-muted group-hover:text-primary transition-colors duration-200 tabular-nums">{{ paddedIndex(i) }}</div>
                <div class="col-span-12 md:col-span-2 font-mono text-[11px] font-bold text-text-main tracking-[0.08em] tabular-nums">{{ product.code || '—' }}</div>
                <div class="col-span-12 md:col-span-5">
                  <div class="font-bold text-[13px] md:text-[14px] text-text-main group-hover:text-primary transition-colors duration-200 line-clamp-1">{{ product.name }}</div>
                </div>
                <div class="col-span-6 md:col-span-2 text-[10px] text-text-muted font-bold tracking-[0.15em] uppercase">{{ product.category?.name || '—' }}</div>
                <div class="col-span-3 md:col-span-1 text-right font-mono text-[11px] font-bold text-primary tabular-nums">{{ formatStrength(product) }}</div>
                <div class="col-span-3 md:col-span-1 text-right">
                  <span class="inline-flex items-center justify-center w-8 h-8 border border-outline-variant group-hover:bg-text-main group-hover:text-canvas group-hover:border-text-main transition-all duration-200">
                    <span class="material-symbols-outlined text-[14px] group-hover:translate-x-0.5 transition-transform duration-200">arrow_forward</span>
                  </span>
                </div>
              </router-link>
            </div>
          </div>

          <!-- Empty -->
          <div v-else class="py-20 text-center bg-canvas border border-outline-variant">
            <div class="font-sans text-[80px] font-bold text-text-muted/30 leading-none mb-4 select-none">∅</div>
            <p class="text-text-secondary font-medium mb-6 text-[13px] uppercase tracking-[0.2em]">{{ t('products.empty') }}</p>
            <button class="bg-text-main text-canvas px-7 py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-primary-deep transition-colors duration-300" @click="clearFilters">
              {{ t('products.clearAll') }}
            </button>
          </div>

          <div v-if="hasMore && !productsLoading" class="mt-12 flex justify-center">
            <button type="button" class="group inline-flex items-center gap-3 bg-transparent border border-text-main text-text-main px-8 py-3.5 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-text-main hover:text-canvas transition-all duration-300 rounded-none" :disabled="loadingMore" @click="loadMore()">
              <span>{{ loadingMore ? t('common.loading') : t('common.loadMore') }}</span>
              <span v-if="!loadingMore" class="material-symbols-outlined text-[16px] group-hover:translate-y-0.5 transition-transform duration-300">expand_more</span>
            </button>
          </div>
        </section>
      </div>
    </main>

    <CTABanner />
  </div>
</template>
