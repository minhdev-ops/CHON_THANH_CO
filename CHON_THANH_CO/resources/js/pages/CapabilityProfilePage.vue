<script setup lang="ts">
import { computed } from 'vue'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import { useSettings } from '../composables/useSettings'
import StatCard from '../components/StatCard.vue'
import ErrorState from '../components/ErrorState.vue'
import PageHeader from '../components/PageHeader.vue'
import SectionHeader from '../components/SectionHeader.vue'
import CTABanner from '../components/CTABanner.vue'
import { t } from '../i18n'
import { getYearsOfExperience } from '../utils/experience'
import { fallbackStats } from '../types/fallback'

const { data: home, error: statsError, load: loadHome } = useApiData(
  () => api.home(),
  () => ({ banners: [], stats: fallbackStats, why_choose_us: [], featured_products: [], latest_projects: [] })
)
const { settings, load } = useSettings()
load()

const capabilityFile = computed(() => settings.value?.['company.capability_file']?.trim() || '')

const capabilities = [
  { icon: 'factory', title: 'Hai nhà máy sản xuất', desc: 'Rọ đá Á Châu (Hóc Môn, 3.000 tấn/năm) và Lưới thép Tiên Phong (Đà Nẵng, 5.000 tấn/năm), tổng công suất 8.000 tấn/năm.' },
  { icon: 'inventory_2', title: '35+ mã sản phẩm', desc: 'Vải địa kỹ thuật, lưới địa kỹ thuật, rọ đá, thảm 3D, màng HDPE, GCL, lưới B40, dây kẽm gai — đáp ứng mọi yêu cầu kỹ thuật.' },
  { icon: 'engineering', title: 'Đội ngũ kỹ sư chuyên môn cao', desc: '12+ kỹ sư địa kỹ thuật, xây dựng, vật liệu — hỗ trợ tư vấn thiết kế và giám sát thi công tại công trường.' },
  { icon: 'local_shipping', title: 'Giao hàng & xuất khẩu', desc: 'Đội xe tải 2.5–18 tấn giao hàng toàn quốc trong 2–7 ngày. Xuất khẩu sang Campuchia, Lào, Myanmar.' },
]

const partners = [
  { label: 'ISO 9001:2015', sub: 'Hệ thống quản lý chất lượng quốc tế', icon: 'verified' },
  { label: 'TCVN 9844:2013', sub: 'Tiêu chuẩn quốc gia vải địa kỹ thuật', icon: 'workspace_premium' },
  { label: 'Tiêu chuẩn châu Âu EN', sub: 'EN ISO 14688, EN 13249...', icon: 'public' },
  { label: 'HOCK Technology', sub: 'Nhà phân phối uỷ quyền ARITEX', icon: 'handshake' },
]

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.about'), to: '/about' },
  { label: t('cap.title') }
])
</script>

