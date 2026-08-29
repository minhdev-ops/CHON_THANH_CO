<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import { useFocusTrap } from '../composables/useFocusTrap'
import type { Project } from '../types'
import { t } from '../i18n'
import { fallbackProjects } from '../types/fallback'

const route = useRoute()
const project = ref<Project | null>(null)
const loading = ref(true)
const notFound = ref(false)
const prev = ref<Project | null>(null)
const next = ref<Project | null>(null)
const lightboxIndex = ref<number | null>(null)
const lightboxEl = ref<HTMLElement | null>(null)
const lightboxOpen = ref(false)
let loadSeq = 0

const onKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape') lightboxIndex.value = null
  if (lightboxIndex.value !== null) {
    if (e.key === 'ArrowLeft') lightboxIndex.value = (lightboxIndex.value - 1 + (project.value?.gallery?.length ?? 0)) % (project.value?.gallery?.length ?? 1)
    if (e.key === 'ArrowRight') lightboxIndex.value = (lightboxIndex.value + 1) % (project.value?.gallery?.length ?? 1)
  }
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))

watch(lightboxIndex, (v) => {
  lightboxOpen.value = v !== null
  document.body.style.overflow = v !== null ? 'hidden' : ''
})

useFocusTrap(lightboxEl, lightboxOpen)

const load = async (slug: string) => {
  const requestId = ++loadSeq
  loading.value = true
  project.value = null
  notFound.value = false
  prev.value = null
  next.value = null
  try {
    const res = await api.project(slug)
    if (requestId !== loadSeq) return
    project.value = res.data

    let all: Project[] = []
    let cursor: number | null | undefined
    let index = -1
    do {
      const page = await api.projects({ cursor, limit: 50 })
      if (requestId !== loadSeq) return
      all = all.concat(page.data)
      cursor = page.next_cursor
      index = all.findIndex((p) => p.slug === slug)
    } while (index < 0 && cursor != null)

    if (index >= 0) {
      prev.value = index > 0 ? all[index - 1] : null
      next.value = index < all.length - 1 ? all[index + 1] : null
    } else {
      const fbIndex = fallbackProjects.findIndex((p) => p.slug === slug)
      if (fbIndex >= 0) {
        prev.value = fbIndex > 0 ? fallbackProjects[fbIndex - 1] : null
        next.value = fbIndex < fallbackProjects.length - 1 ? fallbackProjects[fbIndex + 1] : null
      }
    }
  } catch {
    if (requestId !== loadSeq) return
    const fb = fallbackProjects.find((p) => p.slug === slug)
    if (fb) {
      project.value = fb
      const fbIndex = fallbackProjects.findIndex((p) => p.slug === slug)
      prev.value = fbIndex > 0 ? fallbackProjects[fbIndex - 1] : null
      next.value = fbIndex < fallbackProjects.length - 1 ? fallbackProjects[fbIndex + 1] : null
    } else {
      notFound.value = true
    }
  } finally {
    if (requestId === loadSeq) loading.value = false
  }
}

watch(() => route.params.slug, (slug) => load(String(slug)), { immediate: true })
</script>

