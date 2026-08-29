<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import type { Product } from '../types'
import ProductCard from '../components/ProductCard.vue'
import PageHeader from '../components/PageHeader.vue'
import { t } from '../i18n'
import { fallbackProducts } from '../types/fallback'
import { getYearsOfExperience } from '../utils/experience'

const route = useRoute()
const product = ref<Product | null>(null)
const related = ref<Product[]>([])
const loading = ref(true)
const notFound = ref(false)
const activeImage = ref(0)
const activeTab = ref<'specs' | 'applications' | 'description'>('specs')
let loadSeq = 0

const galleryImages = computed(() => {
  if (!product.value) return []
  const main = product.value.image
  const extras = (product.value.images ?? []).map((img) => img.image).filter(Boolean)
  return [main, ...extras].filter((url): url is string => !!url)
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
    const list = await api.products({ category: res.data.category?.slug, limit: 50 })
    if (requestId !== loadSeq) return
    related.value = list.data.filter((p) => p.slug !== slug).slice(0, 3)
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
          <div class="bg-surface-bright rounded-3xl overflow-hidden flex items-center justify-center p-8 aspect-[4/3] relative border border-outline-variant hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] transition-all duration-500 group">
            <transition name="page" mode="out-in">
              <img :key="activeImage" :src="galleryImages[activeImage] ?? product.image" :alt="product.name" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-700" loading="lazy">
            </transition>
            <div v-if="galleryImages.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 glass-premium rounded-full px-5 py-2.5 shadow-lg">
              <button type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-text-main hover:bg-surface-vlm transition-colors duration-300" @click="activeImage = (activeImage - 1 + galleryImages.length) % galleryImages.length">
                <span class="material-symbols-outlined text-sm">chevron_left</span>
              </button>
              <span class="text-xs font-bold text-text-secondary tabular-nums min-w-[3rem] text-center">{{ activeImage + 1 }}/{{ galleryImages.length }}</span>
              <button type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-text-main hover:bg-surface-vlm transition-colors duration-300" @click="activeImage = (activeImage + 1) % galleryImages.length">
                <span class="material-symbols-outlined text-sm">chevron_right</span>
              </button>
            </div>
          </div>

          <div v-if="galleryImages.length > 1" class="grid grid-cols-5 gap-3 mt-4">
            <button v-for="(img, i) in galleryImages" :key="i" type="button"
              class="aspect-square border-2 rounded-xl overflow-hidden flex items-center justify-center bg-surface-bright transition-all duration-300 p-2"
              :class="i === activeImage ? 'border-primary ring-2 ring-primary/20 shadow-md' : 'border-outline-variant hover:border-primary/50'"
              @click="activeImage = i">
              <img :src="img" :alt="`${product.name} ${i + 1}`" class="w-full h-full object-contain" loading="lazy">
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
              <span class="material-symbols-outlined text-primary text-[24px]">compress</span>
            </span>
            <div>
              <div class="text-[11px] text-text-muted font-bold uppercase tracking-wider mb-1">{{ product.strength_label }}</div>
              <div class="text-text-main font-extrabold text-[20px] tabular-nums">{{ product.strength_min }}<span v-if="product.strength_max && product.strength_max !== product.strength_min"> – {{ product.strength_max }}</span> <span class="text-text-muted text-[14px] font-medium">kN/m</span></div>
            </div>
          </div>

          <div class="text-text-secondary mb-10 leading-[1.8] text-[16px] max-w-2xl"><p>{{ product.description }}</p></div>

          <div v-if="product.specs?.length" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
            <div v-for="spec in product.specs.slice(0, 3)" :key="spec.label" class="bg-surface-bright border border-outline-variant rounded-2xl p-6 flex flex-col items-center text-center hover:shadow-[0_12px_32px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 group card-shine">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center mb-4 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                <span class="material-symbols-outlined text-primary-deep text-xl">{{ spec.icon }}</span>
              </div>
              <span class="text-[11px] font-bold text-text-muted uppercase tracking-[0.15em] mb-2">{{ spec.label }}</span>
              <span class="font-bold text-text-main text-[16px]">{{ spec.value }}</span>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-4 mt-auto border-t border-outline-variant pt-8">
            <router-link to="/contact" class="btn bg-primary text-white hover:bg-primary-dark rounded-full py-4 px-8 text-[15px] font-bold inline-flex items-center gap-3 shadow-[0_4px_20px_rgba(184,155,136,0.3)] transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
              <span class="material-symbols-outlined text-xl">request_quote</span> {{ t('product.quote') }}
            </router-link>
            <a href="tel:0909292530" class="btn border border-outline-variant text-text-main hover:border-primary hover:text-primary rounded-full py-4 px-8 text-[15px] font-bold inline-flex items-center gap-3 transition-all duration-300">
              <span class="material-symbols-outlined text-xl">call</span> Gọi tư vấn
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Tabs -->
    <section class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop mb-20">
      <div class="bg-surface-bright border border-outline-variant rounded-3xl overflow-hidden">
        <div class="flex flex-wrap border-b border-outline-variant bg-surface-vlm">
          <button v-for="tab in ['specs','applications','description'] as const" :key="tab"
            class="px-8 py-5 text-[14px] font-bold uppercase tracking-wider transition-colors duration-300 relative"
            :class="activeTab === tab ? 'text-primary-deep bg-surface-bright' : 'text-text-muted hover:text-primary'"
            @click="activeTab = tab">
            {{ tab === 'specs' ? t('product.specs') : tab === 'applications' ? t('product.applications') : 'Mô tả chi tiết' }}
            <span v-if="activeTab === tab" class="absolute bottom-0 left-0 right-0 h-1 bg-primary"></span>
          </button>
        </div>
        <div class="p-8 md:p-10">
          <transition name="page" mode="out-in">
            <div v-if="activeTab === 'specs' && product.specs?.length" key="specs">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="(spec, i) in product.specs" :key="i" class="flex items-start gap-4 p-5 bg-surface-vlm rounded-2xl border border-outline-variant hover:border-primary/30 hover:shadow-sm transition-all duration-300 group">
                  <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center shrink-0 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                    <span class="material-symbols-outlined text-primary group-hover:text-white">{{ spec.icon || 'check' }}</span>
                  </div>
                  <div class="flex-grow min-w-0">
                    <div class="text-[11px] font-bold text-text-muted uppercase tracking-[0.15em] mb-1">{{ spec.label }}</div>
                    <div class="text-text-main font-bold text-[16px]">{{ spec.value }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else-if="activeTab === 'applications' && product.applications?.length" key="applications" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <div v-for="app in product.applications" :key="app.slug" class="flex items-start gap-4 p-6 bg-surface-vlm border border-outline-variant rounded-2xl hover:shadow-[0_12px_32px_rgba(184,155,136,0.12)] hover:border-primary/30 transition-all duration-500 group card-shine">
                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-primary-deep shrink-0 group-hover:scale-110 transition-transform duration-500">
                  <span class="material-symbols-outlined text-xl">{{ appIcons[app.slug] || 'check' }}</span>
                </div>
                <div>
                  <span class="text-[15px] font-bold text-text-main block mb-1.5">{{ app.name }}</span>
                  <span class="text-[13px] text-text-secondary leading-relaxed">{{ app.description }}</span>
                </div>
              </div>
            </div>
            <div v-else-if="activeTab === 'description'" key="description" class="prose max-w-none text-text-secondary leading-[1.8] text-[16px]">
              <p>{{ product.description }}</p>
              <p class="mt-4">Với hơn {{ getYearsOfExperience() }} năm kinh nghiệm, CHƠN THÀNH cam kết cung cấp sản phẩm {{ product.name }} đạt tiêu chuẩn ISO 9001:2015, TCVN 9844:2013 và tiêu chuẩn Châu Âu EN, ASTM. Đội ngũ kỹ sư hỗ trợ tư vấn kỹ thuật miễn phí từ khâu thiết kế đến thi công.</p>
            </div>
          </transition>
        </div>
      </div>
    </section>

    <!-- Related Products -->
    <section v-if="related.length" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop pb-20">
      <div class="text-center mb-12 reveal">
        <span class="kicker inline-block mb-3">{{ t('product.related') }}</span>
        <h2 class="text-[32px] md:text-[40px] font-extrabold text-text-main tracking-tight">Sản phẩm liên quan</h2>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 stagger-grid">
        <ProductCard v-for="item in related" :key="item.slug" :product="item" />
      </div>
    </section>
  </div>

  <div v-else-if="notFound" class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16 text-center">
    <span class="material-symbols-outlined text-7xl text-outline-variant mb-6 block">inventory_2</span>
    <h1 class="text-[28px] text-text-main font-bold mb-6">{{ t('product.notFound') }}</h1>
    <router-link to="/products" class="btn bg-primary text-white hover:bg-primary-dark rounded-full inline-flex items-center gap-2">
      <span class="material-symbols-outlined text-lg">arrow_back</span> {{ t('product.backToList') }}
    </router-link>
  </div>
</template>
