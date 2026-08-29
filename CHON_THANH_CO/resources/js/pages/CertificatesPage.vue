<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import { useFocusTrap } from '../composables/useFocusTrap'
import type { Certificate } from '../types'
import PageHeader from '../components/PageHeader.vue'
import ErrorState from '../components/ErrorState.vue'
import CTABanner from '../components/CTABanner.vue'
import { t } from '../i18n'
import { fallbackCertificates } from '../types/fallback'

const { data: certificates, loading, error, load } = useApiData(
  async () => (await api.certificates()).data,
  () => fallbackCertificates
)

const viewer = ref<Certificate | null>(null)
const viewerEl = ref<HTMLElement | null>(null)
const viewerOpen = ref(false)

const closeViewer = () => { viewer.value = null }
const onKeydown = (e: KeyboardEvent) => { if (e.key === 'Escape') closeViewer() }
onMounted(() => document.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))
watch(viewer, (v) => {
  viewerOpen.value = !!v
  document.body.style.overflow = v ? 'hidden' : ''
})
useFocusTrap(viewerEl, viewerOpen)

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.certificates') }
])
</script>

<template>
  <div>
    <PageHeader :title="t('certs.title')" :breadcrumbs="breadcrumbs" />

    <section class="relative py-16 md:py-20 overflow-hidden">
      <div class="absolute inset-0 mesh-bg opacity-40"></div>
      <div class="relative max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop w-full">
        <div class="text-center max-w-2xl mx-auto mb-14 reveal">
          <span class="kicker mb-4 block">Our Certificates</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-extrabold tracking-tight mb-5">Chứng nhận chất lượng</h2>
          <p class="text-text-secondary text-[16px] md:text-[17px] leading-relaxed">Các chứng chỉ uy tín khẳng định chất lượng vật liệu CHƠN THÀNH trên thị trường quốc tế.</p>
        </div>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="i in 6" :key="i" class="h-[420px] bg-surface-vlm animate-shimmer rounded-3xl border border-outline-variant"></div>
        </div>

        <div v-else-if="error && !certificates?.length" class="bg-surface-bright border border-outline-variant rounded-3xl">
          <ErrorState :message="error" @retry="load" />
        </div>

        <div v-else-if="certificates?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 stagger-grid">
          <div v-for="(cert, i) in certificates" :key="cert.slug"
            class="bg-surface-bright border border-outline-variant rounded-3xl shadow-sm hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 flex flex-col overflow-hidden group card-shine glow-card reveal"
            :class="`reveal-delay-${(i % 3) + 1}`">
            <div class="h-60 bg-surface-vlm border-b border-outline-variant relative overflow-hidden flex items-center justify-center p-8">
              <img :src="cert.image" :alt="cert.name"
                class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform duration-700 ease-out" loading="lazy">
              <div class="absolute inset-0 bg-primary/0 group-hover:bg-primary/5 transition-colors duration-500 pointer-events-none"></div>
              <div class="absolute top-4 right-4 w-12 h-12 rounded-full bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center opacity-0 group-hover:opacity-100 translate-y-3 group-hover:translate-y-0 transition-all duration-500 shadow-lg">
                <span class="material-symbols-outlined text-primary-deep text-lg">verified</span>
              </div>
            </div>
            <div class="p-8 flex flex-col flex-grow">
              <h2 class="font-extrabold text-text-main text-[20px] mb-3 group-hover:text-primary transition-colors duration-300">{{ cert.name }}</h2>
              <p class="text-[14px] text-text-secondary mb-8 flex-grow leading-relaxed">{{ cert.description }}</p>
              <div v-if="cert.file" class="flex flex-col sm:flex-row gap-3">
                <button @click="viewer = cert" class="flex-1 bg-primary text-white hover:bg-primary-dark py-3 px-5 rounded-full font-bold text-[14px] flex items-center justify-center gap-2 transition-all duration-300 hover:shadow-lg">
                  <span class="material-symbols-outlined text-[18px]">visibility</span> {{ t('certs.view') }}
                </button>
                <a :href="cert.file" :download="`${cert.slug}.pdf`" class="flex-1 border border-outline-variant text-text-main hover:border-primary hover:text-primary py-3 px-5 rounded-full font-bold text-[14px] flex items-center justify-center gap-2 transition-all duration-300">
                  <span class="material-symbols-outlined text-[18px]">download</span> {{ t('certs.download') }}
                </a>
              </div>
              <router-link v-else to="/contact" class="flex-1 bg-primary text-white hover:bg-primary-dark py-3 px-5 rounded-full font-bold text-[14px] flex items-center justify-center gap-2 transition-all duration-300 hover:shadow-lg">
                <span class="material-symbols-outlined text-[18px]">download</span> {{ t('certs.request') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="bg-surface-vlm py-16 md:py-20 px-margin-mobile md:px-margin-desktop border-t border-outline-variant relative overflow-hidden">
      <div class="absolute inset-0 mesh-bg opacity-50"></div>
      <div class="absolute inset-0 noise-overlay pointer-events-none"></div>
      <div class="max-w-max-width mx-auto flex flex-col md:flex-row items-center justify-between gap-8 reveal relative z-10">
        <div class="text-center md:text-left">
          <h2 class="text-[28px] md:text-[34px] text-text-main font-extrabold mb-3 tracking-tight">{{ t('certs.ctaTitle') }}</h2>
          <p class="text-[16px] text-text-secondary leading-relaxed">{{ t('certs.ctaText') }}</p>
        </div>
        <router-link to="/contact" class="btn bg-primary text-white hover:bg-primary-dark rounded-full inline-flex items-center gap-2 group shrink-0 py-4 px-8 text-[15px] font-bold shadow-[0_4px_20px_rgba(184,155,136,0.3)] transition-all duration-300 hover:shadow-lg">
          <span class="material-symbols-outlined text-[18px] group-hover:-translate-y-0.5 transition-transform duration-300">support_agent</span> {{ t('certs.ctaButton') }}
        </router-link>
      </div>
    </section>

    <!-- Viewer Modal -->
    <div v-if="viewer" ref="viewerEl" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-8" @click.self="closeViewer">
      <div class="absolute inset-0 bg-black/80 backdrop-blur-md animate-fade-in-up"></div>
      <div class="relative bg-surface rounded-3xl shadow-2xl w-full max-w-5xl max-h-full flex flex-col overflow-hidden border border-outline-variant animate-scale-in">
        <div class="flex items-center justify-between gap-4 px-6 md:px-8 py-5 border-b border-outline-variant bg-surface-bright">
          <h3 class="font-bold text-lg text-text-main truncate">{{ viewer.name }}</h3>
          <div class="flex items-center gap-3 flex-shrink-0">
            <a :href="viewer.file ?? ''" :download="`${viewer.slug}.pdf`" class="btn bg-primary text-white hover:bg-primary-dark py-2.5 px-5 rounded-full font-bold text-[13px] flex items-center justify-center gap-2 transition-all duration-300">
              <span class="material-symbols-outlined text-[16px]">download</span> {{ t('certs.download') }}
            </a>
            <button type="button" class="w-11 h-11 rounded-full flex items-center justify-center bg-surface-vlm border border-outline-variant hover:bg-outline-variant hover:text-primary transition-all duration-300" :aria-label="t('common.close')" @click="closeViewer">
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>
        </div>
        <div class="flex-1 min-h-[70vh] bg-surface-vlm p-4 md:p-8">
          <iframe :src="viewer.file ?? ''" class="w-full h-full min-h-[70vh] rounded-2xl border border-outline-variant bg-white" frameborder="0" :title="t('certs.viewTitle')"></iframe>
        </div>
      </div>
    </div>

    <CTABanner />
  </div>
</template>
