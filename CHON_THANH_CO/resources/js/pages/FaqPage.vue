<script setup lang="ts">
import { ref, computed } from 'vue'
import { api } from '../api/client'
import { useApiData } from '../composables/useApiData'
import PageHeader from '../components/PageHeader.vue'
import ErrorState from '../components/ErrorState.vue'
import CTABanner from '../components/CTABanner.vue'
import { t } from '../i18n'
import { fallbackFaqs } from '../types/fallback'

const { data: faqs, error, load } = useApiData(
  async () => (await api.faqs()).data,
  () => fallbackFaqs
)

const openIndex = ref<number | null>(0)
const toggle = (index: number) => { openIndex.value = openIndex.value === index ? null : index }
const searchQuery = ref('')

const categories = ['Tất cả', 'Sản phẩm', 'Giao hàng', 'Chất lượng', 'Hỗ trợ', 'Thanh toán']
const activeCategory = ref('Tất cả')

const categoryKeywords: Record<string, string[]> = {
  'Sản phẩm': ['sản phẩm', 'loại', 'chủng loại', 'mã', 'độ bền', 'kích thước', 'quy cách'],
  'Giao hàng': ['giao hàng', 'vận chuyển', 'thời gian', 'ship', 'đơn hàng', 'nhận hàng'],
  'Chất lượng': ['chất lượng', 'chứng nhận', 'iso', 'tiêu chuẩn', 'kiểm định', 'đảm bảo'],
  'Hỗ trợ': ['hỗ trợ', 'tư vấn', 'kỹ thuật', 'liên hệ', 'hotline', 'phản hồi'],
  'Thanh toán': ['thanh toán', 'giá', 'báo giá', 'hợp đồng', 'chiết khấu', 'invoice'],
}

const filteredFaqs = computed(() => {
  let list = faqs.value ?? fallbackFaqs
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter((f) => f.question.toLowerCase().includes(q) || f.answer.toLowerCase().includes(q))
  }
  if (activeCategory.value !== 'Tất cả') {
    const keywords = categoryKeywords[activeCategory.value] ?? []
    list = list.filter((f) => {
      const text = (f.question + ' ' + f.answer).toLowerCase()
      return keywords.some((kw) => text.includes(kw))
    })
  }
  return list
})

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.faq') }
])
</script>

