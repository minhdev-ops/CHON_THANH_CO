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

// Editorial layout: HERO, FEATURED, LIST
const heroArticle = computed(() => news.value[0])
const featuredArticle = computed(() => news.value[1])
const listArticles = computed(() => news.value.slice(2))

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.news') }
])

const paddedIndex = (i: number) => String(i + 1).padStart(2, '0')

const activeCategoryName = computed(() => {
  if (!selectedCategory.value) return t('news.allArticles')
  return categories.value.find((c) => c.slug === selectedCategory.value)?.name || t('news.allArticles')
})
</script>

<template>
  <div>
    <PageHeader :title="t('nav.news')" :breadcrumbs="breadcrumbs" />

    <!-- ═══ EDITORIAL MASTHEAD ═══ -->
    <section class="relative border-b border-outline-variant bg-canvas overflow-hidden">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="grid grid-cols-12 gap-x-6 gap-y-8 items-end">
          <div class="col-span-12 md:col-span-8 reveal">
            <div class="flex items-center gap-3 mb-5">
              <span class="font-mono text-[10px] font-bold text-canvas bg-text-main px-2.5 py-1 tracking-[0.25em]">VOL. 2026</span>
              <span class="w-8 h-px bg-text-main/30"></span>
              <span class="font-mono text-[10px] font-bold text-text-muted tracking-[0.2em] uppercase">{{ activeCategoryName }}</span>
            </div>
            <h1 class="font-sans text-[44px] md:text-[68px] lg:text-[80px] text-text-main font-bold leading-[0.92] tracking-[-0.035em]">
              {{ t('news.titlePart1') }}<br>
              <span class="italic font-normal text-primary">&amp; {{ t('news.titlePart2') }}</span>
            </h1>
          </div>

          <div class="col-span-12 md:col-span-4 reveal reveal-delay-1 md:border-l md:border-text-main/15 md:pl-8">
            <div class="font-mono text-[10px] font-bold text-primary tracking-[0.25em] mb-3">/EDITORIAL</div>
            <p class="text-text-secondary text-[14px] md:text-[15px] leading-[1.7]">
              {{ t('news.subtitle') }}
            </p>
            <div class="mt-6 pt-5 border-t border-outline-variant grid grid-cols-3 gap-3">
              <div>
                <div class="font-sans text-[28px] font-bold text-text-main leading-none tabular-nums">{{ String(news.length).padStart(2, '0') }}</div>
                <div class="font-mono text-[9px] text-text-muted font-bold tracking-[0.2em] uppercase mt-1.5">{{ t('news.statArticles') }}</div>
              </div>
              <div>
                <div class="font-sans text-[28px] font-bold text-primary leading-none tabular-nums">{{ String(categories.length).padStart(2, '0') }}</div>
                <div class="font-mono text-[9px] text-text-muted font-bold tracking-[0.2em] uppercase mt-1.5">{{ t('news.statCategories') }}</div>
              </div>
              <div>
                <div class="font-sans text-[28px] font-bold text-olive leading-none tabular-nums">12+</div>
                <div class="font-mono text-[9px] text-text-muted font-bold tracking-[0.2em] uppercase mt-1.5">{{ t('news.statMonths') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <main class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14">
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
                ? 'bg-text-main text-canvas border-text-main'
                : 'bg-transparent text-text-secondary border-outline-variant hover:border-text-main hover:text-text-main'"
              @click="selectedCategory = null"
            >{{ t('news.all') }}</button>
            <button v-for="cat in categories" :key="cat.slug"
              class="px-4 py-2 text-[11px] font-bold uppercase tracking-[0.15em] border transition-all duration-300 rounded-none"
              :class="selectedCategory === cat.slug
                ? 'bg-text-main text-canvas border-text-main'
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

          <!-- ═══ EDITORIAL LAYOUT ═══ -->
          <div v-else-if="news.length" class="space-y-4">
            <!-- HERO ARTICLE (full-width cover) -->
            <article v-if="heroArticle" class="group relative bg-text-main text-canvas overflow-hidden border border-text-main reveal">
              <div class="grid grid-cols-1 lg:grid-cols-12">
                <!-- Image -->
                <div class="lg:col-span-7 relative aspect-[16/10] lg:aspect-auto lg:min-h-[480px] overflow-hidden">
                  <img
                    :src="heroArticle.image" :alt="heroArticle.title"
                    class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.04] transition-transform duration-[1400ms] ease-out"
                    loading="lazy"
                  >
                  <div class="absolute inset-0 bg-gradient-to-r from-text-main/20 via-transparent to-text-main/80"></div>
                  <div class="absolute top-5 left-5 inline-flex items-center gap-1.5 bg-primary text-canvas px-3 py-1.5 text-[10px] font-mono font-bold tracking-[0.2em] uppercase">
                    <span class="material-symbols-outlined text-[12px] fill">auto_awesome</span>
                    <span>COVER STORY</span>
                  </div>
                  <div class="absolute bottom-5 left-5 font-sans text-[80px] md:text-[120px] font-bold text-canvas/15 leading-none select-none tabular-nums">{{ paddedIndex(0) }}</div>
                </div>

                <!-- Content -->
                <div class="lg:col-span-5 px-7 py-8 lg:px-10 lg:py-12 flex flex-col justify-between relative">
                  <div>
                    <div class="flex items-center gap-3 mb-4">
                      <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em] uppercase">/ {{ heroArticle.category?.name || t('news.category') }}</span>
                      <span class="w-4 h-px bg-canvas/30"></span>
                      <span class="font-mono text-[10px] font-bold text-canvas/50 tracking-[0.2em] uppercase">{{ formatDateLong(heroArticle.published_at) }}</span>
                    </div>
                    <h2 class="font-sans text-[26px] md:text-[32px] lg:text-[36px] font-bold leading-[1.08] tracking-[-0.025em] mb-5 group-hover:text-primary transition-colors duration-500">
                      {{ heroArticle.title }}
                    </h2>
                    <p class="text-canvas/70 text-[14px] leading-[1.7] line-clamp-4">{{ heroArticle.excerpt }}</p>
                  </div>
                  <div class="mt-8 pt-6 border-t border-canvas/15">
                    <router-link
                      :to="`/news/${heroArticle.slug}`"
                      class="inline-flex items-center gap-2 text-[11px] font-bold text-primary tracking-[0.2em] uppercase group-hover:gap-3 transition-all duration-300"
                    >
                      <span>{{ t('news.readMore') }}</span>
                      <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </router-link>
                  </div>
                </div>
              </div>
            </article>

            <!-- FEATURED ARTICLE (offset/asymmetric) -->
            <article v-if="featuredArticle" class="group bg-canvas border border-outline-variant hover:border-text-main transition-colors duration-500 reveal">
              <div class="grid grid-cols-1 md:grid-cols-12 gap-0">
                <div class="md:col-span-5 relative aspect-[4/3] md:aspect-auto md:min-h-[320px] overflow-hidden">
                  <img
                    :src="featuredArticle.image" :alt="featuredArticle.title"
                    class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-[1000ms] ease-out"
                    loading="lazy"
                  >
                  <div class="absolute top-4 left-4 inline-flex items-center gap-1.5 bg-canvas text-text-main px-2.5 py-1 text-[9px] font-mono font-bold tracking-[0.2em] uppercase border border-text-main">
                    <span>/02 — EDITOR'S PICK</span>
                  </div>
                  <div class="absolute bottom-4 left-4 font-sans text-[64px] font-bold text-canvas/15 leading-none select-none tabular-nums">{{ paddedIndex(1) }}</div>
                </div>
                <div class="md:col-span-7 p-6 md:p-10 flex flex-col justify-center">
                  <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary-deep px-2.5 py-1 text-[10px] font-mono font-bold tracking-[0.2em] uppercase">
                      <span class="w-1 h-1 bg-primary"></span>
                      <span>{{ featuredArticle.category?.name || t('news.category') }}</span>
                    </span>
                    <span class="font-mono text-[10px] text-text-muted font-bold tracking-[0.2em] uppercase">{{ formatDate(featuredArticle.published_at) }}</span>
                  </div>
                  <h3 class="font-sans text-[22px] md:text-[26px] font-bold text-text-main leading-[1.15] tracking-[-0.02em] mb-4 group-hover:text-primary transition-colors duration-500">
                    {{ featuredArticle.title }}
                  </h3>
                  <p class="text-text-secondary text-[14px] leading-[1.7] line-clamp-3 mb-5">{{ featuredArticle.excerpt }}</p>
                  <router-link
                    :to="`/news/${featuredArticle.slug}`"
                    class="inline-flex items-center gap-2 text-[11px] font-bold text-primary tracking-[0.2em] uppercase group-hover:gap-3 transition-all duration-300 self-start"
                  >
                    {{ t('news.readMore') }}
                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                  </router-link>
                </div>
              </div>
            </article>

            <!-- LIST ARTICLES (newspaper table) -->
            <div v-if="listArticles.length" class="bg-canvas border border-outline-variant">
              <!-- Sticky table header -->
              <div class="grid grid-cols-12 gap-3 px-4 md:px-6 py-3 bg-text-main text-canvas font-mono text-[10px] font-bold tracking-[0.2em] uppercase sticky top-28 z-20">
                <div class="col-span-1 hidden md:block">№</div>
                <div class="col-span-12 md:col-span-7">{{ t('news.tableTitle') }}</div>
                <div class="col-span-6 md:col-span-2">{{ t('news.tableCategory') }}</div>
                <div class="col-span-3 md:col-span-1">{{ t('news.tableDate') }}</div>
                <div class="col-span-3 md:col-span-1 text-right">→</div>
              </div>
              <div class="divide-y divide-outline-variant">
                <router-link
                  v-for="(item, i) in listArticles" :key="item.slug"
                  :to="`/news/${item.slug}`"
                  class="grid grid-cols-12 gap-3 md:gap-4 items-center px-4 md:px-6 py-4 md:py-5 hover:bg-surface-1 transition-colors duration-200 group reveal"
                >
                  <div class="col-span-1 hidden md:block font-mono text-[18px] font-bold text-text-muted group-hover:text-primary transition-colors duration-200 tabular-nums">{{ paddedIndex(i + 2) }}</div>
                  <div class="col-span-12 md:col-span-7 flex items-start gap-4">
                    <div class="w-20 h-16 md:w-24 md:h-20 shrink-0 overflow-hidden bg-canvas border border-outline-variant">
                      <img :src="item.image" :alt="item.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="flex-grow min-w-0">
                      <h4 class="font-bold text-text-main text-[14px] md:text-[15px] leading-snug line-clamp-2 group-hover:text-primary transition-colors duration-300 mb-1.5">{{ item.title }}</h4>
                      <p class="text-[12px] text-text-muted line-clamp-1 hidden md:block">{{ item.excerpt }}</p>
                    </div>
                  </div>
                  <div class="col-span-6 md:col-span-2">
                    <span class="inline-flex items-center gap-1.5 font-mono text-[10px] text-text-secondary font-bold tracking-[0.2em] uppercase">
                      <span class="w-1 h-1 bg-primary"></span>
                      <span>{{ item.category?.name || t('news.category') }}</span>
                    </span>
                  </div>
                  <div class="col-span-3 md:col-span-1">
                    <span class="font-mono text-[10px] text-text-muted font-bold tracking-[0.15em] uppercase tabular-nums">{{ formatDate(item.published_at) }}</span>
                  </div>
                  <div class="col-span-3 md:col-span-1 text-right">
                    <span class="inline-flex items-center justify-center w-8 h-8 border border-outline-variant group-hover:bg-text-main group-hover:text-canvas group-hover:border-text-main transition-all duration-200">
                      <span class="material-symbols-outlined text-[14px] group-hover:translate-x-0.5 transition-transform duration-200">arrow_forward</span>
                    </span>
                  </div>
                </router-link>
              </div>
            </div>
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
        <aside class="col-span-12 lg:col-span-3 space-y-6">
          <!-- Recent -->
          <section class="bg-canvas border border-outline-variant reveal">
            <div class="bg-text-main text-canvas px-5 py-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-primary tracking-[0.25em]">/RECENT</span>
                <span class="font-mono text-[10px] font-bold tracking-[0.2em] uppercase opacity-70">{{ t('news.latest') }}</span>
              </div>
              <span class="material-symbols-outlined text-[14px] text-primary">schedule</span>
            </div>
            <div class="p-5 divide-y divide-outline-variant">
              <router-link v-for="(n, i) in recentNews" :key="n.slug" :to="`/news/${n.slug}`" class="flex items-start gap-3 group py-3 first:pt-0 last:pb-0">
                <div class="font-mono text-[20px] font-bold text-text-muted/60 group-hover:text-primary transition-colors duration-200 shrink-0 tabular-nums">{{ paddedIndex(i) }}</div>
                <div class="flex-grow min-w-0">
                  <h4 class="font-bold text-text-main text-[12px] leading-snug line-clamp-2 group-hover:text-primary transition-colors duration-300 mb-1">{{ n.title }}</h4>
                  <span class="font-mono text-[9px] text-text-muted font-bold uppercase tracking-[0.2em]">{{ formatDate(n.published_at) }}</span>
                </div>
              </router-link>
            </div>
          </section>

          <!-- Newsletter / Subscribe -->
          <section class="relative overflow-hidden bg-text-main text-canvas p-6 reveal">
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl pointer-events-none"></div>
            <div class="relative">
              <div class="font-mono text-[10px] font-bold text-primary tracking-[0.25em] mb-3">/SUBSCRIBE</div>
              <span class="material-symbols-outlined text-primary text-[32px] mb-3">campaign</span>
              <h3 class="font-sans text-[18px] font-bold mb-2 leading-tight">{{ t('news.subscribeTitle') }}</h3>
              <p class="text-canvas/70 text-[12px] mb-5 leading-[1.7]">{{ t('news.subscribeText') }}</p>
              <router-link to="/contact" class="group inline-flex items-center justify-center gap-2 bg-primary text-canvas px-5 py-3 text-[11px] font-bold tracking-[0.2em] uppercase hover:bg-primary-hover transition-colors w-full">
                <span class="material-symbols-outlined text-[16px]">mail</span>
                <span>{{ t('news.contactUs') }}</span>
                <span class="material-symbols-outlined text-[14px] ml-auto group-hover:translate-x-1 transition-transform duration-300">arrow_forward</span>
              </router-link>
            </div>
          </section>

          <!-- Tag cloud -->
          <section class="bg-canvas border border-outline-variant p-5 reveal">
            <div class="flex items-center gap-2 mb-4">
              <span class="font-mono text-[10px] font-bold text-primary tracking-[0.25em]">/TAGS</span>
              <span class="w-full h-px bg-outline-variant"></span>
            </div>
            <div class="flex flex-wrap gap-1.5">
              <span v-for="tag in ['Geotextile', 'Geogrid', 'HDPE', 'GCL', 'Thi công', 'Báo giá', 'ISO 9001', 'Hạ tầng', 'Môi trường']" :key="tag"
                class="font-mono text-[10px] font-bold text-text-secondary bg-surface-1 border border-outline-variant px-2.5 py-1.5 tracking-[0.1em] uppercase hover:bg-text-main hover:text-canvas hover:border-text-main cursor-pointer transition-colors duration-200">
                #{{ tag }}
              </span>
            </div>
          </section>
        </aside>
      </div>
    </main>

    <CTABanner />
  </div>
</template>
