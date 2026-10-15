<script setup lang="ts">
import { faIcon } from '../utils/icons'
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import type { Product } from '../types'
import ProductCard from '../components/ProductCard.vue'
import PageHeader from '../components/PageHeader.vue'
import LazyImage from '../components/LazyImage.vue'
import RichText from '../components/RichText.vue'
import { t } from '../i18n'
import { fallbackProducts } from '../types/fallback'
import { getYearsOfExperience } from '../utils/experience'
import { buildContactLink } from '../utils/contactLink'

const route = useRoute()
const product = ref<Product | null>(null)
const related = ref<Product[]>([])
const loading = ref(true)
const notFound = ref(false)
const activeImage = ref(0)
type TabKey = 'specs' | 'applications' | 'functions' | 'description'
const activeTab = ref<TabKey>('specs')
let loadSeq = 0

const galleryImages = computed(() => {
  if (!product.value) return []
  const main = product.value.image
  const extras = (product.value.images ?? []).map((img) => img.image).filter(Boolean)
  return [main, ...extras].filter((url): url is string => !!url)
})

// Nút báo giá → liên hệ, nội dung tư vấn đã điền sẵn kèm tên sản phẩm.
const quoteLink = computed(() =>
  buildContactLink({ subject: 'product', product: product.value?.name }),
)

/**
 * Các mã cùng dòng để khách chuyển nhanh, ví dụ các độ dày màng HDPE.
 * Bản đã xem luôn nằm trong danh sách và được đánh dấu.
 */
const lineProducts = computed(() => product.value?.variants ?? [])

/** Bảng cấp cường độ của dòng (ART, GET...). */
const strengthGrades = computed(() => product.value?.strength_grades ?? [])

/** Công trình đã dùng vật liệu của dòng này. */
const lineProjects = computed(() => product.value?.projects ?? [])

/** Nội dung ứng dụng gõ tự do, hiển thị dưới danh sách ứng dụng có cấu trúc. */
const hasAppText = computed(() => !!(product.value?.applications_text ?? '').trim())

const tabs = computed(() => {
  const p = product.value
  if (!p) return [] as { key: TabKey; label: string }[]
  const list: { key: TabKey; label: string }[] = []
  if (p.specs?.length) list.push({ key: 'specs', label: t('product.specs') })
  if (p.applications?.length || p.applications_text) list.push({ key: 'applications', label: t('product.applications') })
  if (p.functions?.trim()) list.push({ key: 'functions', label: t('product.functions') })
  list.push({ key: 'description', label: t('product.description') })
  return list
})

watch(product, (p) => {
  if (!p) return
  if (!tabs.value.some((tab) => tab.key === activeTab.value)) {
    activeTab.value = tabs.value[0]?.key ?? 'description'
  }
})

const onKeydown = (e: KeyboardEvent) => {
  if (!galleryImages.value.length) return
  if (e.key === 'ArrowLeft') activeImage.value = (activeImage.value - 1 + galleryImages.value.length) % galleryImages.value.length
  if (e.key === 'ArrowRight') activeImage.value = (activeImage.value + 1) % galleryImages.value.length
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))

const appIcons: Record<string, string> = {
  'phan-cach-loc': 'filter_alt',
  'thoat-nuoc': 'water_drop',
  'gia-co-nen': 'add_road',
  'chong-xoi-mon': 'landscape',
  'on-dinh-mai-doc': 'architecture',
  'chong-tham': 'opacity',
  'hang-rao-bao-ve': 'shield',
}

const load = async (slug: string) => {
  const requestId = ++loadSeq
  loading.value = true
  product.value = null
  notFound.value = false
  related.value = []
  activeImage.value = 0
  try {
    const res = await api.product(slug)
    if (requestId !== loadSeq) return
    product.value = res.data
    // API đã xếp hạng sẵn theo mức độ liên quan (cùng dòng → chung ứng
    // dụng → cùng danh mục), không cần gọi thêm danh sách sản phẩm.
    related.value = (res.data.related_products ?? []) as Product[]
    if (!related.value.length) {
      related.value = fallbackProducts.filter((p) => p.slug !== slug && p.category?.slug === res.data.category?.slug).slice(0, 3)
    }
  } catch {
    if (requestId !== loadSeq) return
    const fallback = fallbackProducts.find((p) => p.slug === slug)
    if (fallback) {
      product.value = fallback
      related.value = fallbackProducts.filter((p) => p.slug !== slug && p.category?.slug === fallback.category?.slug).slice(0, 3)
    } else {
      notFound.value = true
    }
  } finally {
    if (requestId === loadSeq) loading.value = false
  }
}

