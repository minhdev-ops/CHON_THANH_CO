<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import type { NewsItem } from '../types'
import PageHeader from '../components/PageHeader.vue'
import LazyImage from '../components/LazyImage.vue'
import { t, locale } from '../i18n'
import { fallbackNews } from '../types/fallback'
import { buildContactLink } from '../utils/contactLink'

const route = useRoute()
const item = ref<NewsItem | null>(null)
const loading = ref(true)
const notFound = ref(false)
let loadSeq = 0

const load = async (slug: string) => {
  const requestId = ++loadSeq
  loading.value = true
  notFound.value = false
  item.value = null
  try {
    const res = await api.newsItem(slug)
    if (requestId !== loadSeq) return
    item.value = res.data
    loadRelated(slug)
  } catch {
    if (requestId !== loadSeq) return
    const fb = fallbackNews.find((n) => n.slug === slug)
    if (fb) item.value = fb
    else notFound.value = true
  } finally {
    if (requestId === loadSeq) loading.value = false
  }
}

watch(() => route.params.slug, (slug) => load(String(slug)), { immediate: true })

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

const related = ref<NewsItem[]>([])

const loadRelated = async (slug: string) => {
  try {
    const res = await api.news({ limit: 12 })
    related.value = res.data.filter((n) => n.slug !== slug).slice(0, 3)
  } catch {
    related.value = fallbackNews.filter((n) => n.slug !== slug).slice(0, 3)
  }
}

const readingTime = computed(() => {
  if (!item.value?.content) return 3
  return Math.max(2, Math.round(item.value.content.length / 1200))
})

const shareUrl = computed(() => typeof window !== 'undefined' ? window.location.href : '')

const copyToClipboard = (text: string) => {
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(text)
  }
}

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.news'), to: '/news' },
  { label: item.value?.title || '' }
])
</script>

