<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import type { NewsItem } from '../types'
import PageHeader from '../components/PageHeader.vue'
import ErrorState from '../components/ErrorState.vue'
import CTABanner from '../components/CTABanner.vue'
import { t, locale } from '../i18n'
import { fallbackNews } from '../types/fallback'

const news = ref<NewsItem[]>(fallbackNews)
const newsLoading = ref(false)
const loadingMore = ref(false)
const loadError = ref<string | null>(null)
const nextCursor = ref<number | null>(null)
const hasMore = computed(() => nextCursor.value !== null && nextCursor.value !== undefined)

const { data: categories, error: categoriesError, load: loadCategories } = useApiData(
  () => api.newsCategories(),
  () => [
    { slug: 'tin-cong-ty', name: 'Tin công ty' },
    { slug: 'du-an', name: 'Dự án' },
    { slug: 'su-kien', name: 'Sự kiện' },
    { slug: 'thi-truong', name: 'Thị trường' },
    { slug: 'ky-thuat', name: 'Kỹ thuật' },
  ]
)

const selectedCategory = ref<string | null>(null)
const searchQuery = ref('')

const allNews = ref<NewsItem[]>([])

const loadAllFallback = async () => {
  try {
    let cursor: number | null | undefined
    do {
      const page = await api.news({ cursor, limit: 50 })
      allNews.value.push(...page.data)
      cursor = page.next_cursor
    } while (cursor != null)
  } catch {
    allNews.value = [...fallbackNews]
  }
}

const loadMore = async (reset = false) => {
  if (reset) {
    nextCursor.value = null
    loadError.value = null
    newsLoading.value = true
  } else {
    loadingMore.value = true
  }
  try {
    const res = await api.news({ limit: 50, cursor: nextCursor.value ?? undefined, category: selectedCategory.value ?? undefined })
    let list = res.data ?? []
    if (reset) {
      news.value = list.length ? list : filteredFallback.value
    } else {
      news.value.push(...list)
    }
    nextCursor.value = res.next_cursor
  } catch (e) {
    loadError.value = e instanceof Error ? e.message : t('common.errorGeneric')
    if (reset) news.value = filteredFallback.value
  } finally {
    newsLoading.value = false
    loadingMore.value = false
  }
}

const filteredFallback = computed(() => {
  let list = [...fallbackNews]
  if (selectedCategory.value) list = list.filter((n) => n.category?.slug === selectedCategory.value)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter((n) => n.title.toLowerCase().includes(q) || n.excerpt.toLowerCase().includes(q))
  }
  return list
})

watch(selectedCategory, () => loadMore(true))
watch(searchQuery, () => loadMore(true))

onMounted(async () => {
  loadAllFallback()
  await loadMore(true)
  await nextTick()
  window.scrollTo({ top: 0, behavior: 'instant' as ScrollBehavior })
})

const formatDate = (iso?: string) => {
  if (!iso) return ''
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleDateString(locale.value === 'en' ? 'en-US' : 'vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const formatDateLong = (iso?: string) => {
  if (!iso) return ''
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleDateString(locale.value === 'en' ? 'en-US' : 'vi-VN', { day: '2-digit', month: 'long', year: 'numeric' })
}

const recentNews = computed(() => {
  return [...fallbackNews].sort((a, b) => new Date(b.published_at).getTime() - new Date(a.published_at).getTime()).slice(0, 3)
})

// Editorial layout removed in favor of standard grid

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.news') }
])

const paddedIndex = (i: number) => String(i + 1).padStart(2, '0')

const activeCategoryName = computed(() => {
  if (!selectedCategory.value) return t('news.allArticles')
  return categories.value.find((c) => c.slug === selectedCategory.value)?.name || t('news.allArticles')
})

// Bento layout for News: alternating patterns
// Pattern A: 1 full-width article (span 12)
// Pattern B: 2 half-width articles (span 6, 6)
const bentoRowEnd = [0, 2, 3, 5, 6, 8, 9, 11]
const bentoCol = (i: number) => {
  for (let r = 0; r < bentoRowEnd.length; r++) {
    if (i <= bentoRowEnd[r]) {
      const isARow = r % 2 === 0
      return isARow ? 'md:col-span-12' : 'md:col-span-6'
    }
  }
  return 'md:col-span-6'
}

const bentoAspect = (i: number) => {
  for (let r = 0; r < bentoRowEnd.length; r++) {
    if (i <= bentoRowEnd[r]) {
      const isARow = r % 2 === 0
      return isARow ? 'aspect-[21/9]' : 'aspect-[16/10]'
    }
  }
  return 'aspect-[16/10]'
}
</script>