<template>
  <div v-if="loading">
    <div class="h-[50vh] min-h-[400px] bg-surface-container-high animate-shimmer"></div>
  </div>

  <div v-else-if="project">
    <header class="relative w-full h-[55vh] min-h-[450px] flex items-end hero-parallax overflow-hidden">
      <div class="absolute inset-0 z-0">
        <img :src="project.hero_image" :alt="project.name" class="w-full h-full object-cover object-center scale-105" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/50 to-black/20"></div>
        <div class="absolute inset-0 opacity-30" style="background: radial-gradient(ellipse at 30% 70%, rgba(184,155,136,0.2) 0%, transparent 50%);"></div>
      </div>

      <div class="absolute top-20 right-[10%] w-16 h-16 border border-white/10 rounded-full animate-float pointer-events-none"></div>
      <div class="absolute bottom-32 left-[8%] w-8 h-8 bg-white/5 rotate-45 animate-float-slow pointer-events-none"></div>

      <div class="relative z-10 w-full max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop pb-20 reveal">
        <h1 class="text-[36px] md:text-[52px] text-white font-bold mb-8 leading-[1.1] max-w-4xl tracking-tight" style="text-shadow: 0 4px 30px rgba(0,0,0,0.3);">{{ project.name }}</h1>
        <div class="flex gap-4 flex-wrap">
          <span class="inline-flex items-center gap-2.5 bg-primary text-white px-6 py-3 rounded-2xl text-[15px] font-bold shadow-lg hover:shadow-xl transition-shadow duration-300">
            <span class="material-symbols-outlined text-[20px]">location_on</span> {{ project.location }}
          </span>
          <span class="inline-flex items-center gap-2.5 glass-premium text-white px-6 py-3 rounded-2xl text-[15px] font-bold shadow-lg">
            <span class="material-symbols-outlined text-[20px]">calendar_month</span> {{ project.period }}
          </span>
          <span v-if="project.area" class="inline-flex items-center gap-2.5 glass-premium text-white px-6 py-3 rounded-2xl text-[15px] font-bold shadow-lg">
            <span class="material-symbols-outlined text-[20px]">square_foot</span> {{ project.area }}
          </span>
        </div>
      </div>
    </header>

    <section class="bg-white border-b border-outline-variant">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-12 reveal">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div class="flex flex-col gap-2.5 border-l-[3px] border-primary pl-5 group">
            <span class="text-[12px] text-text-muted uppercase tracking-[0.15em] font-bold">{{ t('project.locationLabel') }}</span>
            <strong class="text-[17px] text-text-main font-bold group-hover:text-primary transition-colors duration-300">{{ project.location }}</strong>
          </div>
          <div class="flex flex-col gap-2.5 border-l-[3px] border-primary pl-5 group">
            <span class="text-[12px] text-text-muted uppercase tracking-[0.15em] font-bold">{{ t('project.periodLabel') }}</span>
            <strong class="text-[17px] text-text-main font-bold group-hover:text-primary transition-colors duration-300">{{ project.period }}</strong>
          </div>
          <div class="flex flex-col gap-2.5 border-l-[3px] border-primary pl-5 group">
            <span class="text-[12px] text-text-muted uppercase tracking-[0.15em] font-bold">{{ t('project.materialsLabel') }}</span>
            <strong class="text-[17px] text-text-main font-bold group-hover:text-primary transition-colors duration-300">{{ project.materials?.length ? project.materials.length + ' ' + t('project.types') : '-' }}</strong>
          </div>
          <div class="flex flex-col gap-2.5 border-l-[3px] border-primary pl-5 group">
            <span class="text-[12px] text-text-muted uppercase tracking-[0.15em] font-bold">{{ t('project.areaLabel') }}</span>
            <strong class="text-[17px] text-text-main font-bold group-hover:text-primary transition-colors duration-300">{{ project.area || '-' }}</strong>
          </div>
        </div>
      </div>
    </section>

    <section class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-16 md:py-20">
      <div class="grid md:grid-cols-12 gap-12 lg:gap-16 items-center">
        <div class="md:col-span-5 flex flex-col gap-8 reveal-left">
          <div>
            <span class="kicker mb-4 block">{{ t('project.description') }}</span>
            <h2 class="text-[32px] md:text-[38px] text-text-main font-bold tracking-tight leading-[1.15]">Mô tả dự án</h2>
          </div>
          <div class="text-text-secondary space-y-5 leading-[1.8] text-[17px]">
            <p v-for="(para, i) in project.description?.split('\n\n') ?? []" :key="i">{{ para }}</p>
          </div>
        </div>
        <div class="md:col-span-7 rounded-3xl overflow-hidden shadow-[0_12px_40px_rgba(184,155,136,0.12)] hover:shadow-[0_20px_60px_rgba(184,155,136,0.18)] transition-all duration-500 reveal-right group">
          <img :src="project.desc_image" :alt="project.name" class="w-full aspect-[4/3] md:aspect-video object-cover object-center group-hover:scale-105 transition-transform duration-700" loading="lazy">
        </div>
      </div>
    </section>

    <section v-if="project.materials?.length" class="bg-surface-vlm border-y border-outline-variant/60 py-16 md:py-20 relative overflow-hidden">
      <div class="absolute inset-0 mesh-bg opacity-50"></div>
      <div class="absolute inset-0 noise-overlay pointer-events-none"></div>
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop relative z-10">
        <div class="text-center mb-14 reveal">
          <span class="eyebrow mb-3 block">{{ t('project.materials') }}</span>
          <h2 class="text-[32px] md:text-[42px] font-bold text-text-main tracking-tight">{{ t('project.materials') }}</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-8 stagger-grid">
          <div v-for="prod in project.materials" :key="prod.name" class="bg-surface-bright rounded-3xl border border-outline-variant shadow-sm hover:shadow-[0_16px_48px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 flex flex-col sm:flex-row overflow-hidden group card-shine">
            <div class="w-full sm:w-2/5 shrink-0 bg-surface-vlm p-6 flex items-center justify-center border-b sm:border-b-0 sm:border-r border-outline-variant">
              <img :src="prod.image" :alt="prod.name" class="w-full h-32 object-contain group-hover:scale-110 transition-transform duration-500" loading="lazy">
            </div>
            <div class="p-8 flex flex-col justify-center">
              <h3 class="font-bold text-[20px] text-primary-deep mb-3">{{ prod.name }}</h3>
              <p class="text-[16px] text-text-secondary mb-6 leading-relaxed">{{ prod.detail }}</p>
              <router-link :to="`/products`" class="inline-flex items-center gap-2 font-bold text-[14px] text-primary hover:text-primary-deep uppercase tracking-[0.12em] transition-colors duration-300 mt-auto w-fit group/link">
                {{ t('project.viewProduct') }} <span class="material-symbols-outlined text-[18px] group-hover/link:translate-x-1 transition-transform duration-300">arrow_forward</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-16 md:py-20">
      <div class="text-center mb-14 reveal">
        <span class="eyebrow mb-3 block">{{ t('project.gallery') }}</span>
        <h2 class="text-[36px] md:text-[48px] text-text-main font-bold tracking-tight">{{ t('project.gallery') }}</h2>
      </div>
      <div v-if="project.gallery?.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 stagger-grid">
        <button v-for="(img, i) in project.gallery" :key="i" type="button"
          class="aspect-square bg-surface-vlm rounded-2xl overflow-hidden group border border-outline-variant shadow-sm hover:shadow-[0_12px_36px_rgba(184,155,136,0.15)] hover:border-primary/40 transition-all duration-500"
          :aria-label="img.alt || project.name" @click="lightboxIndex = i">
          <span class="block w-full h-full bg-cover bg-center group-hover:scale-115 transition-transform duration-700 ease-out" :style="{ backgroundImage: `url(${img.image})` }"></span>
        </button>
      </div>
      <div v-else class="text-center text-text-muted py-12">
        <span class="material-symbols-outlined text-5xl mb-3 block">image</span>
        Hình ảnh đang cập nhật
      </div>
    </section>

    <div v-if="lightboxIndex !== null" ref="lightboxEl" role="dialog" aria-modal="true"
      class="fixed inset-0 z-[100] bg-black/95 flex items-center justify-center p-4" @click.self="lightboxIndex = null">
      <button type="button" class="absolute top-6 right-6 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors duration-300 backdrop-blur-md" :aria-label="t('common.close')" @click="lightboxIndex = null">
        <span class="material-symbols-outlined">close</span>
      </button>
      <button v-if="project.gallery && project.gallery.length > 1" type="button"
        class="absolute left-4 md:left-8 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all duration-300 backdrop-blur-md"
        @click="lightboxIndex = (lightboxIndex! - 1 + project.gallery!.length) % project.gallery!.length">
        <span class="material-symbols-outlined">chevron_left</span>
      </button>
      <button v-if="project.gallery && project.gallery.length > 1" type="button"
        class="absolute right-4 md:right-8 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all duration-300 backdrop-blur-md"
        @click="lightboxIndex = (lightboxIndex! + 1) % project.gallery!.length">
        <span class="material-symbols-outlined">chevron_right</span>
      </button>
      <figure class="max-w-5xl w-full animate-scale-in">
        <img v-if="project.gallery && lightboxIndex !== null && project.gallery[lightboxIndex]"
          :src="project.gallery[lightboxIndex].image" :alt="project.gallery[lightboxIndex].alt || project.name"
          class="w-full max-h-[80vh] object-contain rounded-xl">
        <figcaption class="text-center text-white/50 mt-6 text-[15px] font-medium">
          {{ project.name }}<span v-if="project.gallery && project.gallery.length > 1"> — {{ (lightboxIndex ?? 0) + 1 }}/{{ project.gallery.length }}</span>
        </figcaption>
      </figure>
    </div>

    <section class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 flex justify-between items-center border-t border-outline-variant">
      <router-link v-if="prev" :to="`/projects/${prev.slug}`" class="flex items-center gap-3 font-bold text-[14px] text-primary-deep hover:text-primary transition-colors duration-300 group uppercase tracking-[0.12em]">
        <span class="material-symbols-outlined text-[20px] group-hover:-translate-x-1 transition-transform duration-300">arrow_back</span>
        <span class="hidden sm:inline">{{ prev.name }}</span>
        <span class="sm:hidden">{{ t('project.prev') }}</span>
      </router-link>
      <div v-else></div>
      <router-link to="/projects" class="hidden md:inline-flex items-center gap-2 text-text-muted font-bold text-[13px] uppercase tracking-[0.12em] hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[18px]">grid_view</span>
        Tất cả dự án
      </router-link>
      <router-link v-if="next" :to="`/projects/${next.slug}`" class="flex items-center gap-3 font-bold text-[14px] text-primary-deep hover:text-primary transition-colors duration-300 group uppercase tracking-[0.12em] text-right">
        <span class="hidden sm:inline">{{ next.name }}</span>
        <span class="sm:hidden">{{ t('project.next') }}</span>
        <span class="material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform duration-300">arrow_forward</span>
      </router-link>
      <div v-else></div>
    </section>
  </div>
  <div v-else-if="notFound" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16 text-center">
    <span class="material-symbols-outlined text-7xl text-outline-variant mb-6 block">folder_off</span>
    <h1 class="text-[32px] text-text-main font-bold mb-6">{{ t('project.notFound') }}</h1>
    <router-link to="/projects" class="btn btn-primary btn-magnetic inline-flex items-center gap-2">
      <span class="material-symbols-outlined text-lg">arrow_back</span> {{ t('project.backToList') }}
    </router-link>
  </div>
</template>
