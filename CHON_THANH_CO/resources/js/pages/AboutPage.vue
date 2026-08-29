<script setup lang="ts">
import { computed } from 'vue'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import { useSettings } from '../composables/useSettings'
import StatCard from '../components/StatCard.vue'
import ErrorState from '../components/ErrorState.vue'
import { t, locale } from '../i18n'
import { getYearsOfExperience } from '../utils/experience'
import type { Certificate } from '../types'
import PageHeader from '../components/PageHeader.vue'
import SectionHeader from '../components/SectionHeader.vue'
import { fallbackTimeline, fallbackStats, fallbackCertificates } from '../types/fallback'

const { data: home } = useApiData(() => api.home(), () => ({
  banners: [], stats: fallbackStats, why_choose_us: [], featured_products: [], latest_projects: [],
}))
const { data: certificates, error: certError, load: loadCertificates } = useApiData(
  () => api.certificates(),
  () => ({ data: fallbackCertificates as Certificate[] })
)
const { data: timelineData, error: timelineError, load: loadTimeline } = useApiData(
  () => api.timeline(),
  () => ({ data: fallbackTimeline as { year: string; description: string }[] })
)
const { settings, load } = useSettings()
load()

const timeline = computed(() => {
  const items = timelineData.value?.data ?? fallbackTimeline
  if (items.length) return items.map((item) => ({ year: item.year, description: item.description }))
  return fallbackTimeline
})

const suffix = computed(() => (locale.value === 'en' ? '_en' : '_vi'))
const historyParagraphs = computed(() => {
  const raw = settings.value?.[`about.history${suffix.value}`]?.trim()
  if (raw) return raw.split(/\n{2,}/).map((p) => p.trim()).filter(Boolean)
  return [
    t('about.historyP1'),
    t('about.historyP2', { years: getYearsOfExperience() }),
    t('about.historyP3'),
  ]
})
const missionText = computed(() => settings.value?.[`about.mission${suffix.value}`]?.trim() || 'Trở thành nhà cung cấp vật liệu địa kỹ thuật hàng đầu Việt Nam và khu vực ASEAN, mang đến giải pháp tối ưu về chi phí – kỹ thuật – tiến độ cho mọi dự án hạ tầng, góp phần xây dựng quê hương và bảo vệ môi trường bền vững.')
const visionText = computed(() => settings.value?.[`about.vision${suffix.value}`]?.trim() || 'Đến năm 2030, CHƠN THÀNH sẽ là tập đoàn vật liệu địa kỹ thuật top đầu Đông Nam Á, với 3 nhà máy sản xuất, hệ thống phân phối tại 10 quốc gia và doanh thu vượt 1.000 tỷ đồng/năm, đồng thời là đối tác chiến lược của các tổng thầu lớn trong nước và quốc tế.')

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.about') }
])

// Core values
const coreValues = [
  { icon: 'handshake', title: 'Chữ tín', desc: 'Cam kết chất lượng sản phẩm, tiến độ giao hàng và giá cả hợp lý — đặt lợi ích khách hàng lên hàng đầu.' },
  { icon: 'workspace_premium', title: 'Chất lượng', desc: 'Toàn bộ sản phẩm đạt chuẩn ISO 9001:2015, TCVN 9844:2013, tiêu chuẩn EN, ASTM. Kiểm định bởi tổ chức độc lập.' },
  { icon: 'lightbulb', title: 'Đổi mới', desc: 'Liên tục cập nhật công nghệ sản xuất, phát triển sản phẩm mới theo tiêu chuẩn quốc tế.' },
  { icon: 'eco', title: 'Bền vững', desc: 'Cam kết phát triển bền vững: giảm phát thải, sử dụng vật liệu tái chế, bảo vệ môi trường.' },
]

// Milestones
const milestones = [
  { value: '21+', label: 'Năm kinh nghiệm', icon: 'workspace_premium' },
  { value: '8.000', label: 'Tấn/năm công suất', icon: 'factory' },
  { value: '500+', label: 'Công trình đã tham gia', icon: 'engineering' },
  { value: '120+', label: 'Đối tác trong nước & quốc tế', icon: 'handshake' },
]
</script>

