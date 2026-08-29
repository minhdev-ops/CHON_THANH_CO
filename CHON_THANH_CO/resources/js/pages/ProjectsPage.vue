<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { api } from '../api/client'
import type { Project } from '../types'
import PageHeader from '../components/PageHeader.vue'
import ErrorState from '../components/ErrorState.vue'
import CTABanner from '../components/CTABanner.vue'
import { t } from '../i18n'
import { fallbackProjects } from '../types/fallback'

const projects = ref<Project[]>(fallbackProjects)
const projectsLoading = ref(false)
const loadingMore = ref(false)
const loadError = ref<string | null>(null)
const nextCursor = ref<number | null>(null)
const hasMore = computed(() => nextCursor.value !== null && nextCursor.value !== undefined)

const selectedLocation = ref<string | null>(null)
const searchQuery = ref('')

const allProjects = ref<Project[]>([])

const loadAllFallback = async () => {
  try {
    let cursor: number | null | undefined
    do {
      const page = await api.projects({ cursor, limit: 50 })
      allProjects.value.push(...page.data)
      cursor = page.next_cursor
    } while (cursor != null)
  } catch {
    allProjects.value = [...fallbackProjects]
  }
}

const loadMore = async (reset = false) => {
  if (reset) {
    nextCursor.value = null
    loadError.value = null
    projectsLoading.value = true
  } else {
    loadingMore.value = true
  }
  try {
    const res = await api.projects({ limit: 50, cursor: nextCursor.value ?? undefined })
    let list = res.data ?? []
    if (reset) {
      projects.value = list.length ? list : filteredFallback.value
    } else {
      projects.value.push(...list)
    }
    nextCursor.value = res.next_cursor
  } catch (e) {
    loadError.value = e instanceof Error ? e.message : t('common.errorGeneric')
    if (reset) projects.value = filteredFallback.value
  } finally {
    projectsLoading.value = false
    loadingMore.value = false
  }
}

const filteredFallback = computed(() => {
  let list = [...fallbackProjects]
  if (selectedLocation.value) list = list.filter((p) => p.location?.includes(selectedLocation.value!))
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter((p) => p.name.toLowerCase().includes(q) || p.location.toLowerCase().includes(q))
  }
  return list
})

onMounted(async () => {
  loadAllFallback()
  await loadMore(true)
  await nextTick()
  window.scrollTo({ top: 0, behavior: 'instant' as ScrollBehavior })
})

const locations = computed(() => {
  const set = new Set<string>()
  fallbackProjects.forEach((p) => set.add(p.location.split(',')[0].trim()))
  return Array.from(set)
})

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.projects') }
])

// Statistics
const stats = computed(() => {
  const totalArea = projects.value.reduce((sum, p) => {
    const m = p.area?.match(/[\d,]+/)
    return sum + (m ? Number(m[0].replace(/,/g, '')) : 0)
  }, 0)
  const totalLocations = new Set(projects.value.map((p) => p.location.split(',')[0].trim())).size
  return {
    count: projects.value.length,
    locations: totalLocations,
    area: totalArea.toLocaleString('vi-VN')
  }
})

const paddedIndex = (i: number) => String(i + 1).padStart(2, '0')

// Bento layout: alternating row patterns that always total 12 cols.
// Row A: [8, 4] (2 items, total = 12)
// Row B: [4, 4, 4] (3 items, total = 12)
// Repeat: A, B, A, B, ...
const bentoRowEnd = [1, 4, 6, 9, 11, 14, 16, 19, 21, 24, 26, 29, 31, 34, 36, 39]
const bentoCol = (i: number) => {
  for (let r = 0; r < bentoRowEnd.length; r++) {
    if (i <= bentoRowEnd[r]) {
      const start = r === 0 ? 0 : bentoRowEnd[r - 1] + 1
      const offset = i - start
      const isARow = r % 2 === 0
      if (isARow) {
        return offset === 0 ? 'md:col-span-8' : 'md:col-span-4'
      } else {
        return 'md:col-span-4'
      }
    }
  }
  return 'md:col-span-4'
}

const bentoSpan = (i: number) => bentoCol(i)

const bentoAspect = (i: number) => {
  for (let r = 0; r < bentoRowEnd.length; r++) {
    if (i <= bentoRowEnd[r]) {
      const start = r === 0 ? 0 : bentoRowEnd[r - 1] + 1
      const offset = i - start
      const isARow = r % 2 === 0
      if (isARow && offset === 0) return 'aspect-[16/10]'
      return 'aspect-[4/3]'
    }
  }
  return 'aspect-[4/3]'
}
</script>

