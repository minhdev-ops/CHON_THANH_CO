<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import type { Product } from '../types'
import PageHeader from '../components/PageHeader.vue'
import ErrorState from '../components/ErrorState.vue'
import CTABanner from '../components/CTABanner.vue'
import ProductCard from '../components/ProductCard.vue'
import ProductFilterPanel from '../components/ProductFilterPanel.vue'
import Pagination from '../components/Pagination.vue'
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
const productsLoading = ref(false)
const loadError = ref<string | null>(null)

const selectedCategories = ref<string[]>([])
const selectedApplications = ref<string[]>([])
const selectedStrength = ref<string | null>(null)
const searchQuery = ref('')
const sortBy = ref<'name' | 'strength' | 'code'>('name')
const viewMode = ref<'grid' | 'index'>('grid')
const mobileFiltersOpen = ref(false)

const currentPage = ref(1)
const pageSize = ref(9)

const toggle = (list: string[], value: string) => {
  const i = list.indexOf(value)
  if (i >= 0) list.splice(i, 1)
  else list.push(value)
}

const activeFilterCount = computed(
  () => selectedCategories.value.length + selectedApplications.value.length + (selectedStrength.value ? 1 : 0) + (searchQuery.value ? 1 : 0)
)

const loadProducts = async () => {
  productsLoading.value = true
  loadError.value = null
  try {
    const res = await api.products({ limit: 50 })
    if (res.data && res.data.length > 0) {
      products.value = res.data
    } else {
      products.value = [...fallbackProducts]
    }
  } catch (e) {
    products.value = [...fallbackProducts]
    loadError.value = null
  } finally {
    productsLoading.value = false
  }
}

const filteredProducts = computed(() => {
  let list = products.value.length ? [...products.value] : [...fallbackProducts]
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

const totalPages = computed(() => Math.max(1, Math.ceil(filteredProducts.value.length / pageSize.value)))

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredProducts.value.slice(start, start + pageSize.value)
})

const onPageChange = (page: number) => {
  currentPage.value = page
  const el = document.getElementById('products-main-section')
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  } else {
    window.scrollTo({ top: 250, behavior: 'smooth' })
  }
}

watch([selectedCategories, selectedApplications, selectedStrength, searchQuery, sortBy], () => {
  currentPage.value = 1
}, { deep: true })

onMounted(async () => {
  applyCategoryFromRoute((route.query.category as string) ?? null)
  await loadProducts()
  await nextTick()
  window.scrollTo({ top: 0, behavior: 'instant' as ScrollBehavior })
})

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
  currentPage.value = 1
}

// Lock body scroll while the mobile filter sheet is open
watch(mobileFiltersOpen, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
})
onBeforeUnmount(() => {
  document.body.style.overflow = ''
})

