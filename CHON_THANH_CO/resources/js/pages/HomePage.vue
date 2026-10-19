<script setup lang="ts">
import { faIcon } from '../utils/icons'
import GlobalBanner from '../components/GlobalBanner.vue'
import CTABanner from '../components/CTABanner.vue'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import { ref, computed, onMounted } from 'vue'
import type { Banner, Product, Project, NewsItem, Partner } from '../types'
import HomeStats from '../components/HomeStats.vue'
import ErrorState from '../components/ErrorState.vue'
import Carousel from '../components/Carousel.vue'
import SectionHeader from '../components/SectionHeader.vue'
import ProjectCard from '../components/ProjectCard.vue'
import ProductCard from '../components/ProductCard.vue'
import LazyImage from '../components/LazyImage.vue'
import { getYearsOfExperience } from '../utils/experience'
import {
  fallbackStats,
  fallbackWhyChooseUs,
  fallbackProducts,
  fallbackProjects,
  fallbackNews,
} from '../types/fallback'
import { useSettings } from '../composables/useSettings'
import { t } from '../i18n'
import { buildContactLink } from '../utils/contactLink'

const { settings, load: loadSettings } = useSettings()
loadSettings()

const { data: home, loading, error, load } = useApiData(() => api.home(), () => ({
  banners: [{ section: 'cta', title: 'Sẵn sàng hợp tác cùng CHƠN THÀNH?', text: 'Liên hệ ngay để được tư vấn kỹ thuật miễn phí và nhận báo giá tốt nhất cho dự án của bạn.', link_to: '/contact', button_text: 'Nhận báo giá ngay' }],
  stats: fallbackStats,
  why_choose_us: fallbackWhyChooseUs,
  featured_products: fallbackProducts.slice(0, 4),
  latest_projects: fallbackProjects.slice(0, 6),
}))

const cta = computed<Banner | undefined>(() => home.value?.banners?.find((b) => b.section === 'cta'))
const featuredProducts = computed<Product[]>(() => home.value?.featured_products?.filter((p) => p.slug) ?? fallbackProducts.slice(0, 4))
const latestProjects = computed<Project[]>(() => home.value?.latest_projects?.filter((p) => p.slug) ?? fallbackProjects.slice(0, 6))

// Đối tác — marquee "Đối tác" lấy từ API, fallback danh sách tĩnh.
const partners = ref<Partner[]>([])
const marqueeItems = computed(() =>
  partners.value.length
    ? partners.value.map((p) => ({ name: p.name, image: p.image || null }))
    : clients.map((c) => ({ name: c, image: null as string | null }))
)
onMounted(async () => {
  try {
    const res = await api.partners()
    if (res.data?.length) partners.value = res.data
  } catch { /* giữ danh sách statically */ }
  await loadNews()
})

// Features (4 cards)
const features = [
  { icon: 'verified', title: 'Chất lượng đạt chuẩn', desc: 'ISO 9001:2015 (NQA/UKAS), TCVN 9844:2013. Sản phẩm đạt tiêu chuẩn châu Âu EN, ASTM — đảm bảo chất lượng cho mọi dự án.' },
  { icon: 'history_edu', title: `${getYearsOfExperience()}+ Năm kinh nghiệm`, desc: 'Thành lập 11/05/2005, hơn 2 thập kỷ đồng hành cùng hàng nghìn công trình hạ tầng giao thông, thuỷ lợi trên cả nước.' },
  { icon: 'inventory_2', title: '8.000 tấn/năm', desc: 'Hai nhà máy: Rọ đá Á Châu (Hóc Môn) 3.000 tấn/năm & Lưới Thép Tiên Phong (Đà Nẵng) 5.000 tấn/năm.' },
  { icon: 'support_agent', title: 'Tư vấn & hỗ trợ', desc: 'Tư vấn, cung cấp thông tin về vật liệu và hỗ trợ kỹ thuật, giao hàng toàn quốc bằng đội xe 2.5–18 tấn, xuất khẩu Campuchia, Lào, Myanmar.' },
]