watch(() => route.params.slug, (slug) => load(String(slug)), { immediate: true })

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.products'), to: '/products' },
  { label: product.value?.name || '' }
])
</script>

<template>
  <div v-if="loading" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-8">
    <div class="h-6 w-48 bg-surface-vlm animate-shimmer rounded-full mb-8"></div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
      <div class="h-96 bg-surface-vlm animate-shimmer rounded-3xl"></div>
      <div class="space-y-4">
        <div class="h-8 w-2/3 bg-surface-vlm animate-shimmer rounded-full"></div>
        <div class="h-4 w-1/3 bg-surface-vlm animate-shimmer rounded-full"></div>
        <div class="h-40 bg-surface-vlm animate-shimmer rounded-2xl"></div>
      </div>
    </div>
  </div>

  <div v-else-if="product">
    <PageHeader :title="product.name" :breadcrumbs="breadcrumbs" />

    <section class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">
        <!-- Gallery -->
        <div class="reveal">
          <div class="bg-surface-bright rounded-3xl flex items-center justify-center p-8 aspect-[4/3] relative border border-outline-variant hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] transition-all duration-500 group">
            <transition name="page" mode="out-in">
            <img :key="activeImage" :src="galleryImages[activeImage] ?? product.image" :alt="product.name" class="w-full h-full object-contain group-hover:scale-105 transition-transform ease-out duration-300" loading="lazy">
            </transition>
            <div v-if="galleryImages.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 glass-premium rounded-full px-5 py-2.5 shadow-lg">
              <button type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-text-main hover:bg-surface-vlm transition-colors duration-300" @click="activeImage = (activeImage - 1 + galleryImages.length) % galleryImages.length">
                <i class="fa text-sm fa-chevron-left"></i>
              </button>
              <span class="text-xs font-bold text-text-secondary tabular-nums min-w-[3rem] text-center">{{ activeImage + 1 }}/{{ galleryImages.length }}</span>
              <button type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-text-main hover:bg-surface-vlm transition-colors duration-300" @click="activeImage = (activeImage + 1) % galleryImages.length">
                <i class="fa text-sm fa-chevron-right"></i>
              </button>
            </div>
          </div>

          <div v-if="galleryImages.length > 1" class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4 overflow-x-auto scrollbar-hide pb-2">
            <button v-for="(img, i) in galleryImages" :key="i" type="button"
              class="aspect-square border-2 rounded-xl overflow-hidden flex items-center justify-center bg-surface-bright transition-all duration-300 p-2 cursor-pointer shrink-0 min-w-[80px]"
              :class="i === activeImage ? 'border-primary ring-2 ring-primary/20 shadow-md' : 'border-outline-variant hover:border-primary/50'"
              @click="activeImage = i">
              <LazyImage
                :src="img"
                :alt="`${product.name} ${i + 1}`"
                fallback-src="/images/products/geotextile-roll.jpg"
                container-class="w-full h-full bg-transparent flex items-center justify-center"
                image-class="max-w-full max-h-full object-contain"
              />
            </button>
          </div>
        </div>

        <!-- Product Info -->
        <div class="reveal reveal-delay-1">
          <div class="mb-6">
            <span v-if="product.category" class="inline-block bg-primary/10 text-primary-deep text-[12px] font-bold px-4 py-1.5 rounded-full mb-4 uppercase tracking-[0.15em]">{{ product.category.name }}</span>
            <h1 class="text-[28px] md:text-[36px] text-text-main font-extrabold mb-3 leading-[1.15] tracking-tight">{{ product.name }}</h1>
          </div>

          <div class="text-text-muted mb-6 text-[13px] font-bold uppercase tracking-wider flex items-center gap-3">
            {{ t('product.codeLabel') }}
            <span class="text-text-main font-mono bg-surface-vlm px-3 py-1.5 rounded-lg text-[14px]">{{ product.code }}</span>
          </div>

          <div v-if="product.strength_label" class="flex items-center gap-4 mb-8 bg-primary/5 border border-primary/15 rounded-2xl px-6 py-5">
            <span class="w-12 h-12 rounded-xl bg-primary/15 flex items-center justify-center">
              <i class="fa text-primary text-[24px] fa-compress"></i>
            </span>
            <div>
              <div class="text-[11px] text-text-muted font-bold uppercase tracking-wider mb-1">{{ product.strength_label }}</div>
              <div class="text-text-main font-extrabold text-[20px] tabular-nums">{{ product.strength_min }}<span v-if="product.strength_max && product.strength_max !== product.strength_min"> – {{ product.strength_max }}</span> <span class="text-text-muted text-[14px] font-medium">kN/m</span></div>
            </div>
          </div>

          <div class="text-text-secondary mb-10 leading-[1.8] text-[16px] max-w-2xl">
            <RichText :text="product.description" mode="lead" />
          </div>

          <!-- Các mã cùng dòng: chuyển nhanh giữa các thông số -->
          <div v-if="lineProducts.length" class="mb-10">
            <h2 class="text-[13px] font-bold text-text-muted uppercase tracking-[0.15em] mb-3">
              {{ product.parent ? 'Các loại trong dòng' : 'Các mã cùng dòng' }}
            </h2>
            <div class="flex flex-wrap gap-2">
              <router-link
                v-for="item in lineProducts"
                :key="item.slug"
                :to="`/products/${item.slug}`"
                class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-[13px] font-bold transition-all duration-300"
                :class="item.slug === product.slug
                  ? 'bg-primary text-white border-primary'
                  : 'bg-surface-vlm border-outline-variant text-text-secondary hover:border-primary hover:text-primary'"
              >
                <span class="font-mono">{{ item.code }}</span>
                <span class="font-medium">{{ item.name }}</span>
              </router-link>
            </div>
          </div>

          <!-- Bảng cấp cường độ -->
          <div v-if="strengthGrades.length" class="mb-10">
            <h2 class="text-[13px] font-bold text-text-muted uppercase tracking-[0.15em] mb-3">Các cấp cường độ</h2>
            <div class="overflow-x-auto border border-outline-variant rounded-2xl">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-surface-vlm text-text-muted">
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider">Cấp</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider">Đơn vị</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider">Ghi chú</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="grade in strengthGrades"
                    :key="grade.grade"
                    class="border-t border-outline-variant hover:bg-surface-bright transition-colors"
                  >
                    <td class="px-5 py-3.5 font-mono font-bold text-text-main tabular-nums whitespace-nowrap">{{ grade.grade }}</td>
                    <td class="px-5 py-3.5 text-text-secondary whitespace-nowrap">{{ grade.unit || '—' }}</td>
                    <td class="px-5 py-3.5 text-text-secondary">{{ grade.note || '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-4 mt-auto border-t border-outline-variant pt-8">
            <router-link
              :to="quoteLink"
              class="btn btn-primary btn-magnetic rounded-full py-4 px-8 text-[15px] font-bold inline-flex items-center justify-center gap-3 shadow-[0_4px_20px_rgba(184,155,136,0.3)] transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5"
            >
              <i class="fa text-xl fa-file-pen"></i> {{ t('product.quote') }}
            </router-link>
            <a href="tel:0909292530" class="btn border border-outline-variant text-text-main hover:border-primary hover:text-primary rounded-full py-4 px-8 text-[15px] font-bold inline-flex items-center justify-center gap-3 transition-all duration-300">
              <i class="fa text-xl fa-phone"></i> Gọi tư vấn
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabs -->
    <section class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop mb-20">
      <div class="bg-surface-bright border border-outline-variant rounded-3xl overflow-hidden">
        <div class="flex flex-wrap overflow-x-auto scrollbar-hide border-b border-outline-variant bg-surface-vlm">
          <button v-for="tab in tabs" :key="tab.key"
            class="px-6 md:px-8 py-4 md:py-5 text-[12px] md:text-[14px] font-bold uppercase tracking-wider transition-colors duration-300 relative whitespace-nowrap shrink-0"
            :class="activeTab === tab.key ? 'text-primary-deep bg-surface-bright' : 'text-text-muted hover:text-primary'"
            @click="activeTab = tab.key">
            {{ tab.label }}
            <span v-if="activeTab === tab.key" class="absolute bottom-0 left-0 right-0 h-1 bg-primary"></span>
          </button>
        </div>
        <div class="p-8 md:p-10">
          <transition name="page" mode="out-in">
            <div v-if="activeTab === 'specs' && product.specs?.length" key="specs">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                  v-for="(spec, i) in product.specs"
                  :key="`${spec.label}-${i}`"
                  class="bg-surface-bright border border-outline-variant rounded-2xl p-5 flex items-start gap-4 hover:border-primary/30 transition-colors duration-300"
                >
                  <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                    <i class="fa text-primary text-sm" :class="faIcon(spec.icon || 'check')"></i>
                  </div>
                  <div class="flex-grow min-w-0">
                    <div class="text-[11px] font-bold text-text-muted uppercase tracking-[0.15em] mb-1">{{ spec.label?.trim() || '—' }}</div>
                    <div class="text-text-main font-bold text-[16px] break-words max-w-[40ch]">{{ spec.value }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else-if="activeTab === 'applications'" key="applications" class="space-y-8">
              <!-- Nguồn 1: các bản ghi ứng dụng đã cấu trúc (chọn ở admin) -->
              <div v-if="product.applications?.length" class="grid grid-cols-1 gap-5">
                <div v-for="app in product.applications" :key="app.slug" class="flex items-start gap-4 p-6 bg-surface-vlm border border-outline-variant rounded-2xl hover:shadow-[0_12px_32px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 group card-shine">
                  <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-primary-deep shrink-0">
                    <i class="fa text-xl" :class="faIcon(appIcons[app.slug] || 'check')"></i>
                  </div>
                  <div>
                    <span class="text-[15px] font-bold text-text-main block mb-1.5">{{ app.name }}</span>
                    <span class="text-[13px] text-text-secondary leading-relaxed">{{ app.description }}</span>
                  </div>
                </div>
              </div>

              <!-- Nguồn 2: nội dung gõ tự do, hiện dưới danh sách ứng dụng -->
              <div>
                <h3
                  v-if="product.applications?.length && hasAppText"
                  class="text-[12px] font-bold text-text-muted uppercase tracking-[0.15em] mb-4"
                >
                  {{ t('product.applicationsDetail') }}
                </h3>
                <RichText :text="product.applications_text" class="text-text-secondary text-[16px]" />
              </div>
            </div>
            <div v-else-if="activeTab === 'functions'" key="functions">
              <RichText :text="product.functions" class="text-text-secondary text-[16px]" />
            </div>
            <div v-else-if="activeTab === 'description'" key="description">
              <RichText :text="product.description" mode="rest" class="text-text-secondary text-[16px]" />
              <p class="mt-6 border-t border-outline-variant pt-6 text-text-secondary leading-[1.8] text-[15px]">Với hơn {{ getYearsOfExperience() }} năm kinh nghiệm, CHƠN THÀNH cam kết cung cấp sản phẩm {{ product.name }} đạt tiêu chuẩn ISO 9001:2015, TCVN 9844:2013 và tiêu chuẩn Châu Âu EN, ASTM. Đội ngũ kỹ sư hỗ trợ tư vấn kỹ thuật miễn phí từ khâu thiết kế đến thi công.</p>
            </div>
          </transition>
        </div>
      </div>
    </section>

    <!-- Công trình đã dùng vật liệu của dòng này -->
    <section v-if="lineProjects.length" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop pb-20">
      <div class="text-center mb-12 reveal">
        <h2 class="text-[32px] md:text-[40px] font-extrabold text-text-main tracking-tight">Công trình đã áp dụng</h2>
        <p class="text-text-secondary mt-3">Sản phẩm của dòng Lưới địa kỹ thuật đã được sử dụng tại các công trình sau.</p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-grid">
        <router-link
          v-for="proj in lineProjects"
          :key="proj.slug"
          :to="`/projects/${proj.slug}`"
          class="group bg-surface-bright border border-outline-variant rounded-2xl overflow-hidden hover:border-primary/30 hover:shadow-[0_12px_32px_rgba(184,155,136,0.12)] transition-all duration-500"
        >
          <div class="relative bg-surface-vlm select-none overflow-hidden w-full h-40 object-cover transition-transform duration-700 group-hover:scale-105">
            <img :src="proj.hero_image" :alt="proj.name" class="w-full h-full object-cover transition-opacity duration-500 ease-out opacity-100" loading="lazy" decoding="async">
          </div>
          <div class="p-5">
            <h3 class="font-bold text-text-main text-[15px] leading-snug mb-2 group-hover:text-primary transition-colors">{{ proj.name }}</h3>
            <p v-if="proj.location" class="text-text-muted text-[13px] flex items-center gap-2">
              <i class="fa fa-location-dot text-primary"></i>{{ proj.location }}
            </p>
          </div>
        </router-link>
      </div>
    </section>

    <!-- Related Products -->
    <section v-if="related.length" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop pb-20">
      <div class="text-center mb-12 reveal">
        <h2 class="text-[32px] md:text-[40px] font-extrabold text-text-main tracking-tight">Sản phẩm liên quan</h2>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 stagger-grid">
        <ProductCard v-for="item in related" :key="item.slug" :product="item" />
      </div>
    </section>
  </div>

  <div v-else-if="notFound" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16 text-center">
    <i class="fa text-7xl text-outline-variant mb-6 block fa-boxes-stacked"></i>
    <h1 class="text-[28px] text-text-main font-bold mb-6">{{ t('product.notFound') }}</h1>
    <router-link to="/products" class="btn bg-primary text-primary-deep hover:bg-primary-dark rounded-full inline-flex items-center gap-2">
      <i class="fa text-lg fa-arrow-left"></i> {{ t('product.backToList') }}
    </router-link>
  </div>
</template>
