<script setup lang="ts">
import { computed } from 'vue'
import { useSettings } from '../composables/useSettings'
import PageHeader from '../components/PageHeader.vue'
import CTABanner from '../components/CTABanner.vue'
import { t } from '../i18n'

const { settings, load } = useSettings()
load()

const inspectionFile = computed(() => settings.value?.['company.inspection_file']?.trim() || '')

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.about'), to: '/about' },
  { label: t('cert.title') }
])
</script>

<template>
  <div>
    <PageHeader :title="t('cert.title')" :breadcrumbs="breadcrumbs" />

    <section class="py-16 md:py-20 bg-surface-bright">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-12 reveal">
          <span class="kicker mb-4 block">Documentation</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-bold tracking-tight mb-5">Hồ sơ kiểm định trực tuyến</h2>
          <p class="text-text-secondary max-w-2xl mx-auto text-[16px]">Tải xuống hoặc xem trực tuyến hồ sơ kiểm định chi tiết.</p>
        </div>

        <div v-if="inspectionFile" class="reveal">
          <div class="bg-surface-bright border border-outline-variant rounded-3xl shadow-sm overflow-hidden hover:shadow-[0_12px_40px_rgba(184,155,136,0.1)] transition-all duration-500">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-7 md:px-10 py-6 border-b border-outline-variant bg-surface-vlm">
              <span class="font-bold text-text-main flex items-center gap-3 text-lg">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                  <span class="material-symbols-outlined text-primary text-xl">verified_user</span>
                </div>
                Hồ sơ kiểm định CHƠN THÀNH
              </span>
              <div class="flex items-center gap-3 flex-shrink-0">
                <a :href="inspectionFile" target="_blank" rel="noopener noreferrer" class="btn btn-outline py-2.5 px-5 flex items-center justify-center gap-2 group/btn btn-magnetic">
                  <span class="material-symbols-outlined text-[18px] group-hover/btn:-translate-y-0.5 transition-transform">open_in_new</span>
                  Xem toàn màn hình
                </a>
                <a :href="inspectionFile" download="ho-so-kiem-dinh-chon-thanh.pdf" class="btn btn-primary py-2.5 px-5 flex items-center justify-center gap-2 group/btn btn-magnetic">
                  <span class="material-symbols-outlined text-[18px] group-hover/btn:-translate-y-0.5 transition-transform">download</span>
                  Tải xuống
                </a>
              </div>
            </div>
            <div class="p-5 md:p-8 bg-surface-vlm">
              <iframe :src="inspectionFile" class="w-full h-[75vh] rounded-2xl border border-outline-variant bg-white" title="Hồ sơ kiểm định"></iframe>
            </div>
          </div>
        </div>

        <div v-else class="max-w-3xl mx-auto text-center reveal">
          <div class="border border-outline-variant p-14 bg-surface-bright shadow-sm rounded-3xl flex flex-col items-center hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 glow-card">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center mb-6">
              <span class="material-symbols-outlined text-[32px] text-primary-deep">description</span>
            </div>
            <h2 class="text-[24px] text-text-main font-bold mb-4">Yêu cầu hồ sơ kiểm định</h2>
            <p class="text-text-secondary mb-8 text-[16px] leading-relaxed">Liên hệ với chúng tôi để nhận hồ sơ kiểm định chi tiết cho sản phẩm và dự án của bạn.</p>
            <router-link to="/contact" class="btn btn-primary btn-magnetic inline-flex items-center gap-2 group">
              <span class="material-symbols-outlined text-[18px] group-hover:translate-y-0.5 transition-transform duration-300">download</span>
              Yêu cầu hồ sơ
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <CTABanner />
  </div>
</template>