<template>
  <div>
    <PageHeader :title="t('nav.news')" :breadcrumbs="breadcrumbs" />


    <main class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14 animate-fade-in-up">
      <!-- ═══ CATEGORY BAR (editorial filter) ═══ -->
      <div v-if="categoriesError" class="mb-10">
        <ErrorState :message="categoriesError" @retry="loadCategories" />
      </div>
      <div v-else class="mb-10 reveal">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 pb-5 border-b border-text-main">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em] mr-1 hidden sm:inline">/SECTION</span>
            <button
              class="px-4 py-2 text-[11px] font-bold uppercase tracking-[0.15em] border transition-all duration-300 rounded-none"
              :class="selectedCategory === null
                ? 'bg-primary text-white border-text-main'
                : 'bg-transparent text-text-secondary border-outline-variant hover:border-text-main hover:text-text-main'"
              @click="selectedCategory = null"
            >{{ t('news.all') }}</button>
            <button v-for="cat in categories" :key="cat.slug"
              class="px-4 py-2 text-[11px] font-bold uppercase tracking-[0.15em] border transition-all duration-300 rounded-none"
              :class="selectedCategory === cat.slug
                ? 'bg-primary text-white border-text-main'
                : 'bg-transparent text-text-secondary border-outline-variant hover:border-text-main hover:text-text-main'"
              @click="selectedCategory = cat.slug"
            >{{ cat.name }}</button>
          </div>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[16px]">search</span>
            <input
              v-model="searchQuery" type="text" :placeholder="t('news.searchPlaceholder')"
              class="bg-canvas border border-outline-variant pl-9 pr-3 py-2 text-[12px] text-text-main outline-none focus:border-text-main transition-colors w-full sm:w-64 rounded-none placeholder:text-text-muted/60"
            >
          </div>
        </div>
      </div>

      <div class="grid grid-cols-12 gap-6 lg:gap-10">
        <div class="col-span-12 lg:col-span-9">
          <!-- ═══ LOADING ═══ -->
          <div v-if="newsLoading" class="grid grid-cols-12 gap-4">
            <div v-for="i in 4" :key="i" class="col-span-12 sm:col-span-6 h-80 bg-canvas animate-shimmer border border-outline-variant"></div>
          </div>

          <!-- ═══ ERROR ═══ -->
          <div v-else-if="loadError && !news.length" class="bg-canvas border border-outline-variant p-8">
            <ErrorState :message="loadError" @retry="loadMore(true)" />
          </div>

          <!-- ═══ BENTO GRID ═══ -->
          <div v-else-if="news.length" class="grid grid-cols-12 gap-6 lg:gap-8 stagger-grid">
            <router-link
              v-for="(n, i) in news" :key="n.slug" :to="`/news/${n.slug}`"
              class="col-span-12 bg-surface-bright border border-outline-variant rounded-3xl overflow-hidden group hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 flex flex-col reveal"
              :class="[bentoCol(i), `reveal-delay-${(i%2)+1}`]"
            >
              <div class="overflow-hidden bg-surface-vlm shrink-0" :class="bentoAspect(i)">
                <img :src="n.image" :alt="n.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
              </div>
              <div class="p-7 md:p-8 flex flex-col flex-grow">
                <span class="text-[11px] font-bold text-primary uppercase tracking-[0.2em] mb-3 inline-block">{{ n.category?.name || 'Tin tức' }} — {{ new Date(n.published_at).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</span>
                <h3 class="font-extrabold text-[19px] text-text-main mb-3 group-hover:text-primary transition-colors duration-300 line-clamp-2 leading-snug">{{ n.title }}</h3>
                <p class="text-text-secondary text-[15px] line-clamp-3 leading-relaxed mb-6">{{ n.excerpt }}</p>
                
                <div class="mt-auto flex items-center justify-between pt-5 border-t border-outline-variant/60">
                  <span class="inline-flex items-center gap-2 font-bold text-[12px] text-primary group-hover:text-primary-deep uppercase tracking-[0.15em] transition-colors duration-300">
                    {{ t('news.readMore') }}
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                  </span>
                </div>
              </div>
            </router-link>
          </div>

          <!-- ═══ EMPTY ═══ -->
          <div v-else class="py-20 text-center bg-canvas border border-outline-variant">
            <div class="font-sans text-[80px] font-bold text-text-muted/30 leading-none mb-4 select-none">∅</div>
            <p class="text-text-secondary font-medium text-[12px] uppercase tracking-[0.2em]">{{ t('news.empty') }}</p>
          </div>

          <div v-if="hasMore && !newsLoading" class="mt-12 flex justify-center">
            <button type="button" class="group inline-flex items-center gap-3 bg-transparent border border-text-main text-text-main px-8 py-3.5 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-text-main hover:text-canvas transition-all duration-300 rounded-none" :disabled="loadingMore" @click="loadMore()">
              <span>{{ loadingMore ? t('common.loading') : t('common.loadMore') }}</span>
              <span v-if="!loadingMore" class="material-symbols-outlined text-[16px] group-hover:translate-y-0.5 transition-transform duration-300">expand_more</span>
            </button>
          </div>
        </div>

        <!-- ═══ SIDEBAR (newspaper sidebar) ═══ -->
        <aside class="col-span-12 lg:col-span-3">
          <div class="lg:sticky lg:top-28 space-y-6">
            <!-- Recent -->
            <section class="bg-surface-glass backdrop-blur-xl border border-outline-variant shadow-sm rounded-[24px] overflow-hidden reveal">
              <div class="bg-primary text-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="font-mono text-[10px] font-bold text-primary tracking-[0.25em]">/RECENT</span>
                  <span class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase opacity-70">{{ t('news.latest') }}</span>
                </div>
                <span class="material-symbols-outlined text-[14px] text-primary">schedule</span>
              </div>
              <div class="p-6 divide-y divide-outline-variant/60">
                <router-link v-for="(n, i) in recentNews" :key="n.slug" :to="`/news/${n.slug}`" class="flex items-start gap-3 group py-4 first:pt-0 last:pb-0">
                  <div class="font-mono text-[20px] font-bold text-text-muted/60 group-hover:text-primary transition-colors duration-200 shrink-0 tabular-nums">{{ paddedIndex(i) }}</div>
                  <div class="flex-grow min-w-0">
                    <h4 class="font-bold text-text-main text-[13px] leading-snug line-clamp-2 group-hover:text-primary transition-colors duration-300 mb-1.5">{{ n.title }}</h4>
                    <span class="font-mono text-[9px] text-text-muted font-bold uppercase tracking-[0.2em]">{{ formatDate(n.published_at) }}</span>
                  </div>
                </router-link>
              </div>
            </section>

            <!-- Newsletter / Subscribe -->
            <section class="relative overflow-hidden bg-primary text-white p-8 shadow-sm rounded-[24px] reveal">
              <div class="absolute top-0 right-0 w-40 h-40 bg-primary/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl pointer-events-none"></div>
              <div class="relative">
                <div class="font-mono text-[10px] font-bold text-primary tracking-[0.25em] mb-4">/SUBSCRIBE</div>
                <span class="material-symbols-outlined text-primary text-[36px] mb-4">campaign</span>
                <h3 class="font-sans text-[20px] font-bold mb-3 leading-tight">{{ t('news.subscribeTitle') }}</h3>
                <p class="text-canvas/70 text-[13px] mb-6 leading-[1.7]">{{ t('news.subscribeText') }}</p>
                <router-link to="/contact" class="group inline-flex items-center justify-center gap-2 bg-primary text-canvas px-6 py-3.5 text-[11px] font-bold tracking-[0.2em] uppercase hover:bg-primary-hover transition-colors rounded-full w-full">
                  <span class="material-symbols-outlined text-[16px]">mail</span>
                  <span>{{ t('news.contactUs') }}</span>
                  <span class="material-symbols-outlined text-[14px] ml-auto group-hover:translate-x-1 transition-transform duration-300">arrow_forward</span>
                </router-link>
              </div>
            </section>

            <!-- Tag cloud -->
            <section class="bg-surface-glass backdrop-blur-xl border border-outline-variant p-6 shadow-sm rounded-[24px] reveal">
              <div class="flex items-center gap-2 mb-5">
                <span class="font-mono text-[10px] font-bold text-primary tracking-[0.25em]">/TAGS</span>
                <span class="w-full h-px bg-outline-variant"></span>
              </div>
              <div class="flex flex-wrap gap-2">
                <span v-for="tag in ['Geotextile', 'Geogrid', 'HDPE', 'GCL', 'Thi công', 'Báo giá', 'ISO 9001', 'Hạ tầng', 'Môi trường']" :key="tag"
                  class="font-mono text-[10px] font-bold text-text-secondary bg-canvas border border-outline-variant px-3 py-1.5 tracking-[0.1em] uppercase hover:bg-text-main hover:text-canvas hover:border-text-main cursor-pointer transition-colors duration-200 rounded-full">
                  #{{ tag }}
                </span>
              </div>
            </section>
          </div>
        </aside>
      </div>
    </main>

    <CTABanner />
  </div>
</template>