<template>
  <div v-if="loading" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-8 md:py-12">
    <div class="h-6 w-48 bg-surface-vlm animate-shimmer rounded-full mb-8"></div>
    <div class="h-72 bg-surface-vlm animate-shimmer rounded-3xl mb-8 border border-outline-variant"></div>
    <div class="space-y-4">
      <div class="h-4 bg-surface-vlm animate-shimmer rounded-full"></div>
      <div class="h-4 bg-surface-vlm animate-shimmer rounded-full w-11/12"></div>
      <div class="h-4 bg-surface-vlm animate-shimmer rounded-full w-4/5"></div>
    </div>
  </div>

  <div v-else-if="item">
    <PageHeader :title="item.title" :breadcrumbs="breadcrumbs" :eyebrow="item.category?.name || t('nav.news')" />

    <article class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14">
      <!-- Hero Image -->
      <div class="relative overflow-hidden rounded-3xl border border-outline-variant mb-10 shadow-[0_20px_60px_rgba(0,0,0,0.08)]">
        <LazyImage
          :src="item.image"
          :alt="item.title"
          fallback-src="/images/products/industrial-1.jpg"
          aspect-ratio="aspect-[16/9] md:aspect-[21/9]"
          image-class="w-full h-full object-cover object-center hover:scale-105 transition-transform duration-700"
        />
      </div>

      <!-- Article Meta -->
      <div class="flex flex-wrap items-center justify-center gap-4 mb-8">
        <span v-if="item.category" class="inline-block bg-primary/10 text-primary-deep font-bold text-[12px] px-4 py-1.5 rounded-full uppercase tracking-[0.15em]">{{ item.category.name }}</span>
        <span class="text-[13px] text-text-muted font-bold uppercase tracking-[0.12em] flex items-center gap-1.5">
          <i class="fa text-[16px] fa-calendar"></i>
          {{ formatDateLong(item.published_at) }}
        </span>
        <span class="text-[13px] text-text-muted font-bold uppercase tracking-[0.12em] flex items-center gap-1.5">
          <i class="fa text-[16px] fa-clock"></i>
          {{ readingTime }} phút đọc
        </span>
      </div>

      <!-- Article Title -->
      <h1 class="text-[28px] md:text-[44px] text-text-main font-extrabold mb-10 leading-[1.15] tracking-tight text-center">{{ item.title }}</h1>

      <!-- Excerpt -->
      <p class="text-text-secondary text-[17px] md:text-[19px] font-medium leading-[1.85] mb-14 text-center max-w-4xl mx-auto">{{ item.excerpt }}</p>

      <!-- Divider -->
      <div class="flex items-center gap-4 mb-12 max-w-4xl mx-auto">
        <div class="h-px flex-grow bg-gradient-to-r from-primary/40 to-transparent"></div>
        <i class="fa text-primary text-[20px] fa-book-open"></i>
        <div class="h-px flex-grow bg-gradient-to-l from-primary/40 to-transparent"></div>
      </div>

      <!-- Article Content -->
      <div class="news-content text-text-secondary leading-[1.9] text-[16px] md:text-[17px] max-w-4xl mx-auto" v-if="item.content" v-html="item.content"></div>
      <div v-else class="text-text-secondary leading-[1.9] text-[16px] md:text-[17px] max-w-4xl mx-auto">{{ item.excerpt }}</div>

      <!-- Share & Navigation -->
      <div class="mt-14 pt-8 border-t border-outline-variant max-w-4xl mx-auto flex flex-col sm:flex-row items-center gap-4 justify-center">
        <span class="text-[11px] font-bold text-text-muted uppercase tracking-[0.15em]">Chia sẻ bài viết</span>
        <div class="flex items-center gap-2">
          <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-surface-vlm border border-outline-variant flex items-center justify-center hover:bg-[#1877F2] hover:text-white hover:border-[#1877F2] transition-all duration-300">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
          </a>
          <a :href="`https://zalo.me/share?u=${encodeURIComponent(shareUrl)}`" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-surface-vlm border border-outline-variant flex items-center justify-center hover:bg-[#0068FF] hover:text-white hover:border-[#0068FF] transition-all duration-300 text-[11px] font-bold">Zalo</a>
          <button @click="copyToClipboard(shareUrl)" class="w-10 h-10 rounded-full bg-surface-vlm border border-outline-variant flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all duration-300">
            <i class="fa text-[18px] fa-link"></i>
          </button>
        </div>
      </div>
    </article>

    <!-- Related News -->
    <section v-if="related.length" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop pb-20">
      <div class="text-center mb-12">
        <span class="inline-block px-3.5 py-1 rounded-full bg-primary/10 border border-primary/20 text-primary text-[11px] font-extrabold tracking-[0.15em] uppercase mb-3">Tin tức liên quan</span>
        <h2 class="text-[32px] md:text-[40px] font-extrabold text-text-main tracking-tight">Đọc thêm</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 stagger-grid">
        <router-link v-for="(n, i) in related" :key="n.slug" :to="`/news/${n.slug}`"
          class="group bg-surface-bright rounded-3xl border border-outline-variant overflow-hidden card-shine glow-card hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 flex flex-col"
          :class="`reveal-delay-${(i % 3) + 1}`">
          <div class="relative overflow-hidden">
            <LazyImage
              :src="n.image"
              :alt="n.title"
              fallback-src="/images/products/industrial-1.jpg"
              aspect-ratio="aspect-[16/10]"
              image-class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
            />
            <div class="absolute top-4 left-4">
              <span class="text-[10px] font-bold text-white bg-primary/90 px-3 py-1 rounded-full uppercase tracking-[0.15em]">{{ n.category?.name || 'Tin tức' }}</span>
            </div>
          </div>
          <div class="p-6 flex flex-col flex-grow">
            <h3 class="font-extrabold text-text-main text-[17px] leading-snug line-clamp-2 group-hover:text-primary transition-colors duration-300 mb-4">{{ n.title }}</h3>
            <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-primary group-hover:gap-2.5 transition-all duration-300 mt-auto uppercase tracking-[0.12em]">
              {{ t('news.readMore') }}
              <i class="fa text-[14px] group-hover:translate-x-1 transition-transform fa-arrow-right"></i>
            </span>
          </div>
        </router-link>
      </div>
    </section>

    <!-- Navigation -->
    <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 flex items-center justify-center gap-6 border-t border-outline-variant">
      <router-link to="/news" class="inline-flex items-center gap-3 text-primary-deep font-bold text-[14px] uppercase tracking-[0.12em] hover:text-primary transition-colors duration-300 group">
        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all duration-300">
          <i class="fa text-[18px] group-hover:-translate-x-1 transition-transform duration-300 fa-arrow-left"></i>
        </div>
        {{ t('news.backToList') }}
      </router-link>
      <span class="w-px h-6 bg-outline-variant"></span>
      <router-link
        :to="buildContactLink({ subject: 'general' })"
        class="btn bg-primary text-[#2A2420] hover:bg-primary-dark hover:text-white rounded-full inline-flex items-center gap-3 px-8 py-4 group shadow-[0_4px_20px_rgba(184,155,136,0.3)]"
      >
        {{ t('news.contact') }}
        <i class="fa text-[16px] group-hover:translate-x-1 transition-transform fa-paper-plane"></i>
      </router-link>
    </div>
  </div>

  <div v-else-if="notFound" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16 text-center">
    <i class="fa text-7xl text-outline-variant mb-6 block fa-newspaper"></i>
    <h1 class="text-[28px] text-text-main font-bold mb-6">{{ t('news.notFound') }}</h1>
    <router-link to="/news" class="btn bg-primary text-[#2A2420] hover:bg-primary-dark hover:text-white rounded-full inline-flex items-center gap-2">
      <i class="fa text-lg fa-arrow-left"></i> {{ t('news.backToList') }}
    </router-link>
  </div>
</template>

<style scoped>
.news-content :deep(p) {
  margin-bottom: 1.5rem;
  line-height: 1.9;
}

.news-content :deep(p:last-child) {
  margin-bottom: 0;
}

.news-content :deep(strong) {
  font-weight: 700;
  color: #2a2420;
}

.news-content :deep(em) {
  font-style: italic;
  color: #6b5f56;
}

.news-content :deep(a) {
  color: #a98468;
  text-decoration: underline;
  text-underline-offset: 2px;
  word-break: break-word;
}

.news-content :deep(ul),
.news-content :deep(ol) {
  margin: 0 0 1.5rem;
  padding-left: 1.75rem;
}

.news-content :deep(ul) {
  list-style: disc;
}

.news-content :deep(ol) {
  list-style: decimal;
}

.news-content :deep(li) {
  margin-bottom: 0.5rem;
  line-height: 1.8;
}

.news-content :deep(img),
.news-content :deep(video) {
  max-width: 100%;
  height: auto;
  border-radius: 16px;
  margin: 1.5rem 0;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.news-content :deep(h2) {
  font-size: 26px;
  font-weight: 800;
  color: #2a2420;
  margin: 2rem 0 1rem;
  line-height: 1.3;
}

.news-content :deep(h3),
.news-content :deep(h4) {
  font-size: 20px;
  font-weight: 800;
  color: #2a2420;
  margin: 1.75rem 0 0.75rem;
}

.news-content :deep(blockquote) {
  border-left: 4px solid #b89b88;
  padding-left: 1.25rem;
  color: #6b5f56;
  font-style: italic;
  margin: 0 0 1.5rem;
  font-size: 17px;
  background: #faf7f3;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  border-radius: 0 8px 8px 0;
}

.news-content :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin: 0 0 1.5rem;
  font-size: 15px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
}

.news-content :deep(th),
.news-content :deep(td) {
  border: 1px solid #e5ddd6;
  padding: 0.75rem 1rem;
  text-align: left;
}

.news-content :deep(th) {
  background: #f6f1ec;
  font-weight: 700;
  color: #2a2420;
}
</style>