<template>
  <div>
    <!-- Hero -->
    <PageHeader :title="t('nav.about')" :breadcrumbs="breadcrumbs" />

    <!-- History & Timeline -->
    <section class="py-16 md:py-20 bg-surface-bright relative overflow-hidden">
      <div class="absolute inset-0 dot-pattern opacity-[0.02] pointer-events-none"></div>

      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16 lg:gap-24 items-start">
          <div class="reveal-left order-2 md:order-1">
            <div class="mb-8">
              <span class="kicker mb-4 block">{{ t('about.history') }}</span>
              <h2 class="text-[32px] md:text-[42px] text-text-main font-bold leading-[1.15] tracking-tight">Hành trình phát triển</h2>
            </div>
            <div class="text-text-secondary space-y-6 leading-[1.8] text-[16px] md:text-[17px]">
              <p v-for="(para, i) in historyParagraphs" :key="i" class="relative pl-6 border-l-2 border-primary/30 hover:border-primary transition-colors duration-500">{{ para }}</p>
            </div>
          </div>
          <div class="order-1 md:order-2 reveal-right">
            <div v-if="timelineError && !timeline.length" class="mb-8">
              <ErrorState :message="timelineError" @retry="loadTimeline" />
            </div>
            <div class="relative ml-4 md:ml-0">
              <div class="absolute left-0 top-0 bottom-0 w-[2px] bg-gradient-to-b from-primary/20 via-primary to-primary/20"></div>

              <div class="space-y-6 py-4">
                <div v-for="(item, i) in timeline" :key="i" class="relative pl-12 reveal" :class="`reveal-delay-${(i % 6) + 1}`">
                  <div class="absolute left-0 top-3 w-5 h-5 -translate-x-[9px] rounded-full border-[3px] border-[#B89B88] bg-surface-bright shadow-[0_0_0_4px_rgba(184,155,136,0.1)] z-10 transition-all duration-500 hover:shadow-[0_0_0_8px_rgba(184,155,136,0.2)] hover:scale-125"></div>
                  <div class="bg-surface-bright border border-outline-variant rounded-2xl p-6 md:p-7 hover:shadow-[0_12px_40px_rgba(184,155,136,0.12)] hover:border-[#B89B88] transition-all duration-500 group card-shine">
                    <div class="flex items-center gap-3 mb-3">
                      <span class="text-[13px] font-bold text-primary bg-primary/8 px-3 py-1 rounded-full uppercase tracking-wider">{{ item.year }}</span>
                    </div>
                    <p class="text-[15px] text-text-secondary leading-relaxed">{{ item.description }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-16 md:py-24 relative overflow-hidden mesh-bg">
      <div class="absolute inset-0 noise-overlay pointer-events-none"></div>
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
          <div class="bg-surface-bright p-10 md:p-12 border border-outline-variant shadow-sm rounded-3xl hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-[#B89B88] transition-all duration-500 relative overflow-hidden group reveal-left glow-card">
            <div class="absolute top-0 right-0 w-40 h-40 bg-primary/8 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="absolute -bottom-8 -right-8 text-[140px] font-bold text-primary/[0.04] leading-none select-none pointer-events-none group-hover:text-primary/[0.08] transition-colors duration-500">M</div>

            <div class="relative z-10">
              <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#B89B88]/15 to-[#B89B88]/5 flex items-center justify-center mb-7 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                <span class="material-symbols-outlined text-3xl text-primary" style="font-variation-settings: 'FILL' 1;">flag</span>
              </div>
              <h2 class="text-[28px] md:text-[32px] text-text-main font-bold mb-5 tracking-tight">{{ t('about.mission') }}</h2>
              <p class="text-text-secondary leading-[1.8] text-[16px] md:text-[17px]">{{ missionText }}</p>
            </div>
          </div>

          <div class="bg-surface-bright p-10 md:p-12 border border-outline-variant shadow-sm rounded-3xl hover:shadow-[0_20px_60px_rgba(140,107,88,0.15)] hover:border-[#8C6B58] transition-all duration-500 relative overflow-hidden group reveal-right glow-card">
            <div class="absolute top-0 right-0 w-40 h-40 bg-[#8C6B58]/8 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="absolute -bottom-8 -right-8 text-[140px] font-bold text-[#8C6B58]/[0.04] leading-none select-none pointer-events-none group-hover:text-[#8C6B58]/[0.08] transition-colors duration-500">V</div>

            <div class="relative z-10">
              <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#8C6B58]/15 to-[#8C6B58]/5 flex items-center justify-center mb-7 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                <span class="material-symbols-outlined text-3xl text-[#8C6B58]" style="font-variation-settings: 'FILL' 1;">visibility</span>
              </div>
              <h2 class="text-[28px] md:text-[32px] text-text-main font-bold mb-5 tracking-tight">{{ t('about.vision') }}</h2>
              <p class="text-text-secondary leading-[1.8] text-[16px] md:text-[17px]">{{ visionText }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Core Values -->
    <section class="py-16 md:py-24 bg-canvas">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-14 reveal">
          <SectionHeader align="center" kicker="Giá trị cốt lõi" title="Nguyên tắc hoạt động của chúng tôi" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 stagger-grid">
          <div v-for="(v, i) in coreValues" :key="i"
            class="bg-surface-bright border border-outline-variant rounded-3xl p-8 text-center shadow-sm hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 group card-shine glow-card">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center mx-auto mb-5 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
              <span class="material-symbols-outlined text-[32px] text-primary-deep" style="font-variation-settings: 'FILL' 1;">{{ v.icon }}</span>
            </div>
            <h3 class="font-extrabold text-text-main text-[19px] mb-3 tracking-tight">{{ v.title }}</h3>
            <p class="text-text-secondary text-[14px] leading-relaxed">{{ v.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats / Milestones -->
    <section class="py-16 md:py-24 bg-surface-bright relative overflow-hidden">
      <div class="absolute inset-0 grid-pattern opacity-[0.03] pointer-events-none"></div>
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop text-center relative z-10">
        <div class="text-center mb-16 reveal">
          <span class="kicker mb-4 block">{{ t('about.capabilities') }}</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-bold tracking-tight">Năng lực của chúng tôi</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10 text-left stagger-grid">
          <div v-for="(m, i) in milestones" :key="i"
            class="bg-surface-bright rounded-2xl p-6 shadow-sm border border-outline-variant hover:shadow-xl hover:border-primary/30 transition-all duration-500 group card-shine">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-all duration-500">
              <span class="material-symbols-outlined text-[22px] text-primary-deep group-hover:text-white transition-colors">{{ m.icon }}</span>
            </div>
            <div class="text-[32px] font-extrabold text-text-main mb-1 tabular-nums">{{ m.value }}</div>
            <div class="text-[11px] font-bold text-text-muted uppercase tracking-[0.15em]">{{ m.label }}</div>
          </div>
        </div>
        <div v-if="home?.stats?.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-left stagger-grid">
          <StatCard v-for="(stat, i) in home.stats" :key="i" :icon="stat.icon" :value="stat.value" :label="stat.label"
            class="shadow-sm hover:shadow-[0_16px_48px_rgba(184,155,136,0.15)] rounded-2xl card-shine hover-glow-border border border-transparent" />
        </div>
      </div>
    </section>

    <!-- Two Factories -->
    <section class="py-16 md:py-24 bg-canvas relative overflow-hidden">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-14 reveal">
          <SectionHeader align="center" kicker="Nhà máy sản xuất" title="Hệ thống 2 nhà máy trên toàn quốc" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 stagger-grid">
          <div class="bg-surface-bright border border-outline-variant rounded-3xl overflow-hidden shadow-sm hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 group card-shine">
            <div class="aspect-[16/9] overflow-hidden bg-surface-vlm">
              <img src="/images/products/gabion-1.jpg" alt="Nhà máy Rọ đá Á Châu" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
            </div>
            <div class="p-8">
              <div class="flex items-center gap-3 mb-4">
                <span class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                  <span class="material-symbols-outlined text-primary text-[24px]">factory</span>
                </span>
                <div>
                  <h3 class="font-extrabold text-text-main text-[20px] leading-tight">Nhà máy Rọ đá Á Châu</h3>
                  <span class="text-text-muted text-[12px] font-bold uppercase tracking-wider">Hóc Môn, TP.HCM</span>
                </div>
              </div>
              <p class="text-text-secondary text-[15px] leading-relaxed mb-5">Chuyên sản xuất rọ đá mạ kẽm nhúng nóng, lưới B40, dây kẽm gai, lưới thép hàn. Công suất 3.000 tấn/năm.</p>
              <ul class="space-y-2 text-[14px] text-text-main">
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">check_circle</span> Công suất 3.000 tấn/năm</li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">check_circle</span> Mạ kẽm nhúng nóng ASTM A975</li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">check_circle</span> Giao hàng toàn quốc & xuất khẩu</li>
              </ul>
            </div>
          </div>
          <div class="bg-surface-bright border border-outline-variant rounded-3xl overflow-hidden shadow-sm hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 group card-shine">
            <div class="aspect-[16/9] overflow-hidden bg-surface-vlm">
              <img src="/images/projects/highway-2.jpg" alt="Nhà máy Lưới thép Tiên Phong" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
            </div>
            <div class="p-8">
              <div class="flex items-center gap-3 mb-4">
                <span class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                  <span class="material-symbols-outlined text-primary text-[24px]">precision_manufacturing</span>
                </span>
                <div>
                  <h3 class="font-extrabold text-text-main text-[20px] leading-tight">Nhà máy Lưới thép Tiên Phong</h3>
                  <span class="text-text-muted text-[12px] font-bold uppercase tracking-wider">Đà Nẵng</span>
                </div>
              </div>
              <p class="text-text-secondary text-[15px] leading-relaxed mb-5">Chuyên sản xuất vải địa kỹ thuật không dệt, vải dệt, lưới địa kỹ thuật polyester. Công suất 5.000 tấn/năm.</p>
              <ul class="space-y-2 text-[14px] text-text-main">
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">check_circle</span> Công suất 5.000 tấn/năm</li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">check_circle</span> Tiêu chuẩn TCVN 9844:2013, EN, ASTM</li>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">check_circle</span> Nhà phân phối HOCK Technology</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Certificates -->
    <section class="py-16 md:py-24 relative overflow-hidden mesh-bg">
      <div class="absolute inset-0 noise-overlay pointer-events-none"></div>
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop text-center relative z-10">
        <div class="text-center mb-16 reveal">
          <span class="kicker mb-4 block">{{ t('about.certificates') }}</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-bold tracking-tight">Chứng nhận chất lượng</h2>
        </div>
        <div v-if="certError && !certificates?.data?.length" class="bg-surface-bright border border-outline-variant rounded-2xl">
          <ErrorState :message="certError" @retry="loadCertificates" />
        </div>
        <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-6 stagger-grid">
          <router-link v-for="(cert, i) in certificates?.data ?? []" :key="cert.slug" to="/certificates"
            class="aspect-[3/4] bg-surface-bright border border-outline-variant p-6 flex flex-col items-center justify-center hover:shadow-[0_16px_48px_rgba(184,155,136,0.15)] hover:border-[#B89B88] transition-all duration-500 rounded-2xl group card-shine">
            <div class="w-full h-3/5 mb-4 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
              <img :src="cert.image" :alt="cert.name" class="max-w-full max-h-full object-contain" loading="lazy">
            </div>
            <span class="text-[13px] font-bold text-text-secondary text-center group-hover:text-primary transition-colors duration-300 leading-snug">{{ cert.name }}</span>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Download Profile CTA -->
    <section class="py-16 md:py-24 bg-surface-bright">
      <div class="max-w-3xl mx-auto px-[var(--spacing-margin-mobile)] md:px-[var(--spacing-margin-desktop)] text-center reveal">
        <div class="border border-outline-variant p-12 md:p-16 bg-surface-bright shadow-md rounded-3xl flex flex-col items-center hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] transition-all duration-500 relative overflow-hidden group">
          <div class="absolute top-0 right-0 w-48 h-48 bg-primary/5 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-700"></div>
          <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-primary/5 rounded-full group-hover:scale-125 transition-transform duration-700"></div>

          <div class="relative z-10">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#B89B88]/15 to-[#B89B88]/5 flex items-center justify-center mb-8 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
              <span class="material-symbols-outlined text-4xl text-primary">picture_as_pdf</span>
            </div>
            <h2 class="text-[28px] md:text-[36px] text-text-main mb-6 font-bold tracking-tight">{{ t('about.downloadProfile') }}</h2>
            <p class="text-text-secondary mb-10 text-[16px] md:text-[17px] max-w-lg leading-relaxed">{{ t('about.downloadProfileText') }}</p>
            <router-link to="/contact" class="btn btn-magnetic bg-primary text-white hover:bg-primary-dark rounded-full py-4 px-10 text-[15px] inline-flex items-center gap-3 font-bold shadow-[0_4px_20px_rgba(184,155,136,0.3)] transition-all duration-500">
              <span class="material-symbols-outlined text-xl">download</span> {{ t('about.requestProfile') }}
              <span class="material-symbols-outlined text-lg arrow-bounce">arrow_forward</span>
            </router-link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
