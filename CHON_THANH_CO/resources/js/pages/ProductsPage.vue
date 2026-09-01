<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import type { Product } from '../types'
import PageHeader from '../components/PageHeader.vue'
import ErrorState from '../components/ErrorState.vue'
import CTABanner from '../components/CTABanner.vue'
import ProductCard from '../components/ProductCard.vue'
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

const formatStrength = (p: Product) => {
  const min = p.strength_min
  const max = p.strength_max
  if (min && max) return `${min}–${max}`
  if (min) return `${min}+`
  if (max) return `≤${max}`
  return p.strength_label || '—'
}

// Consistent grid layout for Products: 3 cards per row
const bentoCol = (i: number) => {
  return 'md:col-span-6 lg:col-span-4'
}
</script>

<template>
  <div>
    <PageHeader :title="t('nav.products')" :breadcrumbs="breadcrumbs" />

    <main class="max-w-max-width mx-auto w-full px-margin-mobile md:px-margin-desktop py-10 md:py-14 animate-fade-in-up">
      <!-- Mobile filter trigger -->
      <div class="lg:hidden mb-6 flex items-center gap-3">
        <button
          class="flex-1 inline-flex items-center justify-center gap-2 bg-primary text-white border border-text-main py-3.5 text-[12px] font-bold uppercase tracking-[0.18em] rounded-none"
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
            class="lg:sticky lg:top-36 border border-outline-variant bg-surface-glass backdrop-blur-xl rounded-[24px] shadow-sm overflow-hidden"
            :class="{ 'hidden lg:block': !mobileFiltersOpen, 'block': mobileFiltersOpen }"
          >
            <!-- Rail header -->
            <div class="px-5 py-4 bg-primary text-white flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em]">/FILTER</span>
                <span class="text-[10px] font-bold tracking-[0.2em] uppercase opacity-70">{{ t('products.filter') }}</span>
              </div>
              <span v-if="activeFilterCount" class="font-mono text-[10px] font-bold bg-primary text-canvas rounded-full w-5 h-5 flex items-center justify-center tabular-nums">{{ activeFilterCount }}</span>
            </div>

            <div class="p-5 space-y-7 max-h-[65vh] overflow-y-auto custom-scrollbar">
              <!-- Search -->
              <div>
                <div class="flex items-center gap-2 mb-3">
                  <span class="font-mono text-[13px] font-bold text-primary tracking-[0.2em]">01</span>
                  <span class="text-[13px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.searchLabel') }}</span>
                </div>
                <div class="relative">
                  <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[16px]">search</span>
                  <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="t('products.searchPlaceholder')"
                    class="w-full bg-canvas border border-outline-variant pl-9 pr-3 py-2.5 text-[15px] text-text-main outline-none focus:border-text-main transition-colors duration-200 placeholder:text-text-muted/60 rounded-none"
                  />
                </div>
              </div>

              <!-- Category -->
              <div v-if="!(categoriesError && !categories.length)">
                <div class="flex items-center gap-2 mb-3">
                  <span class="font-mono text-[13px] font-bold text-primary tracking-[0.2em]">02</span>
                  <span class="text-[13px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.categoryFilter') }}</span>
                </div>
                <ul class="space-y-0.5">
                  <li v-for="cat in categories" :key="cat.slug">
                    <label class="group flex items-center gap-3 cursor-pointer py-1.5 px-1 -mx-1 hover:bg-canvas transition-colors duration-200">
                      <span class="relative flex items-center justify-center shrink-0 w-4 h-4">
                        <input
                          type="checkbox"
                          class="peer appearance-none w-4 h-4 border border-outline bg-canvas checked:bg-primary checked:border-primary transition-colors duration-200 cursor-pointer rounded-[2px]"
                          :checked="selectedCategories.includes(cat.slug)"
                          @change="toggle(selectedCategories, cat.slug)"
                        >
                        <span class="material-symbols-outlined absolute text-canvas text-[12px] font-bold opacity-0 peer-checked:opacity-100 pointer-events-none">check</span>
                      </span>
                      <span class="text-[15px] text-text-secondary group-hover:text-text-main transition-colors duration-200 font-medium flex-grow leading-tight">{{ cat.name }}</span>
                      <span v-if="cat.products_count" class="font-mono text-[12px] text-text-muted font-bold tabular-nums">{{ String(cat.products_count).padStart(2, '0') }}</span>
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
                  <span class="font-mono text-[13px] font-bold text-primary tracking-[0.2em]">03</span>
                  <span class="text-[13px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.applicationFilter') }}</span>
                </div>
                <ul class="space-y-0.5 max-h-48 overflow-y-auto custom-scrollbar pr-2">
                  <li v-for="app in applications" :key="app.slug">
                    <label class="group flex items-center gap-3 cursor-pointer py-1.5 px-1 -mx-1 hover:bg-canvas transition-colors duration-200">
                      <span class="relative flex items-center justify-center shrink-0 w-4 h-4">
                        <input
                          type="checkbox"
                          class="peer appearance-none w-4 h-4 border border-outline bg-canvas checked:bg-primary checked:border-primary transition-colors duration-200 cursor-pointer rounded-[2px]"
                          :checked="selectedApplications.includes(app.slug)"
                          @change="toggle(selectedApplications, app.slug)"
                        >
                        <span class="material-symbols-outlined absolute text-canvas text-[12px] font-bold opacity-0 peer-checked:opacity-100 pointer-events-none">check</span>
                      </span>
                      <span class="text-[15px] text-text-secondary group-hover:text-text-main transition-colors duration-200 font-medium leading-tight">{{ app.name }}</span>
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
                  <span class="font-mono text-[13px] font-bold text-primary tracking-[0.2em]">04</span>
                  <span class="text-[13px] font-bold text-text-main uppercase tracking-[0.2em]">{{ t('products.strengthFilter') }}</span>
                </div>
                <ul class="space-y-0.5">
                  <li v-for="s in strengthBuckets" :key="s.label">
                    <label class="group flex items-center gap-3 cursor-pointer py-1.5 px-1 -mx-1 hover:bg-canvas transition-colors duration-200">
                      <span class="relative flex items-center justify-center shrink-0 w-4 h-4">
                        <input
                          type="radio"
                          name="strength-bucket"
                          class="peer appearance-none w-4 h-4 border border-outline bg-canvas checked:bg-primary checked:border-primary transition-colors duration-200 cursor-pointer rounded-full"
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

            <!-- Actions -->
            <div class="p-5 border-t border-outline-variant space-y-2 bg-surface-1">
              <button class="w-full bg-primary text-white py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-primary-deep transition-colors duration-300 rounded-none" @click="loadMore(true)">
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
        </aside>

        <!-- ═══ MAIN GRID ═══ -->
        <section class="col-span-12 lg:col-span-9">
          <!-- Toolbar -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-end mb-8 pb-4 border-b border-text-main gap-4 reveal">
            <div class="flex items-center gap-3 flex-wrap">
              <div class="flex items-center border border-outline-variant bg-canvas">
                <button
                  class="px-3 py-2 transition-colors duration-200"
                  :class="viewMode === 'grid' ? 'bg-primary text-white' : 'text-text-muted hover:text-text-main'"
                  @click="viewMode = 'grid'"
                  :title="t('products.viewGrid')"
                >
                  <span class="material-symbols-outlined text-[16px]">grid_view</span>
                </button>
                <button
                  class="px-3 py-2 transition-colors duration-200 border-l border-outline-variant"
                  :class="viewMode === 'index' ? 'bg-primary text-white' : 'text-text-muted hover:text-text-main'"
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
              class="inline-flex items-center gap-1.5 bg-primary text-white pl-2.5 pr-1.5 py-1 text-[10px] font-bold uppercase tracking-[0.15em] hover:bg-primary-deep transition-colors duration-200"
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

          <!-- ═══ BENTO GRID ═══ -->
          <div v-else-if="products.length && viewMode === 'grid'" class="grid grid-cols-12 gap-6 stagger-grid">
            <div v-for="(p, i) in products" :key="p.slug" class="col-span-12 reveal" :class="[bentoCol(i), `reveal-delay-${(i%4)+1}`]">
              <ProductCard :product="p" class="h-full" />
            </div>
          </div>

          <!-- ═══ INDEX VIEW (table-style catalog) ═══ -->
          <div v-else-if="products.length && viewMode === 'index'" class="bg-canvas border border-outline-variant">
            <!-- Sticky header -->
            <div class="grid grid-cols-12 gap-3 px-4 md:px-6 py-3 bg-primary text-white text-[10px] font-bold tracking-[0.2em] uppercase sticky top-28 z-20">
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
                  <span class="inline-flex items-center justify-center w-8 h-8 border border-outline-variant group-hover:bg-primary group-hover:text-white group-hover:border-primary transition-all duration-200">
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
            <button class="bg-primary text-white px-7 py-3 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-primary-deep transition-colors duration-300" @click="clearFilters">
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
