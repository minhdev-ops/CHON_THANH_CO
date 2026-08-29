<script setup lang="ts">
import { computed } from 'vue'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import { useSettings } from '../composables/useSettings'
import ErrorState from '../components/ErrorState.vue'
import PageHeader from '../components/PageHeader.vue'
import SectionHeader from '../components/SectionHeader.vue'
import CTABanner from '../components/CTABanner.vue'
import { t } from '../i18n'
import { fallbackCertificates } from '../types/fallback'

const { data: certificates, loading, error: certError, load: loadCertificates } = useApiData(
  async () => (await api.certificates()).data,
  () => fallbackCertificates
)

const { settings, load } = useSettings()
load()

const inspectionFile = computed(() => settings.value?.['company.inspection_file']?.trim() || '')

const inspectionProcess = [
  { step: '01', icon: 'science', title: 'Lấy mẫu kiểm định', desc: 'Mẫu sản phẩm được lấy ngẫu nhiên tại nhà máy, có dấu niêm phong của bên thứ 3 (SGS, Quatest).' },
  { step: '02', icon: 'speed', title: 'Thí nghiệm cường độ', desc: 'Kiểm tra cường độ kéo, độ giãn dài, độ bền xuyên thủng, hệ số thấm theo TCVN 9844:2013 và ASTM.' },
  { step: '03', icon: 'biotech', title: 'Phân tích thành phần', desc: 'Phân tích thành phần nguyên liệu, độ bền UV, độ bền hóa học bằng thiết bị phòng thí nghiệm hiện đại.' },
  { step: '04', icon: 'description', title: 'Cấp chứng nhận', desc: 'Cấp chứng nhận CO/CQ, kết quả thí nghiệm và tem chất lượng cho từng lô hàng.' },
]

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
        <div class="max-w-2xl mx-auto text-center reveal">
          <span class="kicker mb-4 block">Process</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-bold tracking-tight mb-5">Quy trình kiểm định 4 bước</h2>
          <p class="text-text-secondary text-[16px] md:text-[17px] leading-relaxed">Mọi sản phẩm CHƠN THÀNH đều được kiểm định nghiêm ngặt trước khi xuất xưởng, đảm bảo đạt tiêu chuẩn quốc tế.</p>
        </div>

        <div class="mt-16 relative">
          <div class="hidden md:block absolute top-1/2 left-0 right-0 h-px bg-outline-variant -translate-y-1/2 z-0"></div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10 stagger-grid">
            <div v-for="(item, i) in inspectionProcess" :key="i"
              class="bg-surface-bright border border-outline-variant rounded-3xl p-9 shadow-sm hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 flex items-start gap-7 group card-shine glow-card">
              <div class="relative flex-shrink-0">
                <span class="text-[56px] md:text-[64px] font-bold leading-none text-outline-variant/40 group-hover:text-primary/15 transition-colors duration-500 select-none">{{ item.step }}</span>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center absolute bottom-0 right-0 group-hover:scale-110 group-hover:shadow-[0_4px_16px_rgba(184,155,136,0.2)] transition-all duration-500">
                  <span class="material-symbols-outlined text-[22px] text-primary-deep" style="font-variation-settings: 'FILL' 1;">{{ item.icon }}</span>
                </div>
              </div>
              <div class="pl-2">
                <h3 class="font-extrabold text-text-main text-[19px] mb-3 group-hover:text-primary transition-colors duration-300">{{ item.title }}</h3>
                <p class="text-[15px] text-text-secondary leading-relaxed">{{ item.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 md:py-28 bg-canvas border-b border-outline-variant">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop text-center">
        <div class="text-center mb-12 reveal">
          <span class="kicker mb-4 block">Certifications</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-bold tracking-tight mb-5">Các chứng nhận</h2>
          <p class="text-text-secondary max-w-2xl mx-auto text-[16px]">Hệ thống chứng nhận đầy đủ khẳng định chất lượng và uy tín của CHƠN THÀNH trên thị trường quốc tế.</p>
        </div>

        <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div v-for="i in 4" :key="i" class="h-[300px] bg-surface-bright border border-outline-variant animate-shimmer rounded-3xl"></div>
        </div>
        <div v-else-if="certError && !certificates?.length" class="bg-surface-bright border border-outline-variant rounded-3xl">
          <ErrorState :message="certError" @retry="loadCertificates" />
        </div>
        <div v-else-if="certificates?.length" class="grid grid-cols-2 md:grid-cols-4 gap-8 stagger-grid">
          <router-link v-for="cert in certificates" :key="cert.slug" to="/certificates"
            class="aspect-[3/4] bg-surface-bright border border-outline-variant p-7 flex flex-col items-center justify-center shadow-sm hover:shadow-[0_16px_48px_rgba(184,155,136,0.15)] hover:border-primary/40 transition-all duration-500 rounded-3xl group card-shine glow-card">
            <div class="w-full h-3/4 mb-4 flex items-center justify-center group-hover:scale-105 transition-transform duration-700 bg-surface-vlm rounded-2xl border border-outline-variant/50 p-4">
              <img :src="cert.image" :alt="cert.name" class="max-w-full max-h-full object-contain" loading="lazy">
            </div>
            <span class="text-[13px] font-bold text-text-secondary text-center group-hover:text-primary transition-colors duration-300">{{ cert.name }}</span>
          </router-link>
        </div>
        <router-link to="/certificates" class="btn btn-primary btn-magnetic inline-flex mt-12 items-center gap-2 group">
          <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform duration-300">assignment_turned_in</span>
          Xem tất cả
        </router-link>
      </div>
    </section>

    <section class="py-20 md:py-28 bg-surface-bright">
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
              <iframe :src="inspectionFile" class="w-full h-[70vh] rounded-2xl border border-outline-variant bg-white" title="Hồ sơ kiểm định"></iframe>
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