<template>
  <div>
    <PageHeader :title="t('cap.title')" :breadcrumbs="breadcrumbs" />

    <section class="py-16 md:py-20 bg-surface-bright">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="reveal-left">
            <span class="kicker mb-4 block">Capability Profile</span>
            <h2 class="text-[32px] md:text-[42px] text-text-main font-bold tracking-tight leading-[1.15] mb-6">Năng lực toàn diện — Đối tác tin cậy của ngành hạ tầng</h2>
            <p class="text-text-secondary text-[16px] md:text-[17px] leading-relaxed mb-6">
              Với hơn {{ getYearsOfExperience() }} năm kinh nghiệm, CHƠN THÀNH tự hào sở hữu hệ thống 2 nhà máy sản xuất hiện đại, đội ngũ kỹ sư chuyên môn cao và hệ thống phân phối rộng khắp 63 tỉnh thành.
            </p>
            <div class="grid grid-cols-2 gap-4">
              <div class="flex items-start gap-3"><span class="material-symbols-outlined text-primary">check_circle</span><span class="text-[14px] text-text-main font-bold">2 nhà máy 8.000 tấn/năm</span></div>
              <div class="flex items-start gap-3"><span class="material-symbols-outlined text-primary">check_circle</span><span class="text-[14px] text-text-main font-bold">12+ kỹ sư chuyên môn</span></div>
              <div class="flex items-start gap-3"><span class="material-symbols-outlined text-primary">check_circle</span><span class="text-[14px] text-text-main font-bold">500+ dự án đã tham gia</span></div>
              <div class="flex items-start gap-3"><span class="material-symbols-outlined text-primary">check_circle</span><span class="text-[14px] text-text-main font-bold">Xuất khẩu 3 quốc gia</span></div>
            </div>
          </div>
          <div class="reveal-right">
            <div class="relative rounded-3xl overflow-hidden shadow-xl">
              <img src="/images/home-distribution.jpg" alt="Nhà máy CHƠN THÀNH" class="w-full aspect-[4/3] object-cover">
              <div class="absolute bottom-6 left-6 right-6 bg-surface-glass backdrop-blur-md rounded-2xl p-5 border-l-4 border-primary">
                <div class="font-extrabold text-text-main text-lg">{{ getYearsOfExperience() }}+ năm đồng hành cùng hạ tầng Việt Nam</div>
                <div class="text-text-secondary text-[13px] mt-1">Hơn 500 công trình từ 2005 đến nay</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 md:py-20 bg-canvas">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-14 reveal">
          <SectionHeader align="center" kicker="Our Capabilities" title="Năng lực cốt lõi" />
        </div>
        <div class="mt-16 grid grid-cols-1 md:grid-cols-2 gap-8 stagger-grid">
          <div v-for="(cap, i) in capabilities" :key="i"
            class="bg-surface-bright border border-outline-variant rounded-3xl p-9 shadow-sm hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 flex items-start gap-7 group card-shine glow-card">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:shadow-[0_4px_16px_rgba(184,155,136,0.2)] transition-all duration-500">
              <span class="material-symbols-outlined text-[28px] text-primary-deep" style="font-variation-settings: 'FILL' 1;">{{ cap.icon }}</span>
            </div>
            <div>
              <h3 class="font-extrabold text-text-main text-[19px] mb-3 group-hover:text-primary transition-colors duration-300">{{ cap.title }}</h3>
              <p class="text-[15px] text-text-secondary leading-relaxed">{{ cap.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 md:py-28 bg-surface-bright border-b border-outline-variant">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop text-center">
        <div class="text-center mb-16 reveal">
          <span class="kicker mb-4 block">By Numbers</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-bold tracking-tight">Con số ấn tượng</h2>
        </div>
        <div v-if="statsError && !home?.stats?.length" class="bg-surface-bright border border-outline-variant rounded-3xl">
          <ErrorState :message="statsError" @retry="loadHome" />
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 text-left stagger-grid">
          <StatCard v-for="(stat, i) in home?.stats ?? fallbackStats" :key="i"
            :icon="stat.icon" :value="stat.value" :label="stat.label"
            class="glow-card" />
        </div>
      </div>
    </section>

    <section class="py-20 md:py-28 bg-canvas">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-16 reveal">
          <span class="kicker mb-4 block">Standards</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-bold tracking-tight">Tiêu chuẩn & Chứng nhận</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 stagger-grid">
          <div v-for="(p, i) in partners" :key="i"
            class="bg-surface-bright border border-outline-variant rounded-3xl p-9 text-center shadow-sm hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 group card-shine glow-card">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center mx-auto mb-6 group-hover:scale-110 group-hover:shadow-[0_4px_16px_rgba(184,155,136,0.2)] transition-all duration-500">
              <span class="material-symbols-outlined text-[32px] text-primary-deep" style="font-variation-settings: 'FILL' 1;">{{ p.icon }}</span>
            </div>
            <h3 class="text-[16px] font-extrabold text-text-main mb-2">{{ p.label }}</h3>
            <p class="text-[14px] text-text-secondary leading-relaxed">{{ p.sub }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 md:py-28 bg-surface-bright">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-12 reveal">
          <span class="kicker mb-4 block">Documentation</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-bold tracking-tight mb-5">Hồ sơ năng lực trực tuyến</h2>
          <p class="text-text-secondary max-w-2xl mx-auto text-[16px]">Tải xuống hoặc xem trực tuyến hồ sơ năng lực chi tiết của CHƠN THÀNH.</p>
        </div>

        <div v-if="capabilityFile" class="reveal">
          <div class="bg-surface-bright border border-outline-variant rounded-3xl shadow-sm overflow-hidden hover:shadow-[0_12px_40px_rgba(184,155,136,0.1)] transition-all duration-500">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-7 md:px-10 py-6 border-b border-outline-variant bg-surface-vlm">
              <span class="font-bold text-text-main flex items-center gap-3 text-lg">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                  <span class="material-symbols-outlined text-primary text-xl">picture_as_pdf</span>
                </div>
                Hồ sơ năng lực CHƠN THÀNH
              </span>
              <div class="flex items-center gap-3 flex-shrink-0">
                <a :href="capabilityFile" target="_blank" rel="noopener noreferrer" class="btn btn-outline py-2.5 px-5 flex items-center justify-center gap-2 group/btn btn-magnetic">
                  <span class="material-symbols-outlined text-[18px] group-hover/btn:-translate-y-0.5 transition-transform">open_in_new</span>
                  Xem toàn màn hình
                </a>
                <a :href="capabilityFile" download="ho-so-nang-luc-chon-thanh.pdf" class="btn btn-primary py-2.5 px-5 flex items-center justify-center gap-2 group/btn btn-magnetic">
                  <span class="material-symbols-outlined text-[18px] group-hover/btn:-translate-y-0.5 transition-transform">download</span>
                  Tải xuống
                </a>
              </div>
            </div>
            <div class="p-5 md:p-8 bg-surface-vlm">
              <iframe :src="capabilityFile" class="w-full h-[70vh] rounded-2xl border border-outline-variant bg-white" title="Hồ sơ năng lực"></iframe>
            </div>
          </div>
        </div>

        <div v-else class="max-w-3xl mx-auto text-center reveal">
          <div class="border border-outline-variant p-14 bg-surface-bright shadow-sm rounded-3xl flex flex-col items-center hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 glow-card">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center mb-6">
              <span class="material-symbols-outlined text-[32px] text-primary-deep">support_agent</span>
            </div>
            <h2 class="text-[24px] text-text-main font-bold mb-4">Bạn cần hồ sơ năng lực chi tiết?</h2>
            <p class="text-text-secondary mb-8 text-[16px] leading-relaxed">Liên hệ với chúng tôi để nhận hồ sơ năng lực đầy đủ với thông tin chi tiết về nhà máy, dây chuyền sản xuất và các dự án tiêu biểu.</p>
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