// Services
const services = [
  { icon: 'engineering', title: 'Tư vấn kỹ thuật', desc: 'Phân tích địa chất, tư vấn giải pháp vật liệu tối ưu cho từng loại công trình hạ tầng.', image: '/images/products/geotextile-roll.jpg' },
  { icon: 'category', title: 'Cung cấp vật liệu', desc: 'Nguồn hàng ổn định, 35+ mã sản phẩm: vải KT không dệt, vải KT dệt, geogrid, rọ đá, màng HDPE.', image: '/images/projects/factory-a-chau.jpg' },
  { icon: 'construction', title: 'Hỗ trợ thi công', desc: 'Đội ngũ kỹ sư giám sát lắp đặt đúng kỹ thuật, đảm bảo tiến độ và chất lượng công trình.', image: '/images/projects/site-gabion.jpg' },
  { icon: 'workspace_premium', title: 'Bảo hành & hậu mãi', desc: 'Cam kết chất lượng theo TCVN 9844:2013, ISO 9001:2015, đồng hành lâu dài cùng khách hàng.', image: '/images/projects/highway-1.jpg' },
]

// FAQ
const openFaq = ref<number | null>(0)
const faqs = [
  { q: 'CHƠN THÀNH cung cấp những loại vật liệu địa kỹ thuật nào?', a: 'Chúng tôi cung cấp đầy đủ: vải địa kỹ thuật không dệt (ART 12–ART 280), vải địa kỹ thuật dệt (GET 5–GET 500), lưới địa kỹ thuật, thảm 3D chống xói mòn, rọ đá, băng thấm GCL, màng chống thấm HDPE, lưới B40 và dây kẽm gai. Tất cả đều đạt chuẩn ISO 9001:2015 và TCVN 9844:2013.' },
  { q: 'Sản phẩm có được kiểm định chất lượng không?', a: 'Toàn bộ sản phẩm đều được kiểm định bởi tổ chức độc lập (NQA/UKAS), đạt chứng nhận ISO 9001:2015, TCVN 9844:2013 và các tiêu chuẩn EN, ASTM. CHƠN THÀNH là nhà phân phối uỷ quyền chính thức của HOCK Technology — nhà sản xuất lớn nhất châu Á.' },
  { q: 'CHƠN THÀNH có hỗ trợ kỹ thuật tại công trình không?', a: 'Có. Chúng tôi cung cấp dịch vụ tư vấn kỹ thuật miễn phí từ giai đoạn thiết kế cơ sở đến khi hoàn thiện, hỗ trợ giám sát thi công tại công trình, đảm bảo vật liệu được lắp đặt đúng kỹ thuật.' },
  { q: 'Thời gian giao hàng là bao lâu?', a: 'Với hàng có sẵn kho: 2–7 ngày làm việc toàn quốc bằng đội xe tải 2.5–18 tấn. Hàng đặt theo thông số kỹ thuật riêng: 7–21 ngày tùy số lượng. Xuất khẩu sang Campuchia, Lào, Myanmar: 10–25 ngày bao gồm thông quan.' },
  { q: 'Công ty có xuất khẩu không?', a: 'Có. CHƠN THÀNH đã xuất khẩu sản phẩm sang Campuchia, Lào và Myanmar với đầy đủ hồ sơ CO/CQ và chứng nhận quốc tế. Đội ngũ kỹ sư hỗ trợ tư vấn kỹ thuật cho dự án xuất khẩu.' },
]

// Testimonials
const testimonials = [
  { name: 'Nguyễn Văn An', role: 'Giám đốc – Công ty CP Xây dựng Delta', avatar: '/images/projects/highway-1.jpg', rating: 5, text: 'Vải địa kỹ thuật ARITEX của CHƠN THÀNH chất lượng vượt trội, giúp dự án hoàn thành đúng tiến độ. Đội ngũ tư vấn kỹ thuật rất chuyên nghiệp và tận tâm.' },
  { name: 'Trần Minh Tuấn', role: 'Tổng thầu Hoàng Gia', avatar: '/images/projects/highway-2.jpg', rating: 5, text: 'Chất lượng sản phẩm ổn định, giao hàng đúng hẹn. Đặc biệt là dịch vụ hỗ trợ kỹ thuật tại công trình rất bài bản, đúng theo tiêu chuẩn TCVN.' },
  { name: 'Lê Thị Hương', role: 'Ban QLDA Cơ sở hạ tầng TW', avatar: '/images/projects/bridge-1.jpg', rating: 5, text: 'Rọ đá và lưới thép CHƠN THÀNH đạt TCVN, hiệu quả chống xói mòn tốt. Chúng tôi đã hợp tác nhiều dự án và rất hài lòng với năng lực nhà máy.' },
]

// Clients marquee
const clients = ['HOCK Technology', 'ARITEX', 'Petrovietnam', 'Vingroup', 'Coteccons', 'Vinaconex', 'Hoà Bình', 'Delta', 'Trung Nam', 'CIENCO 4', 'CIENCO 6', 'Sông Đà']