<template>
  <div>
    <PageHeader :title="t('nav.faq')" :breadcrumbs="breadcrumbs" />

    <section class="relative py-16 md:py-20 overflow-hidden">
      <div class="absolute inset-0 mesh-bg opacity-40"></div>
      <div class="relative max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-14 reveal">
          <span class="kicker mb-4 block">FAQ</span>
          <h2 class="text-[32px] md:text-[42px] text-text-main font-extrabold tracking-tight mb-5">Câu hỏi thường gặp</h2>
          <p class="text-text-secondary text-[16px] md:text-[17px] leading-relaxed max-w-xl mx-auto">Những câu hỏi phổ biến về sản phẩm và dịch vụ của chúng tôi.</p>
        </div>

        <div v-if="error && !faqs?.length" class="bg-surface-bright border border-outline-variant rounded-3xl">
          <ErrorState :message="error" @retry="load" />
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
          <div class="lg:col-span-9 space-y-4">
            <div v-for="(faq, i) in filteredFaqs" :key="i"
              class="glow-card border border-outline-variant rounded-3xl overflow-hidden transition-all duration-500 bg-surface-bright group card-shine reveal"
              :class="[`reveal-delay-${(i % 3) + 1}`, openIndex === i ? 'shadow-[0_12px_40px_rgba(184,155,136,0.15)] border-primary/40 ring-1 ring-primary/10' : 'hover:shadow-[0_8px_30px_rgba(184,155,136,0.08)] hover:border-primary/20']">
              <button @click="toggle(i)" class="w-full flex justify-between items-center p-6 md:p-8 text-left transition-colors duration-300 group/btn">
                <div class="flex items-center gap-4 flex-grow">
                  <span class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center shrink-0 text-primary-deep font-bold text-[14px] transition-all duration-300 group-hover/btn:bg-primary group-hover/btn:text-white group-hover/btn:scale-110">{{ String(i+1).padStart(2,'0') }}</span>
                  <span class="font-bold pr-6 transition-colors duration-300 text-[16px] md:text-[17px]"
                    :class="openIndex === i ? 'text-primary-deep' : 'text-text-main group-hover/btn:text-primary'">{{ faq.question }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all duration-500"
                  :class="openIndex === i ? 'bg-primary text-white rotate-180 shadow-[0_4px_16px_rgba(184,155,136,0.3)]' : 'bg-surface-vlm text-text-muted group-hover/btn:bg-primary/10 group-hover/btn:text-primary'">
                  <span class="material-symbols-outlined text-xl">expand_more</span>
                </div>
              </button>
              <div class="overflow-hidden transition-all duration-500 ease-in-out"
                :style="openIndex === i ? { maxHeight: '800px', opacity: 1 } : { maxHeight: '0px', opacity: 0 }">
                <div class="px-6 pb-6 md:px-8 md:pb-8 text-[15px] text-text-secondary border-t border-outline-variant/40 pt-5 leading-[1.8]">
                  {{ faq.answer }}
                </div>
              </div>
            </div>

            <div v-if="filteredFaqs.length === 0" class="py-16 text-center bg-surface-bright border border-outline-variant rounded-3xl card-shine glow-card">
              <span class="material-symbols-outlined text-6xl text-outline-variant mb-4 block">search_off</span>
              <p class="text-text-secondary font-medium mb-6 text-[16px]">Không tìm thấy câu hỏi phù hợp với từ khóa "{{ searchQuery }}".</p>
              <button class="btn bg-primary text-white hover:bg-primary-dark rounded-full px-6 py-3 font-bold text-[14px]" @click="searchQuery = ''">Xóa bộ lọc</button>
            </div>
          </div>

          <div class="lg:col-span-3 space-y-6">
            <div class="bg-surface-bright border border-outline-variant rounded-3xl p-6 card-shine glow-card reveal reveal-delay-1">
              <div class="flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary text-[22px]">search</span>
                <h3 class="font-extrabold text-text-main text-[16px]">Tìm kiếm</h3>
              </div>
              <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-muted text-[18px]">search</span>
                <input v-model="searchQuery" type="text" placeholder="Nhập từ khóa..."
                  class="w-full bg-canvas border border-outline-variant rounded-full pl-11 pr-4 py-3.5 text-[14px] text-text-main outline-none focus:border-primary focus:ring-4 focus:ring-primary/15 transition-all duration-300">
              </div>
            </div>

            <div class="bg-surface-bright border border-outline-variant rounded-3xl p-6 card-shine glow-card reveal reveal-delay-2">
              <div class="flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary text-[22px]">category</span>
                <h3 class="font-extrabold text-text-main text-[16px]">Danh mục</h3>
              </div>
              <div class="flex flex-wrap gap-2">
                <button v-for="cat in categories" :key="cat"
                  class="px-4 py-2 rounded-full font-bold text-[11px] uppercase tracking-[0.1em] transition-all duration-300"
                  :class="activeCategory === cat ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-canvas text-text-secondary border border-outline-variant hover:border-primary/40 hover:text-primary-deep'"
                  @click="activeCategory = cat">
                  {{ cat }}
                </button>
              </div>
            </div>

            <div class="bg-gradient-to-br from-primary to-primary-dark rounded-3xl p-6 text-white reveal reveal-delay-3">
              <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[24px]">help_center</span>
              </div>
              <h3 class="font-extrabold text-[17px] mb-2">Vẫn chưa tìm thấy?</h3>
              <p class="text-white/80 text-[14px] mb-5 leading-relaxed">Đội ngũ hỗ trợ sẵn sàng giải đáp mọi thắc mắc.</p>
              <router-link to="/contact" class="inline-flex items-center gap-2 bg-white text-primary-dark px-5 py-2.5 rounded-full font-bold text-[13px] hover:bg-white/90 transition-all duration-300 hover:shadow-lg hover:scale-105">
                {{ t('faq.ctaButton') }}
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <CTABanner
      :title="t('faq.ctaTitle')"
      :text="t('faq.ctaText')"
      :linkLabel="t('faq.ctaButton')"
    />
  </div>
</template>
