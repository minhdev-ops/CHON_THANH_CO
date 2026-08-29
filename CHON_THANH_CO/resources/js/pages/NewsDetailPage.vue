<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import type { NewsItem } from '../types'
import PageHeader from '../components/PageHeader.vue'
import { t, locale } from '../i18n'
import { fallbackNews } from '../types/fallback'

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

const paragraphs = (content?: string) =>
  (content ?? '').split(/\n{2,}/).map((p) => p.trim()).filter(Boolean)

const related = computed(() => {
  if (!item.value) return []
  return fallbackNews.filter((n) => n.slug !== item.value!.slug).slice(0, 3)
})

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
    <PageHeader :title="item.title" :breadcrumbs="breadcrumbs" />

    <article class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14">
      <!-- Hero Image -->
      <div class="relative aspect-[16/9] md:aspect-[21/9] overflow-hidden rounded-3xl bg-surface-vlm border border-outline-variant mb-10">
        <img :src="item.image" :alt="item.title" class="w-full h-full object-cover object-center hover:scale-105 transition-transform duration-700">
      </div>

      <!-- Article Meta -->
      <div class="flex flex-wrap items-center gap-4 mb-8">
        <span v-if="item.category" class="inline-block bg-primary/10 text-primary-deep font-bold text-[12px] px-4 py-1.5 rounded-full uppercase tracking-[0.15em]">{{ item.category.name }}</span>
        <span class="text-[13px] text-text-muted font-bold uppercase tracking-[0.12em] flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">calendar_month</span>
          {{ formatDateLong(item.published_at) }}
        </span>
        <span class="text-[13px] text-text-muted font-bold uppercase tracking-[0.12em] flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">schedule</span>
          {{ readingTime }} phút đọc
        </span>
      </div>

      <!-- Article Title -->
      <h1 class="text-[28px] md:text-[38px] text-text-main font-extrabold mb-10 leading-[1.2] tracking-tight max-w-4xl">{{ item.title }}</h1>

      <!-- Excerpt -->
      <p class="text-text-main border-l-[3px] border-primary pl-6 mb-12 text-[18px] font-medium leading-[1.8] max-w-3xl">{{ item.excerpt }}</p>

      <!-- Article Content -->
      <div class="max-w-3xl">
        <div v-if="paragraphs(item.content).length" class="text-text-secondary space-y-6 leading-[1.8] text-[16px]">
          <p v-for="(para, i) in paragraphs(item.content)" :key="i">{{ para }}</p>
        </div>
        <div v-else class="text-text-secondary text-[16px] leading-[1.8]">{{ item.excerpt }}</div>
      </div>

      <!-- Share & Navigation -->
      <div class="mt-12 pt-8 border-t border-outline-variant flex flex-col sm:flex-row items-start sm:items-center gap-4 justify-between">
        <span class="text-[11px] font-bold text-text-muted uppercase tracking-[0.15em]">Chia sẻ bài viết</span>
        <div class="flex items-center gap-2">
          <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-surface-vlm border border-outline-variant flex items-center justify-center hover:bg-[#1877F2] hover:text-white hover:border-[#1877F2] transition-all duration-300">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
          </a>
          <a :href="`https://zalo.me/share?u=${encodeURIComponent(shareUrl)}`" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-surface-vlm border border-outline-variant flex items-center justify-center hover:bg-[#0068FF] hover:text-white hover:border-[#0068FF] transition-all duration-300 text-[11px] font-bold">Zalo</a>
          <button @click="copyToClipboard(shareUrl)" class="w-10 h-10 rounded-full bg-surface-vlm border border-outline-variant flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all duration-300">
            <span class="material-symbols-outlined text-[18px]">link</span>
          </button>
        </div>
      </div>
    </article>

    <!-- Related News -->
    <section v-if="related.length" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop pb-20">
      <div class="text-center mb-12">
        <span class="kicker inline-block mb-3">Tin tức liên quan</span>
        <h2 class="text-[32px] md:text-[40px] font-extrabold text-text-main tracking-tight">Đọc thêm</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 stagger-grid">
        <router-link v-for="(n, i) in related" :key="n.slug" :to="`/news/${n.slug}`"
          class="group bg-surface-bright rounded-3xl border border-outline-variant overflow-hidden card-shine glow-card hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500"
          :class="`reveal-delay-${(i % 3) + 1}`">
          <div class="aspect-[16/10] overflow-hidden">
            <img :src="n.image" :alt="n.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
          </div>
          <div class="p-6">
            <span class="text-[11px] font-bold text-primary uppercase tracking-[0.15em] mb-3 block">{{ n.category?.name || 'Tin tức' }}</span>
            <h3 class="font-extrabold text-text-main text-[16px] leading-snug line-clamp-2 group-hover:text-primary transition-colors duration-300 mb-3">{{ n.title }}</h3>
            <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-primary group-hover:gap-2.5 transition-all duration-300">
              {{ t('news.readMore') }}
              <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </span>
          </div>
        </router-link>
      </div>
    </section>

    <!-- Navigation -->
    <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-8 flex items-center justify-between border-t border-outline-variant">
      <router-link to="/news" class="inline-flex items-center gap-2 text-primary-deep font-bold text-[13px] uppercase tracking-[0.12em] hover:text-primary transition-colors duration-300 group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform duration-300">arrow_back</span>
        {{ t('news.backToList') }}
      </router-link>
      <router-link to="/contact" class="btn bg-primary text-white hover:bg-primary-dark rounded-full inline-flex items-center gap-2 group">
        {{ t('news.contact') }}
        <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">send</span>
      </router-link>
    </div>
  </div>

  <div v-else-if="notFound" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16 text-center">
    <span class="material-symbols-outlined text-7xl text-outline-variant mb-6 block">article</span>
    <h1 class="text-[28px] text-text-main font-bold mb-6">{{ t('news.notFound') }}</h1>
    <router-link to="/news" class="btn bg-primary text-white hover:bg-primary-dark rounded-full inline-flex items-center gap-2">
      <span class="material-symbols-outlined text-lg">arrow_back</span> {{ t('news.backToList') }}
    </router-link>
  </div>
</template>