const toggleCategory = (slug: string) => toggle(selectedCategories.value, slug)

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
      <!-- Mobile: app-style search bar + category chips (dính dưới app bar) -->
      <div class="lg:hidden -mx-margin-mobile px-margin-mobile mb-5 sticky top-[56px] z-30 bg-canvas/95 backdrop-blur-md border-b border-outline-variant/50">
        <div class="pt-2 pb-2">
          <div class="flex items-center gap-2">
            <label class="relative flex-1 min-w-0">
              <i class="fa fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[15px] text-text-muted"></i>
              <input
                v-model="searchQuery"
                type="search"
                enterkeyhint="search"
                :placeholder="t('products.searchPlaceholder')"
                class="w-full h-11 pl-10 pr-3 rounded-xl bg-surface-1 border border-outline-variant text-[16px] text-text-main outline-none focus:border-primary focus:ring-2 focus:ring-primary/15 transition-all placeholder:text-text-muted/70 appearance-none"
              />
            </label>
            <button
              type="button"
              class="relative shrink-0 h-11 px-3.5 rounded-xl btn-primary inline-flex items-center justify-center gap-2 text-[12px] font-bold uppercase tracking-[0.1em] active:scale-[0.97] transition-transform shadow-sm"
              :aria-label="`${t('products.filter')}${activeFilterCount ? ` (${activeFilterCount})` : ''}`"
              @click="mobileFiltersOpen = true"
            >
              <i class="fa text-[16px] fa-sliders"></i>
              <span class="hidden min-[400px]:inline">{{ t('products.filter') }}</span>
              <span v-if="activeFilterCount" class="bg-white text-primary-deep text-[11px] font-bold rounded-full min-w-5 h-5 px-1 flex items-center justify-center">{{ activeFilterCount }}</span>
            </button>
          </div>
        </div>
        <div class="flex gap-2 overflow-x-auto snap-x snap-mandatory no-scrollbar pb-2.5">
          <button
            class="shrink-0 snap-start px-4 rounded-full border text-[13px] font-semibold whitespace-nowrap transition-colors duration-200 min-h-[44px] active:scale-[0.97]"
            :class="selectedCategories.length === 0 ? 'bg-primary-deep text-white border-primary-deep shadow-sm' : 'bg-surface-1 text-text-secondary border-outline-variant'"
            @click="selectedCategories = []"
          >
            {{ t('projects.all') }}
          </button>
          <button
            v-for="cat in categories"
            :key="'pill-' + cat.slug"
            class="shrink-0 snap-start px-4 rounded-full border text-[13px] font-semibold whitespace-nowrap transition-colors duration-200 min-h-[44px] active:scale-[0.97]"
            :class="selectedCategories.includes(cat.slug) ? 'bg-primary-deep text-white border-primary-deep shadow-sm' : 'bg-surface-1 text-text-secondary border-outline-variant'"
            @click="toggleCategory(cat.slug)"
          >
            {{ cat.name }}
          </button>
        </div>
      </div>

      <div class="grid grid-cols-12 gap-6 lg:gap-10">
        <!-- ═══ FILTER RAIL (desktop) ═══ -->
        <aside class="col-span-12 lg:col-span-3 hidden lg:block">
          <div
            class="lg:sticky lg:top-36 border border-outline-variant bg-surface-glass backdrop-blur-xl rounded-2xl lg:rounded-[24px] shadow-sm overflow-hidden"
          >
            <!-- Rail header -->
            <div class="px-5 py-4 bg-primary text-white flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="font-mono text-[11px] font-bold text-primary tracking-[0.2em]">/FILTER</span>
                <span class="text-[11px] font-bold tracking-[0.2em] uppercase opacity-70">{{ t('products.filter') }}</span>
              </div>
              <span v-if="activeFilterCount" class="font-mono text-[11px] font-bold bg-primary text-canvas rounded-full w-5 h-5 flex items-center justify-center tabular-nums">{{ activeFilterCount }}</span>
            </div>

            <div class="p-5 max-h-[65vh] overflow-y-auto custom-scrollbar">
              <ProductFilterPanel
                v-model:search-query="searchQuery"
                v-model:selected-categories="selectedCategories"
                v-model:selected-applications="selectedApplications"
                v-model:selected-strength="selectedStrength"
                :categories="categories"
                :applications="applications"
                :strength-buckets="strengthBuckets"
                :categories-error="categoriesError"
                :applications-error="applicationsError"
                @retry-categories="loadCategories"
                @retry-applications="loadApplications"
              />
            </div>

            <!-- Actions -->
            <div class="p-5 border-t border-outline-variant space-y-2 bg-surface-1">
              <button class="w-full btn-primary py-3 text-[11px] font-bold uppercase tracking-[0.2em] transition-colors duration-300 rounded-none" @click="currentPage = 1">
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
        <section id="products-main-section" class="col-span-12 lg:col-span-9 scroll-mt-32">
          <!-- Toolbar -->
          <div class="flex items-center justify-between mb-5 md:mb-8 pb-3 md:pb-4 border-b border-text-main gap-3 md:gap-4 reveal">
            <div class="text-[13px] text-text-secondary font-medium">
              <span>Tìm thấy </span>
              <strong class="text-text-main font-bold tabular-nums">{{ filteredProducts.length }}</strong>
              <span> sản phẩm</span>
            </div>
            <div class="flex items-center gap-2 md:gap-3">
              <div class="hidden sm:flex items-center border border-outline-variant bg-canvas">
                <button
                  class="px-3 py-2 transition-colors duration-200"
                  :class="viewMode === 'grid' ? 'bg-primary text-white' : 'text-text-muted hover:text-text-main'"
                  @click="viewMode = 'grid'"
                  :title="t('products.viewGrid')"
                >
                  <i class="fa text-[16px] fa-th"></i>
                </button>
                <button
                  class="px-3 py-2 transition-colors duration-200 border-l border-outline-variant"
                  :class="viewMode === 'index' ? 'bg-primary text-white' : 'text-text-muted hover:text-text-main'"
                  @click="viewMode = 'index'"
                  :title="t('products.viewList')"
                >
                  <i class="fa text-[16px] fa-list"></i>
                </button>
              </div>
              <select v-model="sortBy" class="h-11 md:h-auto bg-surface-1 md:bg-canvas border border-outline-variant rounded-xl md:rounded-none px-3 text-[12px] md:text-[11px] font-bold text-text-main uppercase tracking-[0.08em] md:tracking-[0.15em] focus:outline-none focus:border-text-main cursor-pointer">
                <option value="name">A–Z</option>
                <option value="code">{{ t('products.sortCode') }}</option>
              </select>
            </div>
          </div>

          <!-- Active filter chips -->
          <div v-if="activeFilterCount > 0" class="flex flex-wrap items-center gap-2 mb-8 reveal">
            <span class="text-[11px] font-mono font-bold text-text-muted tracking-[0.2em] uppercase">/ACTIVE</span>
            <button
              v-for="cat in selectedCategories" :key="'c-'+cat"
              class="inline-flex items-center gap-1.5 btn-primary pl-2.5 pr-1.5 py-1 text-[11px] font-bold uppercase tracking-[0.15em] transition-colors duration-200"
              @click="toggle(selectedCategories, cat)"
            >
              <span>{{ categories.find(c => c.slug === cat)?.name || cat }}</span>
              <i class="fa text-[12px] fa-xmark"></i>
            </button>
            <button
              v-for="app in selectedApplications" :key="'a-'+app"
              class="inline-flex items-center gap-1.5 btn-primary pl-2.5 pr-1.5 py-1 text-[11px] font-bold uppercase tracking-[0.15em] transition-colors duration-200"
              @click="toggle(selectedApplications, app)"
            >
              <span>{{ applications.find(a => a.slug === app)?.name || app }}</span>
              <i class="fa text-[12px] fa-xmark"></i>
            </button>
            <button
              v-if="selectedStrength"
              class="inline-flex items-center gap-1.5 bg-olive text-canvas pl-2.5 pr-1.5 py-1 text-[11px] font-bold uppercase tracking-[0.15em] hover:bg-olive-light transition-colors duration-200"
              @click="selectedStrength = null"
            >
              <span>{{ selectedStrength }}</span>
              <i class="fa text-[12px] fa-xmark"></i>
            </button>
            <button class="text-[11px] font-bold text-text-muted tracking-[0.18em] uppercase underline hover:text-text-main transition-colors ml-2" @click="clearFilters">{{ t('products.clearAll') }}</button>
          </div>

          <!-- Loading skeleton -->
          <div v-if="productsLoading" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-6">
            <div v-for="i in 6" :key="i" class="h-40 sm:h-80 bg-canvas animate-shimmer border border-outline-variant rounded-2xl md:rounded-xl"></div>
          </div>

          <!-- Error -->
          <div v-else-if="loadError && !filteredProducts.length" class="bg-surface-1 border border-outline-variant p-8">
            <ErrorState :message="loadError" @retry="loadProducts" />
          </div>

          <!-- ═══ BENTO GRID ═══ -->
          <div v-else-if="filteredProducts.length && viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-6 stagger-grid">
            <div v-for="(p, i) in paginatedProducts" :key="p.slug" class="reveal" :class="[`reveal-delay-${(i%3)+1}`]">
              <ProductCard :product="p" variant="catalog" class="h-full" />
            </div>
          </div>

          <!-- ═══ INDEX VIEW (table-style catalog) ═══ -->
          <div v-else-if="filteredProducts.length && viewMode === 'index'" class="bg-canvas border border-outline-variant">
            <!-- Sticky header -->
            <div class="grid grid-cols-12 gap-3 px-4 md:px-6 py-3 bg-primary text-white text-[11px] font-bold tracking-[0.2em] uppercase sticky top-28 z-20">
              <div class="col-span-1">№</div>
              <div class="col-span-12 md:col-span-2">Code</div>
              <div class="col-span-12 md:col-span-5">Product</div>
              <div class="col-span-6 md:col-span-2">Category</div>
              <div class="col-span-3 md:col-span-1 text-right">Strength</div>
              <div class="col-span-3 md:col-span-1 text-right">View</div>
            </div>
            <div class="divide-y divide-outline-variant">
              <router-link
                v-for="(product, i) in paginatedProducts" :key="product.slug"
                :to="`/products/${product.slug}`"
                class="grid grid-cols-12 gap-3 items-center px-4 md:px-6 py-4 hover:bg-surface-1 transition-colors duration-200 group reveal"
              >
                <div class="col-span-1 font-mono text-[14px] md:text-[16px] font-bold text-text-muted group-hover:text-primary transition-colors duration-200 tabular-nums">{{ paddedIndex((currentPage - 1) * pageSize + i) }}</div>
                <div class="col-span-12 md:col-span-2 font-mono text-[11px] font-bold text-text-main tracking-[0.08em] tabular-nums">{{ product.code || '—' }}</div>
                <div class="col-span-12 md:col-span-5">
                  <div class="font-bold text-[13px] md:text-[14px] text-text-main group-hover:text-primary transition-colors duration-200 line-clamp-1">{{ product.name }}</div>
                </div>
                <div class="col-span-6 md:col-span-2 text-[11px] text-text-muted font-bold tracking-[0.15em] uppercase">{{ product.category?.name || '—' }}</div>
                <div class="col-span-3 md:col-span-1 text-right font-mono text-[11px] font-bold text-primary tabular-nums">{{ formatStrength(product) }}</div>
                <div class="col-span-3 md:col-span-1 text-right">
                  <span class="inline-flex items-center justify-center w-8 h-8 border border-outline-variant group-hover:bg-primary group-hover:text-white group-hover:border-primary transition-all duration-200">
                    <i class="fa text-[14px] group-hover:translate-x-0.5 transition-transform duration-200 fa-arrow-right"></i>
                  </span>
                </div>
              </router-link>
            </div>
          </div>

          <!-- Empty -->
          <div v-else class="py-20 text-center bg-canvas border border-outline-variant">
            <div class="font-sans text-[80px] font-bold text-text-muted/30 leading-none mb-4 select-none">∅</div>
            <p class="text-text-secondary font-medium mb-6 text-[13px] uppercase tracking-[0.2em]">{{ t('products.empty') }}</p>
            <button class="btn-primary px-7 py-3 text-[11px] font-bold uppercase tracking-[0.2em] transition-colors duration-300" @click="clearFilters">
              {{ t('products.clearAll') }}
            </button>
          </div>

          <!-- ═══ PAGINATION ═══ -->
          <Pagination
            v-if="!productsLoading && filteredProducts.length > 0"
            v-model:current-page="currentPage"
            :total-pages="totalPages"
            :total-items="filteredProducts.length"
            :page-size="pageSize"
            item-name="sản phẩm"
            @change="onPageChange"
          />
        </section>
      </div>
    </main>

    <!-- ═══ MOBILE FILTER BOTTOM SHEET ═══ -->
    <Teleport to="body">
      <Transition name="filter-sheet">
        <div v-if="mobileFiltersOpen" class="fixed inset-0 z-[70] lg:hidden" role="dialog" aria-modal="true" :aria-label="t('products.filter')" @click.self="mobileFiltersOpen = false">
          <div class="absolute inset-0 bg-text-main/45 backdrop-blur-[2px]" @click="mobileFiltersOpen = false"></div>
          <div class="absolute left-0 right-0 bottom-0 bg-surface-1 rounded-t-3xl shadow-[0_-16px_48px_rgba(26,26,26,0.18)] flex flex-col max-h-[90dvh]">
            <!-- Drag indicator -->
            <div class="pt-2.5 pb-1 flex justify-center shrink-0">
              <span class="w-10 h-1.5 rounded-full bg-outline-variant"></span>
            </div>
            <!-- Header -->
            <div class="px-5 pb-3 flex items-center justify-between shrink-0">
              <div>
                <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em] block">/FILTER</span>
                <span class="text-[15px] font-bold text-text-main uppercase tracking-[0.12em]">{{ t('products.filter') }}</span>
              </div>
              <button class="w-11 h-11 -mr-1 rounded-full bg-surface-vlm text-text-main flex items-center justify-center active:scale-95 transition-transform" :aria-label="t('common.close')" @click="mobileFiltersOpen = false">
                <i class="fa text-[17px] fa-xmark"></i>
              </button>
            </div>
            <!-- Scrollable body -->
            <div class="flex-1 overflow-y-auto px-5 pt-2 pb-4 overscroll-contain">
              <ProductFilterPanel
                v-model:search-query="searchQuery"
                v-model:selected-categories="selectedCategories"
                v-model:selected-applications="selectedApplications"
                v-model:selected-strength="selectedStrength"
                :categories="categories"
                :applications="applications"
                :strength-buckets="strengthBuckets"
                :categories-error="categoriesError"
                :applications-error="applicationsError"
                @retry-categories="loadCategories"
                @retry-applications="loadApplications"
              />
            </div>
            <!-- Sticky footer actions -->
            <div class="shrink-0 border-t border-outline-variant bg-surface-1 px-5 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom,0px))] flex gap-3">
              <button
                v-if="activeFilterCount > 0"
                class="shrink-0 w-[38%] bg-canvas text-text-main border border-outline-variant py-3.5 text-[12px] font-bold uppercase tracking-[0.15em] rounded-xl"
                @click="clearFilters"
              >
                {{ t('products.clear') }}
              </button>
              <button
                class="flex-1 btn-primary py-3.5 text-[12px] font-bold uppercase tracking-[0.15em] rounded-xl active:scale-[0.98] transition-transform"
                @click="mobileFiltersOpen = false"
              >
                {{ t('products.apply') }} ({{ filteredProducts.length }})
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <CTABanner />
  </div>
</template>

<style scoped>
.filter-sheet-enter-active,
.filter-sheet-leave-active {
  transition: opacity 0.28s ease;
}
.filter-sheet-enter-active > div:last-child,
.filter-sheet-leave-active > div:last-child {
  transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1);
}
.filter-sheet-enter-from,
.filter-sheet-leave-to {
  opacity: 0;
}
.filter-sheet-enter-from > div:last-child,
.filter-sheet-leave-to > div:last-child {
  transform: translateY(100%);
}
</style>
