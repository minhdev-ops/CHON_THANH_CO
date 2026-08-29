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
// Items 0,1 = row A (8,4); 2,3,4 = row B (4,4,4); 5,6 = row A; 7,8,9 = row B; etc.
// Cumulative offsets: A ends at 1, B ends at 4, A ends at 6, B ends at 9, ...
// rowEnd[0]=1, rowEnd[1]=4, rowEnd[2]=6, rowEnd[3]=9, rowEnd[4]=11, rowEnd[5]=14
const bentoRowEnd = [1, 4, 6, 9, 11, 14, 16, 19, 21, 24, 26, 29, 31, 34, 36, 39]
const bentoCol = (i: number) => {
  // Find which row this index falls into
  for (let r = 0; r < bentoRowEnd.length; r++) {
    if (i <= bentoRowEnd[r]) {
      const start = r === 0 ? 0 : bentoRowEnd[r - 1] + 1
      const offset = i - start
      const isARow = r % 2 === 0
      if (isARow) {
        // Row A: [8, 4]
        return offset === 0 ? 'md:col-span-8' : 'md:col-span-4'
      } else {
        // Row B: [4, 4, 4]
        return 'md:col-span-4'
      }
    }
  }
  // Fallback for indices beyond table
  return 'md:col-span-4'
}

const bentoSpan = (i: number) => bentoCol(i)

