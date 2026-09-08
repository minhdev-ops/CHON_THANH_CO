<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import type { NewsItem } from '../types'
import PageHeader from '../components/PageHeader.vue'
import ErrorState from '../components/ErrorState.vue'
import CTABanner from '../components/CTABanner.vue'
import Pagination from '../components/Pagination.vue'
import LazyImage from '../components/LazyImage.vue'
import { t, locale } from '../i18n'
import { fallbackNews } from '../types/fallback'

const news = ref<NewsItem[]>([...fallbackNews])
const newsLoading = ref(false)
const loadError = ref<string | null>(null)

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

const currentPage = ref(1)
const pageSize = ref(5)

const loadNews = async () => {
  newsLoading.value = true
  loadError.value = null
  try {
    const res = await api.news({ limit: 50 })
    if (res.data && res.data.length > 0) {
      news.value = res.data
    } else {
      news.value = [...fallbackNews]
    }
  } catch (e) {
    news.value = [...fallbackNews]
    loadError.value = null
  } finally {
    newsLoading.value = false
  }
}

const filteredNews = computed(() => {
  let list = news.value.length ? [...news.value] : [...fallbackNews]
  if (selectedCategory.value) list = list.filter((n) => n.category?.slug === selectedCategory.value)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter((n) => n.title.toLowerCase().includes(q) || n.excerpt.toLowerCase().includes(q))
  }
  return list
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredNews.value.length / pageSize.value)))

const paginatedNews = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredNews.value.slice(start, start + pageSize.value)
})

const onPageChange = (page: number) => {
  currentPage.value = page
  const el = document.getElementById('news-main-section')
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  } else {
    window.scrollTo({ top: 250, behavior: 'smooth' })
  }
}

watch([selectedCategory, searchQuery], () => {
  currentPage.value = 1
})

onMounted(async () => {
  await loadNews()
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
  return categories.value?.find((c) => c.slug === selectedCategory.value)?.name || t('news.allArticles')
})