// News preview — fetch from API on mount, fallback to static data.
const news = ref<NewsItem[]>([...fallbackNews])
const newsLoading = ref(false)

const loadNews = async () => {
  newsLoading.value = true
  try {
    const res = await api.news({ limit: 3 })
    news.value = res.data.length ? res.data : [...fallbackNews]
  } catch {
    news.value = [...fallbackNews]
  } finally {
    newsLoading.value = false
  }
}

const newsPreview = computed<NewsItem[]>(() => news.value.slice(0, 3))

// Stats for about section
const defaultStats = [
  { value: '21+', label: 'Năm kinh nghiệm', icon: 'workspace_premium' },
  { value: '8.000', label: 'Tấn/năm công suất', icon: 'factory' },
  { value: '35+', label: 'Mã sản phẩm', icon: 'category' },
  { value: '3', label: 'Quốc gia xuất khẩu', icon: 'public' },
]
const displayStats = computed(() =>
  home.value?.stats?.length ? home.value.stats.map((s, i) => ({
    value: s.value, label: s.label, icon: defaultStats[i]?.icon || 'star'
  })) : defaultStats
)
</script>

<template>
  <div>
    <!-- ── 1. Hero Banner ─────────────────────────────── -->
    <GlobalBanner />

    <!-- ── 2. Client Trust Bar ───────────────────────── -->
    <section class="w-full border-y border-white/40 bg-canvas py-6 md:py-14">
      <div class="max-w-max-width mx-auto px-4 md:px-margin-desktop flex items-center gap-4 md:gap-8">
        <span class="font-bold text-text-main text-[14px] md:text-[20px] whitespace-nowrap shrink-0 uppercase tracking-wide">Đối tác</span>
        <span class="w-px h-6 md:h-7 bg-border shrink-0 hidden md:block"></span>
        <div class="relative flex overflow-hidden flex-grow mask-edges">
          <div class="flex animate-marquee whitespace-nowrap items-center gap-10 md:gap-16">
            <template v-for="i in 3" :key="i">
              <template v-for="c in marqueeItems" :key="c.name + i">
                <img v-if="c.image" :src="c.image" :alt="c.name" class="h-9 md:h-12 w-auto object-contain">
                <span v-else class="text-text-muted font-bold text-[14px] md:text-[20px] tracking-wide">{{ c.name }}</span>
              </template>
              <span class="text-accent/40 text-base md:text-lg">◆</span>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- ── 3. Features (4 cards) ─────────────────────── -->
    <section class="w-full py-10 md:py-20 bg-canvas feature">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center max-w-2xl mx-auto mb-8 md:mb-12 reveal">
          <SectionHeader align="center" kicker="Tại sao chọn chúng tôi" title="Năng lực vượt trội — Đồng hành bền vững"
            :subtitle="`Với hơn ${getYearsOfExperience()} năm kinh nghiệm, CHƠN THÀNH mang đến giải pháp toàn diện về tư vấn vật liệu, đạt chuẩn quốc tế.`" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
          <div v-for="(f, i) in features" :key="i"
            class="feature-card relative bg-surface-bright rounded-2xl p-4 md:p-6 pt-5 md:pt-10 text-center flex flex-col items-center h-full transition-all duration-500 group overflow-hidden reveal"
            :class="`reveal-delay-${i+1}`">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative w-[40px] h-[40px] md:w-[72px] md:h-[72px] rounded-2xl bg-surface-vlm flex items-center justify-center mb-3 md:mb-6">
              <i class="fa text-[20px] md:text-[36px] text-primary" :class="faIcon(f.icon)"></i>
            </div>
            <h3 class="relative z-10 font-extrabold text-text-main text-[13px] md:text-[18px] mb-2 md:mb-3 tracking-tight leading-tight">{{ f.title }}</h3>
            <p class="relative z-10 text-text-secondary text-[11px] md:text-[14px] leading-relaxed mb-4 md:mb-6 flex-grow hidden sm:block">{{ f.desc }}</p>
            <router-link to="/about" class="relative z-10 inline-flex items-center gap-1.5 text-primary font-semibold text-[11px] md:text-[14px] group-hover:text-primary-dark transition-colors duration-300">
              Chi tiết
              <i class="fa text-[14px] md:text-[18px] group-hover:translate-x-1 transition-transform duration-300 fa-arrow-right"></i>
            </router-link>
            <div class="absolute -bottom-10 -right-10 w-28 h-28 rounded-full bg-primary/5 group-hover:bg-primary/10 transition-all duration-700 group-hover:scale-150"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── 4. About (2-col: text + image/stats) ──────── -->
    <section class="w-full py-10 md:py-20 bg-surface-bright">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 items-center">
          <div class="reveal-left">
            <SectionHeader align="left" kicker="Về chúng tôi" title="Nhà cung cấp địa kỹ thuật hàng đầu" />
            <p class="text-text-secondary text-[16px] md:text-[18px] leading-relaxed mt-6 mb-4">
              {{ settings?.['company.description'] || `Với hơn ${getYearsOfExperience()} năm hoạt động (thành lập 11/05/2005), CHƠN THÀNH tự hào là đơn vị cung cấp vật liệu địa kỹ thuật uy tín nhất tại Việt Nam, là nhà phân phối uỷ quyền chính thức của HOCK Technology — nhà sản xuất lớn nhất châu Á.` }}
            </p>
            <p v-if="!settings?.['company.description']" class="text-text-secondary text-[16px] leading-relaxed mb-8">
              Đồng hành cùng hàng nghìn nhà thầu và chủ đầu tư, chúng tôi mang đến giải pháp tối ưu cho hạ tầng giao thông, thủy lợi với thương hiệu riêng ARITEX.
            </p>
            <p v-else class="text-text-secondary text-[16px] leading-relaxed mb-8">
              {{ settings?.['about.mission_vi'] || 'Đồng hành cùng hàng nghìn nhà thầu và chủ đầu tư, chúng tôi mang đến giải pháp tối ưu cho hạ tầng giao thông, thủy lợi với thương hiệu riêng ARITEX.' }}
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-10">
              <div v-for="(c, i) in [
                'Nhà phân phối uỷ quyền HOCK Technology — lớn nhất châu Á',
                'Hai nhà máy: Hóc Môn (3.000 tấn/năm) & Đà Nẵng (5.000 tấn/năm)',
                'Tư vấn, cung cấp thông tin về vật liệu và hỗ trợ kỹ thuật',
                'Xuất khẩu CO/CQ đầy đủ sang Campuchia, Lào, Myanmar',
              ]" :key="i" class="flex items-start gap-3 text-text-main font-bold text-[15px]">
                <i class="fa text-primary font-bold fa-circle-check"></i>
                {{ c }}
              </div>
            </div>

            <router-link to="/about" class="btn-primary rounded-full py-3.5 px-8 text-[16px] shadow-md">
              Xem hồ sơ năng lực
            </router-link>
          </div>

          <div class="reveal-right">
            <div class="grid gap-6">
              <div class="rounded-[10px] overflow-hidden bg-surface-vlm shadow-md">
                <LazyImage
                  src="/images/projects/factory-a-chau.jpg"
                  alt="CHƠN THÀNH nhà máy"
                  fallback-src="/images/projects/factory-a-chau.jpg"
                  aspect-ratio="aspect-[16/9]"
                  image-class="w-full h-full object-cover hover:scale-105 transition-transform duration-700"
                />
              </div>
              <HomeStats :stats="displayStats" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── 5. Featured Products ──────────────────────── -->
    <section class="w-full py-10 md:py-20 bg-canvas">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 md:gap-6 mb-8 md:mb-12 reveal">
          <SectionHeader kicker="Sản phẩm của chúng tôi" title="Sản phẩm tiêu biểu" />
          <router-link to="/products" class="btn-primary rounded-full py-3.5 px-8 shrink-0 shadow-md transition-colors font-semibold hover:transform-none">
            Tất cả sản phẩm <i class="fa text-lg ml-1 align-middle fa-arrow-right"></i>
          </router-link>
        </div>
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="i in 4" :key="i" class="h-80 bg-surface-vlm animate-shimmer rounded-2xl border border-outline-variant"></div>
        </div>
        <ErrorState v-else-if="error" message="Không thể tải sản phẩm" @retry="load" />
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-grid">
          <ProductCard v-for="(p, i) in featuredProducts" :key="p.slug" :product="p" class="reveal" :class="`reveal-delay-${(i%4)+1}`" />
        </div>
      </div>
    </section>

    <!-- ── 6. Services ───────────────────────────────── -->
    <section class="w-full py-10 md:py-24 bg-surface-bright relative overflow-hidden">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop relative z-10">
        <div class="text-center max-w-2xl mx-auto mb-8 md:mb-16 reveal">
          <SectionHeader align="center" kicker="Dịch vụ của chúng tôi" title="Giải pháp trọn gói từ tư vấn đến thi công" />
        </div>
        <!-- Desktop: 4-column grid -->
        <div class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 stagger-grid">
          <div v-for="(s, i) in services" :key="i"
            class="group flex flex-col bg-canvas border border-outline-variant rounded-3xl overflow-hidden hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 reveal" :class="`reveal-delay-${(i%4)+1}`">
            <LazyImage
              :src="s.image"
              :alt="s.title"
              fallback-src="/images/products/geotextile-roll.jpg"
              aspect-ratio="aspect-[4/3]"
              container-class="shrink-0"
              image-class="w-full h-full object-cover"
              no-overflow
            >
              <div class="absolute inset-0 bg-gradient-to-t from-text-main/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
              <div class="absolute -bottom-6 right-6 w-14 h-14 bg-primary text-white rounded-2xl flex items-center justify-center shadow-lg transform group-hover:-translate-y-2 transition-transform duration-500 z-10">
                <i class="fa text-[28px]" :class="faIcon(s.icon)"></i>
              </div>
            </LazyImage>
            <div class="p-6 md:p-8 flex flex-col flex-grow relative pt-10">
              <h3 class="font-extrabold text-[20px] text-text-main mb-3 group-hover:text-primary transition-colors duration-300 leading-snug">{{ s.title }}</h3>
              <p class="text-text-secondary text-[14px] leading-relaxed mb-6 flex-grow">{{ s.desc }}</p>
              <div class="mt-auto pt-5 border-t border-outline-variant/60">
                <router-link
                  :to="buildContactLink({ subject: 'quote-24h' })"
                  class="inline-flex items-center gap-2 font-bold text-[13px] text-primary group-hover:text-primary-deep uppercase tracking-[0.12em] transition-colors duration-300"
                >
                  Tư vấn ngay <i class="fa text-[16px] group-hover:translate-x-1 transition-transform fa-arrow-right"></i>
                </router-link>
              </div>
            </div>
          </div>
        </div>
        <!-- Mobile: compact horizontal cards -->
        <div class="flex flex-col gap-3 md:hidden stagger-grid">
          <div v-for="(s, i) in services" :key="i"
            class="group flex items-center gap-4 bg-canvas border border-outline-variant rounded-2xl p-5 hover:shadow-md hover:border-primary/30 transition-all duration-300 reveal" :class="`reveal-delay-${(i%4)+1}`">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
              <i class="fa text-[24px] text-primary" :class="faIcon(s.icon)"></i>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-extrabold text-[15px] text-text-main mb-1 group-hover:text-primary transition-colors duration-300 leading-tight">{{ s.title }}</h3>
              <p class="text-text-secondary text-[13px] leading-snug line-clamp-2">{{ s.desc }}</p>
            </div>
            <router-link
              :to="buildContactLink({ subject: 'quote-24h' })"
              class="shrink-0 w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all duration-300"
            >
              <i class="fa text-[18px] text-primary group-hover:text-white fa-arrow-right"></i>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- ── 7. Process Steps ──────────────────────────── -->
    <section class="w-full py-10 md:py-20 bg-canvas relative overflow-hidden">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop relative z-10">
        <div class="text-center max-w-2xl mx-auto mb-8 md:mb-16 reveal">
          <SectionHeader align="center" kicker="Quy trình hợp tác" title="4 bước đồng hành cùng khách hàng" />
        </div>
        <!-- Desktop: 4-column grid -->
        <div class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 stagger-grid">
          <div v-for="(p, i) in [
            { num: '01', icon: 'support_agent', title: 'Tiếp nhận yêu cầu', desc: 'Tư vấn viên ghi nhận yêu cầu dự án, thông số kỹ thuật và tiến độ mong muốn.' },
            { num: '02', icon: 'biotech', title: 'Phân tích & đề xuất', desc: 'Kỹ sư phân tích địa chất, đề xuất giải pháp vật liệu tối ưu về chi phí – kỹ thuật.' },
            { num: '03', icon: 'inventory_2', title: 'Sản xuất & giao hàng', desc: 'Hàng sản xuất tại nhà máy Hóc Môn/Đà Nẵng, giao tận công trường bằng đội xe 2.5–18 tấn.' },
            { num: '04', icon: 'verified', title: 'Hỗ trợ thi công', desc: 'Kỹ sư giám sát lắp đặt tại công trường, nghiệm thu và bàn giao đúng tiến độ.' },
          ]" :key="i" class="relative reveal" :class="`reveal-delay-${(i%4)+1}`">
            <div class="bg-surface-bright border border-outline-variant rounded-3xl p-8 hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 group h-full card-shine glow-card">
              <div class="flex items-start justify-between mb-6">
                <span class="text-[48px] md:text-[64px] font-bold text-primary/30 group-hover:text-primary transition-colors duration-500 leading-none tabular-nums drop-shadow-sm">{{ p.num }}</span>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                  <i class="fa text-[26px] text-primary-deep" :class="faIcon(p.icon)"></i>
                </div>
              </div>
              <h3 class="font-extrabold text-text-main text-[19px] mb-3 tracking-tight">{{ p.title }}</h3>
              <p class="text-text-secondary text-[14px] leading-relaxed">{{ p.desc }}</p>
            </div>
          </div>
        </div>
        <!-- Mobile: compact horizontal steps -->
        <div class="flex flex-col gap-2 md:hidden">
          <div v-for="(p, i) in [
            { num: '01', icon: 'support_agent', title: 'Tiếp nhận yêu cầu', desc: 'Tư vấn viên ghi nhận yêu cầu dự án và thông số kỹ thuật.' },
            { num: '02', icon: 'biotech', title: 'Phân tích & đề xuất', desc: 'Kỹ sư đề xuất giải pháp vật liệu tối ưu chi phí – kỹ thuật.' },
            { num: '03', icon: 'inventory_2', title: 'Sản xuất & giao hàng', desc: 'Giao tận công trường bằng đội xe 2.5–18 tấn.' },
            { num: '04', icon: 'verified', title: 'Hỗ trợ thi công', desc: 'Giám sát lắp đặt, nghiệm thu đúng tiến độ.' },
          ]" :key="i" class="flex items-center gap-3 bg-surface-bright border border-outline-variant rounded-2xl p-4 reveal" :class="`reveal-delay-${(i%4)+1}`">
            <span class="text-[28px] font-bold text-primary/40 leading-none tabular-nums shrink-0">{{ p.num }}</span>
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
              <i class="fa text-[20px] text-primary" :class="faIcon(p.icon)"></i>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-extrabold text-text-main text-[14px] leading-tight">{{ p.title }}</h3>
              <p class="text-text-secondary text-[12px] leading-snug mt-0.5 line-clamp-1">{{ p.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── 8. Featured Projects ───────────────────────── -->
    <section class="w-full py-10 md:py-20 bg-surface-bright">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 md:gap-6 mb-8 md:mb-12 reveal">
          <SectionHeader kicker="Dự án của chúng tôi" title="Công trình hạ tầng tiêu biểu" />
          <router-link to="/projects" class="btn-primary rounded-full py-3.5 px-8 shrink-0 shadow-md transition-colors font-semibold hover:transform-none">
            Tất cả dự án <i class="fa text-lg ml-1 align-middle fa-arrow-right"></i>
          </router-link>
        </div>
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="i" class="h-[400px] animate-shimmer rounded-[10px]"></div>
        </div>
        <ErrorState v-else-if="error" message="Không thể tải dữ liệu" @retry="load" />
        <Carousel v-else :count="latestProjects.length" :per-view-xl="3" :per-view-lg="3" :per-view-sm="2" :per-view-mobile="1">
          <template #default="{ index }">
            <ProjectCard :project="latestProjects[index]" class="reveal shadow-sm hover:shadow-lg rounded-[10px]" :class="`reveal-delay-${(index%3)+1}`" />
          </template>
        </Carousel>
      </div>
    </section>

    <!-- ── 9. FAQ + Image ────────────────────────────── -->
    <section class="w-full py-10 md:py-20 bg-canvas">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 items-center">
          <div class="reveal-left">
            <SectionHeader align="left" kicker="Câu hỏi thường gặp" title="Giải đáp thắc mắc của bạn" />
            <div class="flex flex-col gap-4 mt-8">
              <div v-for="(faq, i) in faqs" :key="i"
                class="rounded-[10px] overflow-hidden transition-all duration-300 border border-outline-variant"
                :class="openFaq === i ? 'shadow-md border-primary' : 'hover:border-primary'">
                <button
                  class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left cursor-pointer transition-colors duration-300"
                  :class="openFaq === i ? 'bg-primary/90 text-white' : 'bg-surface-vlm text-text-main'"
                  @click="openFaq = openFaq === i ? null : i">
                  <span class="font-bold text-[15px] md:text-[18px]">{{ faq.q }}</span>
                  <div class="w-10 h-10 flex items-center justify-center shrink-0 transition-colors">
                    <i class="fa text-[24px] transition-transform duration-300 fa-chevron-down" :class="openFaq === i ? 'rotate-180 text-white' : 'text-primary'"></i>
                  </div>
                </button>
                <transition name="page">
                  <div v-if="openFaq === i" class="px-5 md:px-6 py-4 md:py-5 text-[14px] md:text-[16px] text-text-secondary leading-relaxed bg-surface-bright">{{ faq.a }}</div>
                </transition>
              </div>
            </div>
          </div>
          <div class="reveal-right">
            <div class="relative rounded-[10px] overflow-hidden">
              <LazyImage
                src="/images/projects/site-gabion.jpg"
                alt="Công trình địa kỹ thuật"
                fallback-src="/images/projects/site-gabion.jpg"
                aspect-ratio="aspect-[4/5]"
                image-class="w-full h-full object-cover"
              >
                <div class="absolute inset-0 bg-gradient-to-t from-primary-deep/80 via-primary-deep/20 to-transparent"></div>
                <div class="absolute bottom-8 left-8 right-8 z-10">
                  <div class="bg-surface-glass backdrop-blur-md rounded-[10px] p-6">
                    <div class="text-text-main font-extrabold text-2xl mb-2">{{ getYearsOfExperience() }}+ năm kinh nghiệm</div>
                    <div class="text-text-secondary text-[15px] font-medium">Đồng hành cùng hàng nghìn công trình hạ tầng từ 2005 đến nay</div>
                  </div>
                </div>
              </LazyImage>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── 10. Testimonials ──────────────────────────── -->
    <section class="w-full py-10 md:py-20 bg-surface-bright relative overflow-hidden">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop relative z-10">
        <div class="text-center max-w-2xl mx-auto mb-8 md:mb-14 reveal">
          <SectionHeader align="center" kicker="Khách hàng nói về chúng tôi" title="Đối tác đồng hành" />
        </div>
        <!-- Desktop: 3-column grid -->
        <div class="hidden md:grid grid-cols-2 lg:grid-cols-3 gap-6 stagger-grid">
          <div v-for="(t, i) in testimonials" :key="i"
            class="testimonial-card bg-surface-1 border border-outline-variant rounded-2xl p-6 flex flex-col h-full reveal" :class="`reveal-delay-${(i%3)+1}`">
            <div class="flex items-center gap-1 mb-4">
              <i v-for="s in 5" :key="s" class="fa text-primary text-[20px] fa-star"></i>
            </div>
            <p class="text-text-secondary text-[15px] leading-relaxed mb-6 flex-grow italic">"{{ t.text }}"</p>
            <div class="flex items-center gap-3 pt-4 border-t border-outline-variant">
              <LazyImage
                :src="t.avatar"
                :alt="t.name"
                fallback-src="/images/projects/highway-1.jpg"
                container-class="w-12 h-12 rounded-full shrink-0 border-2 border-primary/20"
                image-class="w-full h-full rounded-full object-cover"
              />
              <div>
                <div class="font-extrabold text-text-main text-[15px]">{{ t.name }}</div>
                <div class="text-text-secondary text-[13px] font-medium">{{ t.role }}</div>
              </div>
            </div>
          </div>
        </div>
        <!-- Mobile: horizontal scroll -->
        <div class="flex md:hidden gap-4 overflow-x-auto snap-x snap-mandatory pb-4 -mx-4 px-4 scrollbar-none">
          <div v-for="(t, i) in testimonials" :key="i"
            class="testimonial-card bg-surface-1 border border-outline-variant rounded-2xl p-5 flex flex-col snap-start shrink-0 w-[85vw] max-w-[320px] reveal" :class="`reveal-delay-${(i%3)+1}`">
            <div class="flex items-center gap-1 mb-3">
              <i v-for="s in 5" :key="s" class="fa text-primary text-[16px] fa-star"></i>
            </div>
            <p class="text-text-secondary text-[13px] leading-relaxed mb-4 flex-grow italic line-clamp-4">"{{ t.text }}"</p>
            <div class="flex items-center gap-3 pt-3 border-t border-outline-variant">
              <LazyImage
                :src="t.avatar"
                :alt="t.name"
                fallback-src="/images/projects/highway-1.jpg"
                container-class="w-10 h-10 rounded-full shrink-0 border-2 border-primary/20"
                image-class="w-full h-full rounded-full object-cover"
              />
              <div>
                <div class="font-extrabold text-text-main text-[14px]">{{ t.name }}</div>
                <div class="text-text-secondary text-[13px] font-medium">{{ t.role }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── 11. News Preview ──────────────────────────── -->
    <section class="w-full py-10 md:py-20 bg-canvas">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 md:gap-6 mb-8 md:mb-12 reveal">
          <SectionHeader kicker="Tin tức mới nhất" title="Tin tức & Sự kiện" />
          <router-link to="/news" class="btn-primary rounded-full py-3 px-6 md:py-3.5 md:px-8 shrink-0 shadow-md transition-colors font-semibold text-[14px]">
            Xem tất cả <i class="fa text-lg ml-1 align-middle fa-arrow-right"></i>
          </router-link>
        </div>
        <!-- Desktop: 3-column grid -->
        <div v-if="newsLoading" class="hidden md:grid grid-cols-3 gap-8">
          <div v-for="i in 3" :key="i" class="h-80 bg-surface-vlm animate-shimmer rounded-3xl border border-outline-variant"></div>
        </div>
        <div v-else class="hidden md:grid grid-cols-3 gap-8 stagger-grid">
          <router-link v-for="(n, i) in newsPreview" :key="n.slug" :to="`/news/${n.slug}`"
            class="bg-surface-bright border border-outline-variant rounded-3xl overflow-hidden group hover:shadow-[0_20px_60px_rgba(184,155,136,0.15)] hover:border-primary/30 transition-all duration-500 flex flex-col reveal" :class="`reveal-delay-${(i%3)+1}`">
            <LazyImage
              :src="n.image"
              :alt="n.title"
              fallback-src="/images/products/industrial-1.jpg"
              aspect-ratio="aspect-[16/10]"
              image-class="w-full h-full object-cover"
            />
            <div class="p-5 md:p-7 flex flex-col flex-grow">
              <span class="text-[11px] font-bold text-primary uppercase tracking-[0.15em] mb-3">{{ n.category?.name || 'Tin tức' }} — {{ new Date(n.published_at).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</span>
              <h3 class="font-extrabold text-text-main text-[16px] md:text-[19px] mb-2 md:mb-3 group-hover:text-primary transition-colors duration-300 line-clamp-2 leading-snug">{{ n.title }}</h3>
              <p class="text-text-secondary text-[14px] leading-relaxed line-clamp-2 mb-5 flex-grow">{{ n.excerpt }}</p>
              <span class="inline-flex items-center gap-2 font-bold text-[13px] text-primary group-hover:text-primary-deep uppercase tracking-[0.12em] transition-colors duration-300 mt-auto">
                Đọc tiếp <i class="fa text-[16px] group-hover:translate-x-1 transition-transform fa-arrow-right"></i>
              </span>
            </div>
          </router-link>
        </div>
        <!-- Mobile: horizontal cards -->
        <div v-if="newsLoading" class="flex flex-col gap-3 md:hidden">
          <div v-for="i in 3" :key="i" class="h-24 bg-surface-vlm animate-shimmer rounded-2xl border border-outline-variant"></div>
        </div>
        <div v-else class="flex flex-col gap-3 md:hidden stagger-grid">
          <router-link v-for="(n, i) in newsPreview" :key="n.slug" :to="`/news/${n.slug}`"
            class="flex items-center gap-3 bg-surface-bright border border-outline-variant rounded-2xl p-4 group hover:shadow-md hover:border-primary/30 transition-all duration-300 reveal" :class="`reveal-delay-${(i%3)+1}`">
            <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0">
              <LazyImage
                :src="n.image"
                :alt="n.title"
                fallback-src="/images/products/industrial-1.jpg"
                image-class="w-full h-full object-cover"
              />
            </div>
            <div class="flex-1 min-w-0">
              <span class="text-[11px] font-bold text-primary uppercase tracking-[0.12em]">{{ n.category?.name || 'Tin tức' }}</span>
              <h3 class="font-extrabold text-text-main text-[14px] mt-1 group-hover:text-primary transition-colors duration-300 line-clamp-2 leading-snug">{{ n.title }}</h3>
              <span class="inline-flex items-center gap-1 font-bold text-[11px] text-primary mt-1.5 uppercase tracking-[0.1em]">
                Đọc tiếp <i class="fa text-[14px] fa-arrow-right"></i>
              </span>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ── 12. CTA ─────────────────────────────────────── -->
    <CTABanner
      :title="cta?.title || 'Sẵn sàng hợp tác cùng CHƠN THÀNH?'"
      :text="cta?.text || 'Liên hệ ngay để được tư vấn kỹ thuật miễn phí và nhận báo giá tốt nhất cho dự án của bạn.'"
      :link-to="cta?.link_to || ''"
      :link-label="cta?.button_text || 'Nhận báo giá ngay'"
      subject="quote-24h"
    />
  </div>
</template>