const bentoAspect = (i: number) => {
  // The first item in each row A is a "feature" (8-col, wider aspect)
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

    <!-- ═══ EDITORIAL MASTHEAD ═══ -->
    <section class="relative border-b border-outline-variant bg-canvas overflow-hidden">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="grid grid-cols-12 gap-x-6 gap-y-8 items-end">
          <div class="col-span-12 md:col-span-7 reveal">
            <div class="flex items-center gap-3 mb-5">
              <span class="font-mono text-[11px] font-bold text-primary tracking-[0.18em] uppercase">M—02</span>
              <span class="w-8 h-px bg-primary"></span>
              <span class="kicker">{{ t('projects.eyebrow') }}</span>
            </div>
            <h1 class="font-sans text-[44px] md:text-[68px] lg:text-[80px] text-text-main font-bold leading-[0.92] tracking-[-0.035em] mb-5">
              {{ t('projects.titlePart1') }}<br>
              <span class="italic font-normal text-primary">{{ t('projects.titlePart2') }}</span>
            </h1>
            <p class="text-text-secondary text-[15px] md:text-[17px] leading-[1.7] max-w-xl">
              {{ t('projects.subtitle') }}
            </p>
          </div>

          <!-- Stats panel (dark) -->
          <div class="col-span-12 md:col-span-5 reveal reveal-delay-1">
            <div class="bg-text-main text-canvas px-7 py-7 relative">
              <div class="absolute top-0 left-0 w-1.5 h-full bg-primary"></div>
              <div class="flex items-center justify-between mb-5">
                <span class="font-mono text-[10px] font-bold text-primary tracking-[0.25em] uppercase">/AT-A-GLANCE</span>
                <span class="font-mono text-[10px] font-bold text-canvas/40 tracking-[0.25em] uppercase">2024—25</span>
              </div>
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <div class="font-sans text-[36px] md:text-[44px] font-bold text-canvas leading-none tabular-nums">{{ String(stats.count).padStart(2, '0') }}</div>
                  <div class="font-mono text-[10px] text-canvas/50 font-bold tracking-[0.2em] uppercase mt-2">{{ t('projects.statProjects') }}</div>
                </div>
                <div class="border-l border-canvas/15 pl-4">
                  <div class="font-sans text-[36px] md:text-[44px] font-bold text-primary leading-none tabular-nums">{{ String(stats.locations).padStart(2, '0') }}</div>
                  <div class="font-mono text-[10px] text-canvas/50 font-bold tracking-[0.2em] uppercase mt-2">{{ t('projects.statLocations') }}</div>
                </div>
                <div class="border-l border-canvas/15 pl-4">
                  <div class="font-sans text-[20px] md:text-[24px] font-bold text-canvas leading-none tabular-nums">{{ stats.area }}</div>
                  <div class="font-mono text-[10px] text-canvas/50 font-bold tracking-[0.2em] uppercase mt-2">{{ t('projects.statArea') }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <main class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14">
      <!-- ═══ FILTER BAR ═══ -->
      <div class="mb-10 reveal">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 pb-5 border-b border-text-main">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em] mr-1">/FILTER</span>
            <button
              class="px-4 py-2 text-[11px] font-bold uppercase tracking-[0.15em] border transition-all duration-300 rounded-none"
              :class="selectedLocation === null
                ? 'bg-text-main text-canvas border-text-main'
                : 'bg-transparent text-text-secondary border-outline-variant hover:border-text-main hover:text-text-main'"
              @click="selectedLocation = null"
            >{{ t('projects.all') }}</button>
            <button
              v-for="loc in locations" :key="loc"
              class="px-4 py-2 text-[11px] font-bold uppercase tracking-[0.15em] border transition-all duration-300 rounded-none"
              :class="selectedLocation === loc
                ? 'bg-text-main text-canvas border-text-main'
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
            <span class="hidden sm:inline-flex items-center gap-1.5 font-mono text-[10px] text-text-muted font-bold tracking-[0.2em] uppercase tabular-nums px-3 py-2 bg-canvas border border-outline-variant">
              <span class="w-1.5 h-1.5 bg-primary"></span>
              {{ String(projects.length).padStart(2, '0') }} {{ t('projects.results') }}
            </span>
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
      <div v-else-if="projects?.length" class="grid grid-cols-12 gap-4">
        <router-link
          v-for="(project, i) in projects" :key="project.slug"
          :to="`/projects/${project.slug}`"
          class="group block relative overflow-hidden bg-text-main border border-text-main reveal"
          :class="bentoSpan(i)"
        >
          <!-- Image wrapper — controls card height via aspect ratio -->
          <div class="relative w-full overflow-hidden" :class="bentoAspect(i)">
            <img
              :src="project.hero_image" :alt="project.name"
              class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.04] transition-transform duration-[1400ms] ease-out"
              loading="lazy"
            >
            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-text-main via-text-main/30 to-text-main/0"></div>
            <div class="absolute inset-0 bg-text-main/0 group-hover:bg-text-main/20 transition-colors duration-700"></div>

            <!-- Index (giant) -->
            <div class="absolute top-3 left-3 font-sans text-[64px] md:text-[80px] font-bold text-canvas/15 leading-none select-none pointer-events-none tabular-nums">{{ paddedIndex(i) }}</div>

            <!-- Top right: status + arrow -->
            <div class="absolute top-4 right-4 flex items-center gap-2">
              <span class="inline-flex items-center gap-1.5 bg-olive/95 text-canvas px-2.5 py-1 text-[9px] font-mono font-bold tracking-[0.2em] uppercase">
                <span class="w-1 h-1 bg-canvas"></span>
                <span>{{ t('projects.completed') }}</span>
              </span>
              <span class="w-9 h-9 rounded-full border border-canvas/30 backdrop-blur-sm flex items-center justify-center group-hover:bg-primary group-hover:border-primary group-hover:rotate-[-45deg] transition-all duration-500">
                <span class="material-symbols-outlined text-canvas text-[14px]">arrow_forward</span>
              </span>
            </div>

            <!-- Bottom info -->
            <div class="absolute bottom-0 left-0 right-0 p-5 md:p-6">
              <div class="flex items-center gap-3 mb-3">
                <span class="font-mono text-[10px] font-bold text-primary tracking-[0.2em] uppercase">{{ project.period }}</span>
                <span class="w-6 h-px bg-canvas/30"></span>
                <span class="inline-flex items-center gap-1 font-mono text-[10px] font-bold text-canvas/70 tracking-[0.2em] uppercase">
                  <span class="material-symbols-outlined text-[12px]">location_on</span>
                  <span>{{ project.location }}</span>
                </span>
              </div>
            <h3
              class="font-sans text-canvas font-bold leading-[1.1] tracking-[-0.025em] group-hover:text-primary transition-colors duration-500"
              :class="bentoSpan(i).includes('col-span-8') ? 'text-[22px] md:text-[30px] lg:text-[36px]' : 'text-[16px] md:text-[18px]'"
            >
              {{ project.name }}
            </h3>
            <div v-if="project.area && bentoSpan(i).includes('col-span-8')" class="flex items-center gap-6 pt-4 mt-4 border-t border-canvas/15">
                <div>
                  <div class="font-mono text-[9px] text-canvas/50 font-bold tracking-[0.2em] uppercase mb-1">/AREA</div>
                  <div class="font-mono text-[14px] font-bold text-canvas tabular-nums">{{ project.area }}</div>
                </div>
                <div v-if="project.materials?.length">
                  <div class="font-mono text-[9px] text-canvas/50 font-bold tracking-[0.2em] uppercase mb-1">/MATERIALS</div>
                  <div class="font-mono text-[14px] font-bold text-primary tabular-nums">{{ project.materials.length }} {{ t('project.types') }}</div>
                </div>
              </div>
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