// Standard layout logic
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
        <div class="flex flex-col lg:flex-row items-stretch lg:items-end justify-between gap-6 pb-0 border-b border-outline-variant">
          <div class="flex items-center gap-6 overflow-x-auto no-scrollbar whitespace-nowrap">
            <button
              class="pb-4 text-[14px] font-bold transition-colors relative"
              :class="selectedCategory === null ? 'text-primary' : 'text-text-secondary hover:text-text-main'"
              @click="selectedCategory = null"
            >
              {{ t('news.all') }}
              <div v-if="selectedCategory === null" class="absolute bottom-0 left-0 w-full h-[3px] bg-primary rounded-t-sm"></div>
            </button>
            <button v-for="cat in categories" :key="cat.slug"
              class="pb-4 text-[14px] font-bold transition-colors relative"
              :class="selectedCategory === cat.slug ? 'text-primary' : 'text-text-secondary hover:text-text-main'"
              @click="selectedCategory = cat.slug"
            >
              {{ cat.name }}
              <div v-if="selectedCategory === cat.slug" class="absolute bottom-0 left-0 w-full h-[3px] bg-primary rounded-t-sm"></div>
            </button>
          </div>
          <div class="relative w-full lg:w-72 mb-4 lg:mb-3">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-muted text-[18px]">search</span>
            <input
              v-model="searchQuery" type="text" :placeholder="t('news.searchPlaceholder')"
              class="bg-surface-vlm border border-outline-variant/60 shadow-inner pl-10 pr-4 py-2.5 text-[13px] text-text-main outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all w-full rounded-md placeholder:text-text-muted/60"
            >
          </div>
        </div>
      </div>

      <div class="grid grid-cols-12 gap-6 lg:gap-10">
        <div id="news-main-section" class="col-span-12 lg:col-span-9 scroll-mt-32">
          <!-- ═══ LOADING ═══ -->
          <div v-if="newsLoading" class="grid grid-cols-12 gap-4">
            <div v-for="i in 4" :key="i" class="col-span-12 sm:col-span-6 h-80 bg-canvas animate-shimmer border border-outline-variant"></div>
          </div>

          <!-- ═══ ERROR ═══ -->
          <div v-else-if="loadError && !filteredNews.length" class="bg-canvas border border-outline-variant p-8">
            <ErrorState :message="loadError" @retry="loadNews" />
          </div>

          <!-- ═══ PROFESSIONAL LIST VIEW ═══ -->
          <div v-else-if="filteredNews.length" class="flex flex-col gap-8">
            <router-link
              v-for="(n, i) in paginatedNews" :key="n.slug" :to="`/news/${n.slug}`"
              class="group flex flex-col md:flex-row bg-white border border-outline-variant/60 rounded-sm shadow-sm hover:shadow-md transition-all duration-300 reveal overflow-hidden"
              :class="[`reveal-delay-${(i%5)+1}`]"
            >
              <LazyImage
                :src="n.image"
                :alt="n.title"
                fallback-src="/images/products/industrial-1.jpg"
                aspect-ratio="aspect-[4/3]"
                container-class="w-full md:w-[35%] shrink-0 md:border-r border-outline-variant/60"
                image-class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
              />
              <div class="p-6 md:p-8 flex flex-col flex-grow justify-center">
                <div class="flex items-center gap-3 mb-4">
                  <span class="text-primary text-[11px] font-bold uppercase tracking-widest">{{ n.category?.name || 'Tin tức' }}</span>
                  <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
                  <span class="text-[12px] font-medium text-text-muted flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px]">calendar_today</span>{{ new Date(n.published_at).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</span>
                </div>
                <h3 class="font-bold text-[22px] md:text-[24px] text-text-main mb-4 group-hover:text-primary transition-colors duration-300 line-clamp-2 leading-snug">{{ n.title }}</h3>
                <p class="text-text-secondary text-[15px] line-clamp-3 leading-relaxed mb-6">{{ n.excerpt }}</p>
                
                <div class="mt-auto">
                  <span class="inline-flex items-center gap-1.5 font-bold text-[13px] text-primary group-hover:text-primary-deep transition-colors duration-300 uppercase tracking-widest relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-px after:bg-primary group-hover:after:w-full after:transition-all after:duration-300">
                    {{ t('news.readMore') }}
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                  </span>
                </div>
              </div>
            </router-link>
          </div>

          <!-- ═══ EMPTY ═══ -->
          <div v-else class="py-24 flex flex-col items-center justify-center text-center bg-surface-bright border border-outline-variant rounded-sm shadow-sm">
            <div class="w-16 h-16 rounded-sm bg-surface-vlm border border-outline-variant/60 flex items-center justify-center mb-6">
              <span class="material-symbols-outlined text-[32px] text-text-muted">article</span>
            </div>
            <h3 class="text-text-main font-bold text-[18px] mb-2 tracking-wide">KHÔNG TÌM THẤY BÀI VIẾT</h3>
            <p class="text-text-secondary font-medium text-[15px]">{{ t('news.empty') }}</p>
          </div>

          <!-- ═══ PAGINATION ═══ -->
          <Pagination
            v-if="!newsLoading && filteredNews.length > 0"
            v-model:current-page="currentPage"
            :total-pages="totalPages"
            :total-items="filteredNews.length"
            :page-size="pageSize"
            item-name="bài viết"
            @change="onPageChange"
          />
        </div>

        <!-- ═══ SIDEBAR (newspaper sidebar) ═══ -->
        <aside class="col-span-12 lg:col-span-3">
          <div class="lg:sticky lg:top-32 space-y-6">
            <!-- Recent -->
            <section class="bg-white border border-outline-variant shadow-sm rounded-md overflow-hidden reveal">
              <div class="bg-surface-vlm/30 border-b border-outline-variant/60 px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-[12px] text-text-main tracking-widest uppercase">{{ t('news.latest') }}</span>
                </div>
              </div>
              <div class="p-5 divide-y divide-outline-variant/60">
                <router-link v-for="(n, i) in recentNews" :key="n.slug" :to="`/news/${n.slug}`" class="flex items-start gap-3 group py-3.5 first:pt-0 last:pb-0">
                  <div class="font-mono text-[14px] font-bold text-outline-variant group-hover:text-primary transition-colors duration-200 shrink-0 tabular-nums pt-0.5">{{ paddedIndex(i) }}</div>
                  <div class="flex-grow min-w-0">
                    <h4 class="font-medium text-text-main text-[13px] leading-snug line-clamp-2 group-hover:text-primary transition-colors duration-300 mb-1.5">{{ n.title }}</h4>
                    <span class="text-[10px] text-text-muted font-medium">{{ formatDate(n.published_at) }}</span>
                  </div>
                </router-link>
              </div>
            </section>

            <!-- Newsletter / Subscribe -->
            <section class="relative overflow-hidden bg-white border border-outline-variant p-6 shadow-sm rounded-md reveal">
              <div class="relative">
                <div class="font-bold text-[12px] text-text-main tracking-widest uppercase mb-4">{{ t('news.subscribeTitle') }}</div>
                <p class="text-text-secondary text-[13px] mb-6 leading-relaxed">{{ t('news.subscribeText') }}</p>
                <router-link to="/contact" class="group flex items-center justify-center gap-2 bg-primary text-white px-5 py-3 text-[12px] font-bold tracking-wider uppercase hover:bg-primary-hover shadow-sm hover:shadow transition-all rounded-md w-full">
                  <span>{{ t('news.contactUs') }}</span>
                </router-link>
              </div>
            </section>

            <!-- Tag cloud -->
            <section class="bg-white border border-outline-variant p-6 shadow-sm rounded-md reveal">
              <div class="font-bold text-[12px] text-text-main tracking-widest uppercase mb-5">TAGS</div>
              <div class="flex flex-wrap gap-2">
                <span v-for="tag in ['Geotextile', 'Geogrid', 'HDPE', 'GCL', 'Thi công', 'Báo giá', 'ISO 9001']" :key="tag"
                  class="text-[11px] font-medium text-text-secondary bg-surface-vlm border border-outline-variant/60 px-3 py-1.5 hover:bg-primary hover:text-white hover:border-primary cursor-pointer transition-colors duration-200 rounded-sm">
                  {{ tag }}
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