<template>
  <div>
    <PageHeader :title="t('nav.projects')" :breadcrumbs="breadcrumbs" />

    <main class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14 animate-fade-in-up">
      <!-- ═══ FILTER BAR ═══ -->
      <div class="mb-10 reveal">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 pb-5 border-b border-text-main">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em] mr-1">/FILTER</span>
            <button
              class="px-4 py-2 text-[11px] font-bold uppercase tracking-[0.15em] border transition-all duration-300 rounded-none"
              :class="selectedLocation === null
                ? 'bg-primary text-white border-text-main'
                : 'bg-transparent text-text-secondary border-outline-variant hover:border-text-main hover:text-text-main'"
              @click="selectedLocation = null"
            >{{ t('projects.all') }}</button>
            <button
              v-for="loc in locations" :key="loc"
              class="px-4 py-2 text-[11px] font-bold uppercase tracking-[0.15em] border transition-all duration-300 rounded-none"
              :class="selectedLocation === loc
                ? 'bg-primary text-white border-text-main'
                : 'bg-transparent text-text-secondary border-outline-variant hover:border-text-main hover:text-text-main'"
              @click="selectedLocation = loc"
            >{{ loc }}</button>
          </div>
          <div class="flex items-center gap-3">
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-[16px]">search</span>
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="t('projects.searchPlaceholder')"
                class="bg-canvas border border-outline-variant pl-9 pr-3 py-2 text-[12px] text-text-main outline-none focus:border-text-main transition-colors w-56 rounded-none placeholder:text-text-muted/60"
              >
            </div>
            <div class="hidden sm:flex flex-col">
              <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em] uppercase">/TOTAL</span>
              <span class="font-bold text-[16px] text-text-main leading-none tabular-nums mt-0.5">{{ String(projects.length).padStart(3, '0') }} {{ t('projects.results') }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ LOADING ═══ -->
      <div v-if="projectsLoading" class="grid grid-cols-12 gap-4">
        <div v-for="i in 6" :key="i" class="col-span-12 sm:col-span-6 lg:col-span-4 h-80 bg-canvas animate-shimmer border border-outline-variant"></div>
      </div>

      <!-- ═══ ERROR ═══ -->
      <div v-else-if="loadError && !projects?.length" class="bg-canvas border border-outline-variant p-8">
        <ErrorState :message="loadError" @retry="loadMore(true)" />
      </div>

      <!-- ═══ PROJECT BENTO GRID ═══ -->
      <div v-else-if="projects?.length" class="grid grid-cols-12 gap-6 lg:gap-8 stagger-grid">
        <router-link
          v-for="(project, i) in projects" :key="project.slug"
          :to="`/projects/${project.slug}`"
          class="group flex flex-col col-span-12 bg-surface-bright border border-outline-variant rounded-3xl overflow-hidden hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 reveal"
          :class="[bentoSpan(i), `reveal-delay-${(i%3)+1}`]"
        >
          <!-- Image wrapper -->
          <div class="relative w-full overflow-hidden bg-surface-vlm shrink-0" :class="bentoAspect(i)">
            <img
              :src="project.hero_image" :alt="project.name"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
              loading="lazy"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-text-main/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="absolute top-5 left-5 bg-canvas/95 backdrop-blur-md px-3 py-1.5 rounded-full flex items-center gap-2 shadow-sm border border-outline-variant">
              <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
              <span class="text-[10px] font-mono font-bold text-text-main tracking-[0.25em] uppercase">{{ t('projects.completed') }}</span>
            </div>
            
            <!-- Index (giant, subtle in background of image) -->
            <div class="absolute bottom-3 right-5 font-sans text-[64px] font-extrabold text-canvas/40 leading-none select-none pointer-events-none tabular-nums z-10 drop-shadow-md">{{ paddedIndex(i) }}</div>
          </div>

          <!-- Content -->
          <div class="p-6 md:p-8 flex flex-col flex-grow relative">
            <div class="flex items-center gap-2 mb-3">
              <span class="text-[11px] font-bold text-primary uppercase tracking-[0.2em]">{{ project.period }}</span>
              <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
              <span class="text-[11px] font-bold text-text-muted uppercase tracking-[0.15em] line-clamp-1">{{ project.location }}</span>
            </div>
            
            <h3 class="font-extrabold text-[19px] text-text-main mb-3 group-hover:text-primary transition-colors duration-300 leading-snug line-clamp-2">
              {{ project.name }}
            </h3>
            
            <p v-if="project.area || project.materials" class="text-text-secondary text-[14px] leading-relaxed line-clamp-2 mb-6 flex-grow">
              <span v-if="project.area">Quy mô: {{ project.area }}. </span>
              <span v-if="project.materials">Sử dụng {{ project.materials.length }} loại vật liệu địa kỹ thuật.</span>
            </p>
            <div v-else class="flex-grow mb-6"></div>

            <div class="mt-auto flex items-center justify-between pt-5 border-t border-outline-variant/60">
              <span class="inline-flex items-center gap-2 font-bold text-[13px] text-primary group-hover:text-primary-deep uppercase tracking-[0.12em] transition-colors duration-300">
                Chi tiết dự án <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </span>
            </div>
          </div>
        </router-link>
      </div>

      <!-- ═══ LOAD MORE ═══ -->
      <div v-if="hasMore && !projectsLoading" class="mt-12 flex justify-center">
        <button type="button" class="group inline-flex items-center gap-3 bg-transparent border border-text-main text-text-main px-8 py-3.5 text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-text-main hover:text-canvas transition-all duration-300 rounded-none" :disabled="loadingMore" @click="loadMore()">
          <span>{{ loadingMore ? t('common.loading') : t('common.loadMore') }}</span>
          <span v-if="!loadingMore" class="material-symbols-outlined text-[16px] group-hover:translate-y-0.5 transition-transform duration-300">expand_more</span>
        </button>
      </div>
    </main>

    <CTABanner
      :title="t('projects.ctaTitle')"
      :linkLabel="t('projects.ctaButton')"
    />
  </div>
</template>
