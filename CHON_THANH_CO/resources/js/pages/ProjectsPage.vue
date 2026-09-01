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

// Standard grid, no bento logic needed
</script>

<template>
  <div>
    <PageHeader :title="t('nav.projects')" :breadcrumbs="breadcrumbs" />

    <main class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14 animate-fade-in-up">
      <!-- ═══ FILTER BAR ═══ -->
      <div class="mb-10 reveal">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-end justify-between gap-6 pb-0 border-b border-outline-variant">
          <div class="flex items-center gap-6 overflow-x-auto no-scrollbar whitespace-nowrap">
            <button
              class="pb-4 text-[14px] font-bold transition-colors relative"
              :class="selectedLocation === null ? 'text-primary' : 'text-text-secondary hover:text-text-main'"
              @click="selectedLocation = null"
            >
              {{ t('projects.all') }}
              <div v-if="selectedLocation === null" class="absolute bottom-0 left-0 w-full h-[3px] bg-primary rounded-t-sm"></div>
            </button>
            <button
              v-for="loc in locations" :key="loc"
              class="pb-4 text-[14px] font-bold transition-colors relative"
              :class="selectedLocation === loc ? 'text-primary' : 'text-text-secondary hover:text-text-main'"
              @click="selectedLocation = loc"
            >
              {{ loc }}
              <div v-if="selectedLocation === loc" class="absolute bottom-0 left-0 w-full h-[3px] bg-primary rounded-t-sm"></div>
            </button>
          </div>
          <div class="flex items-center gap-4 mb-4 lg:mb-3">
            <div class="relative w-full lg:w-72">
              <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-text-muted text-[18px]">search</span>
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="t('projects.searchPlaceholder')"
                class="bg-surface-vlm border border-outline-variant/60 shadow-inner pl-10 pr-4 py-2.5 text-[13px] text-text-main outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all w-full rounded-md placeholder:text-text-muted/60"
              >
            </div>
            <div class="hidden sm:flex flex-col items-end px-3 border-l border-outline-variant h-full justify-center">
              <span class="font-black text-[16px] text-text-main leading-none">{{ String(projects.length).padStart(2, '0') }}</span>
              <span class="text-[9px] font-bold text-text-muted uppercase tracking-widest mt-0.5">Kết quả</span>
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

      <!-- ═══ PROFESSIONAL PROJECT GRID ═══ -->
      <div v-else-if="projects?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 stagger-grid">
        <router-link
          v-for="(project, i) in projects" :key="project.slug"
          :to="`/projects/${project.slug}`"
          class="group flex flex-col bg-white border border-outline-variant/60 shadow-sm hover:shadow-md transition-all duration-300 reveal rounded-sm overflow-hidden"
          :class="[`reveal-delay-${(i%3)+1}`]"
        >
          <!-- Image wrapper -->
          <div class="relative w-full overflow-hidden bg-canvas shrink-0 aspect-[4/3] border-b border-outline-variant/60">
            <img
              :src="project.hero_image" :alt="project.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
              loading="lazy"
            >
            <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-md px-2.5 py-1 flex items-center gap-2 shadow-sm rounded-sm">
              <span class="w-1.5 h-1.5 bg-primary animate-pulse"></span>
              <span class="text-[9px] font-bold text-text-main tracking-[0.2em] uppercase">{{ t('projects.completed') }}</span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-6 md:p-8 flex flex-col flex-grow relative">
            <div class="flex items-center gap-3 mb-3">
              <span class="text-primary text-[10px] font-extrabold uppercase tracking-[0.2em]">{{ project.period }}</span>
              <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
              <span class="text-[11px] font-bold text-text-muted line-clamp-1 uppercase tracking-wider">{{ project.location }}</span>
            </div>
            
            <h3 class="font-bold text-[18px] md:text-[20px] text-text-main mb-3 group-hover:text-primary transition-colors duration-300 leading-snug line-clamp-2">
              {{ project.name }}
            </h3>
            
            <p v-if="project.area || project.materials" class="text-text-secondary text-[13.5px] leading-relaxed line-clamp-2 mb-6 flex-grow">
              <span v-if="project.area">Quy mô: <strong>{{ project.area }}</strong>. </span>
              <span v-if="project.materials">Sử dụng <strong>{{ project.materials.length }}</strong> loại vật tư.</span>
            </p>
            <div v-else class="flex-grow mb-6"></div>

            <div class="mt-auto flex items-center pt-5 border-t border-outline-variant/30">
              <span class="inline-flex items-center gap-1.5 font-bold text-[12px] text-primary group-hover:text-primary-deep transition-colors duration-300 uppercase tracking-widest relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-px after:bg-primary group-hover:after:w-full after:transition-all after:duration-300">
                Chi tiết dự án <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </span>
            </div>
          </div>
        </router-link>
      </div>

      <!-- ═══ LOAD MORE ═══ -->
      <div v-if="hasMore && !projectsLoading" class="mt-14 flex justify-center">
        <button type="button" class="btn btn-outline py-3.5 px-8 flex items-center justify-center gap-2 group btn-magnetic rounded-full font-bold shadow-sm" :disabled="loadingMore" @click="loadMore()">
          <span>{{ loadingMore ? t('common.loading') : t('common.loadMore') }}</span>
          <span v-if="!loadingMore" class="material-symbols-outlined text-[20px] group-hover:translate-y-1 transition-transform duration-300">keyboard_double_arrow_down</span>
        </button>
      </div>
    </main>

    <CTABanner
      :title="t('projects.ctaTitle')"
      :linkLabel="t('projects.ctaButton')"
    />
  </div>
</template>
