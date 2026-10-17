<script setup lang="ts">
import { computed } from 'vue'
import { useSettings } from '../composables/useSettings'
import PageHeader from '../components/PageHeader.vue'
import CTABanner from '../components/CTABanner.vue'
import PdfFlipbook from '../components/PdfFlipbook.vue'
import { t } from '../i18n'
import { buildContactLink } from '../utils/contactLink'

const { settings, load } = useSettings()
load()

const inspectionFile = computed(() => settings.value?.['company.inspection_file']?.trim() || '/documents/ho-so-nang-luc-2026.pdf')

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
        <div v-if="inspectionFile" class="reveal">
          <div class="bg-surface-bright border border-outline-variant rounded-3xl shadow-sm overflow-hidden hover:shadow-[0_12px_40px_rgba(184,155,136,0.1)] transition-all duration-500">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-7 md:px-10 py-6 border-b border-outline-variant bg-surface-vlm">
              <span class="font-bold text-text-main flex items-center gap-3 text-lg">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                  <i class="fa text-primary text-xl fa-circle-check"></i>
                </div>
                Hồ sơ kiểm định CHƠN THÀNH
              </span>
              <div class="flex items-center gap-3 flex-shrink-0">
                <a :href="inspectionFile" target="_blank" rel="noopener noreferrer" class="btn bg-surface-bright border border-outline-variant text-text-main hover:border-primary hover:text-primary py-2.5 px-5 rounded-full font-bold text-[13px] flex items-center justify-center gap-2 transition-all duration-300">
                  <i class="fa text-[16px] fa-up-right-from-square"></i>
                  Xem trên trình duyệt
                </a>
                <a :href="inspectionFile" download="ho-so-kiem-dinh-chon-thanh.pdf" class="btn btn-primary py-2.5 px-5 flex items-center justify-center gap-2 group/btn btn-magnetic">
                  <i class="fa text-[18px] group-hover/btn:-translate-y-0.5 transition-transform fa-download"></i>
                  Tải xuống
                </a>
              </div>
            </div>
            <div class="p-5 md:p-8 bg-surface-vlm">
              <div class="w-full h-[75vh] md:h-[85vh] rounded-2xl border border-outline-variant bg-white overflow-hidden relative shadow-inner">
                <PdfFlipbook :pdf-url="inspectionFile" title="Hồ sơ kiểm định CHƠN THÀNH" hide-close hide-header />
              </div>
            </div>
          </div>
        </div>

        <div v-else class="max-w-3xl mx-auto text-center reveal">
          <div class="border border-outline-variant p-14 bg-surface-bright shadow-sm rounded-3xl flex flex-col items-center hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 glow-card">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center mb-6">
              <i class="fa text-[32px] text-primary-deep fa-file-text"></i>
            </div>
            <h2 class="text-[24px] text-text-main font-bold mb-4">Yêu cầu hồ sơ kiểm định</h2>
            <p class="text-text-secondary mb-8 text-[16px] leading-relaxed">Liên hệ với chúng tôi để nhận hồ sơ kiểm định chi tiết cho sản phẩm và dự án của bạn.</p>
            <router-link
              :to="buildContactLink({ subject: 'certificate' })"
              class="btn btn-primary btn-magnetic inline-flex items-center gap-2 group"
            >
              <i class="fa text-[18px] group-hover:translate-y-0.5 transition-transform duration-300 fa-download"></i>
              Yêu cầu hồ sơ
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <CTABanner />
  </div>
</template>
