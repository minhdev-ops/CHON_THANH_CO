<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import { useSettings } from '../composables/useSettings'
import type { Product } from '../types'
import PageHeader from '../components/PageHeader.vue'
import SectionHeader from '../components/SectionHeader.vue'
import { t } from '../i18n'
import { contactBodyText } from '../utils/contactLink'

const route = useRoute()
const { settings, load } = useSettings()

const DEFAULT_MAP_EMBED = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d550.0847160481649!2d106.60798745916034!3d10.810054716844608!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752bb2c658a587%3A0x3362c58ad0f4ce7c!2zQ8OUTkcgVFkgVE5INCBEViBWw4AgVE0gQ0jGoE4gVEjDgE5I!5e1!3m2!1sen!2s!4v1785813190548!5m2!1sen!2s'

const mapEmbed = computed(() => {
  const raw = settings.value?.['contact.map_embed']?.trim()
  if (!raw) return DEFAULT_MAP_EMBED
  const srcMatch = raw.match(/src=["']([^"']+)["']/i)
  return srcMatch?.[1] ?? raw
})

const allProducts = ref<Product[]>([])
const submitted = ref(false)
const submitting = ref(false)
const error = ref<string | null>(null)
const formErrors = ref<Record<string, string>>({})

const form = ref({
  name: '',
  phone: '',
  email: '',
  company: '',
  message: '',
})

type FieldKey = 'name' | 'phone' | 'email' | 'company' | 'message' | 'products'

const searchQuery = ref('')
const selectedProducts = ref<string[]>([])
const showSuggestions = ref(false)
const productInput = ref<HTMLInputElement | null>(null)
const productContainer = ref<HTMLElement | null>(null)

const onDocumentClick = (event: MouseEvent) => {
  const el = productContainer.value
  if (el && !el.contains(event.target as Node)) {
    showSuggestions.value = false
  }
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick))

const availableProducts = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  return allProducts.value.filter((p) => {
    if (!p.name || selectedProducts.value.includes(p.name)) return false
    return !q || p.name.toLowerCase().includes(q)
  })
})

onMounted(async () => {
  applyPrefill()
  load()
  try {
    let cursor: number | undefined
    const items: Product[] = []
    while (true) {
      const res = await api.products({ limit: 100, cursor })
      items.push(...res.data.filter((p) => p.name))
      if (res.next_cursor == null) break
      cursor = res.next_cursor
    }
    allProducts.value = items
  } catch {
    /* ignore */
  }
})

const addProduct = (input: string | Product) => {
  const name = typeof input === 'string' ? input : input.name
  if (!name) return
  if (!selectedProducts.value.includes(name)) {
    selectedProducts.value.push(name)
  }
  touchField('products')
  searchQuery.value = ''
  showSuggestions.value = false
  productInput.value?.focus()
}

const removeProduct = (index: number) => {
  selectedProducts.value.splice(index, 1)
  touchField('products')
}

const validateField = (field: FieldKey): string => {
  const name = form.value.name.trim()
  const phone = form.value.phone.trim()
  const email = form.value.email.trim()
  const company = form.value.company.trim()
  const message = form.value.message.trim()

  switch (field) {
    case 'name':
      if (!name) return t('contact.errName')
      if (name.length < 2) return t('contact.errNameShort')
      if (!/^[\p{L}\p{M}\s.'-]+$/u.test(name)) return t('contact.errNameInvalid')
      if (name.length > 150) return t('contact.errNameLong')
      return ''
    case 'phone':
      if (!phone) return t('contact.errPhone')
      if (!/^[0-9+][0-9+\-\s().]*$/.test(phone)) return t('contact.errPhoneInvalid')
      if (phone.replace(/\D/g, '').length < 9) return t('contact.errPhoneShort')
      if (phone.length > 20) return t('contact.errPhoneLong')
      return ''
    case 'email':
      if (!email) return t('contact.errEmail')
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return t('contact.errEmailInvalid')
      if (email.length > 255) return t('contact.errEmailLong')
      return ''
    case 'company':
      if (!company) return ''
      if (company.length < 2) return t('contact.errCompanyShort')
      if (company.length > 150) return t('contact.errCompanyLong')
      return ''
    case 'products':
      if (selectedProducts.value.length > 20) return t('contact.errProducts')
      return ''
    case 'message':
      if (!message) return t('contact.errMessage')
      if (message.length < 5) return t('contact.errMessageShort')
      if (message.length > 5000) return t('contact.errMessageLong')
      return ''
  }
}

const touchField = (field: FieldKey) => {
  const message = validateField(field)
  if (message) formErrors.value[field] = message
  else if (formErrors.value[field]) delete formErrors.value[field]
}

const queryValue = (v: unknown): string => (Array.isArray(v) ? String(v[0] ?? '') : String(v ?? ''))

// CTA toàn site dẫn tới /contact?subject=...&body=...&product=... — điền sẵn form.
const applyPrefill = () => {
  const product = queryValue(route.query.product).trim()
  if (product && !selectedProducts.value.includes(product)) {
    selectedProducts.value.push(product)
    touchField('products')
  }

  if (form.value.message) return
  const body = queryValue(route.query.body).trim()
  const subject = queryValue(route.query.subject).trim() as keyof typeof contactBodyText
  const text = body || contactBodyText[subject] || ''
  if (text) form.value.message = text
}

watch(() => [route.query.subject, route.query.body, route.query.product], applyPrefill)

const validate = (): boolean => {
  const fields: FieldKey[] = ['name', 'phone', 'email', 'company', 'products', 'message']
  const errors: Record<string, string> = {}
  for (const field of fields) {
    const message = validateField(field)
    if (message) errors[field] = message
  }
  formErrors.value = errors
  return Object.keys(errors).length === 0
}

const scrollToError = (key: string) => {
  const el = document.querySelector<HTMLElement>(`[data-error-field="${key}"]`)
  el?.scrollIntoView({ behavior: 'smooth', block: 'center' })
  el?.querySelector<HTMLInputElement | HTMLTextAreaElement>('input, textarea')?.focus({ preventScroll: true })
}

const handleSubmit = async () => {
  form.value.name = form.value.name.trim()
  form.value.phone = form.value.phone.trim()
  form.value.email = form.value.email.trim()
  form.value.company = form.value.company.trim()
  form.value.message = form.value.message.trim()

  if (!validate()) {
    const first = Object.keys(formErrors.value)[0]
    if (first) scrollToError(first)
    return
  }

  submitting.value = true
  error.value = null
  try {
    const payload = { ...form.value, products: selectedProducts.value }
    const res = await api.contact(payload)
    if (!res.ok) {
      let message = t('common.errorGeneric')
      try {
        const body = await res.json()
        if (body.errors) {
          const mapped: Record<string, string> = {}
          for (const [key, value] of Object.entries(body.errors)) {
            mapped[key] = Array.isArray(value) ? value[0] : String(value)
          }
          formErrors.value = mapped
          const firstKey = Object.keys(mapped)[0]
          if (firstKey) {
            scrollToError(firstKey)
            message = mapped[firstKey]
          }
        } else if (body.message) {
          message = body.message
        }
      } catch {
        /* ignore */
      }
      throw new Error(message)
    }
    submitted.value = true
    form.value = { name: '', phone: '', email: '', company: '', message: '' }
    selectedProducts.value = []
    searchQuery.value = ''
    formErrors.value = {}
    window.setTimeout(() => {
      submitted.value = false
    }, 8000)
  } catch (e) {
    error.value = e instanceof Error ? e.message : t('common.errorGeneric')
  } finally {
    submitting.value = false
  }
}

const breadcrumbs = computed(() => [
  { label: t('nav.home'), to: '/' },
  { label: t('nav.contact') }
])
</script>

<template>
  <div class="max-w-full overflow-x-hidden">
    <!-- Hero Banner -->
    <PageHeader :title="t('nav.contact')" :breadcrumbs="breadcrumbs" />

    <section class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-16 md:py-20">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <SectionHeader align="center" kicker="Contact Us" :title="t('contact.formTitle')" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-16">
        <!-- Contact Form -->
        <div class="bg-surface-bright border border-outline-variant rounded-3xl p-8 md:p-10 shadow-sm hover:shadow-[0_16px_48px_rgba(184,155,136,0.12)] transition-all duration-500 reveal-left card-shine glow-card">
          <div v-if="submitted" class="mb-6 flex items-start gap-3 bg-green-50 border border-green-200 rounded-2xl p-4 animate-fade-in-up">
            <i class="fa text-green-600 flex-shrink-0 fa-circle-check"></i>
            <p class="text-sm text-green-800 font-medium">{{ t('contact.success') }}</p>
          </div>
          <div v-if="error" class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 rounded-2xl p-4">
            <i class="fa text-red-500 flex-shrink-0 fa-circle-exclamation"></i>
            <p class="text-sm text-red-600 font-medium">{{ error }}</p>
          </div>
          <form class="space-y-6" novalidate @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div data-error-field="name">
                <label class="block text-[11px] font-bold text-text-muted mb-2.5 uppercase tracking-[0.15em]" for="name">{{ t('contact.name') }}</label>
                <input id="name" v-model="form.name" type="text" :aria-invalid="!!formErrors.name" :class="[formErrors.name ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-outline-variant focus:border-primary focus:ring-primary']" class="w-full bg-surface-vlm border rounded-2xl px-5 py-4 text-[16px] md:text-sm text-text-main input-premium outline-none scroll-mt-[90px] transition-all duration-300" @input="touchField('name')" @blur="touchField('name')">
                <p v-if="formErrors.name" class="mt-2 text-xs text-red-500 font-medium">{{ formErrors.name }}</p>
              </div>
              <div data-error-field="phone">
                <label class="block text-[11px] font-bold text-text-muted mb-2.5 uppercase tracking-[0.15em]" for="phone">{{ t('contact.phone') }}</label>
                <input id="phone" v-model="form.phone" type="tel" :aria-invalid="!!formErrors.phone" :class="[formErrors.phone ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-outline-variant focus:border-primary focus:ring-primary']" class="w-full bg-surface-vlm border rounded-2xl px-5 py-4 text-[16px] md:text-sm text-text-main input-premium outline-none scroll-mt-[90px] transition-all duration-300" @input="touchField('phone')" @blur="touchField('phone')">
                <p v-if="formErrors.phone" class="mt-2 text-xs text-red-500 font-medium">{{ formErrors.phone }}</p>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div data-error-field="email">
                <label class="block text-[11px] font-bold text-text-muted mb-2.5 uppercase tracking-[0.15em]" for="email">{{ t('contact.email') }}</label>
                <input id="email" v-model="form.email" type="email" :aria-invalid="!!formErrors.email" :class="[formErrors.email ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-outline-variant focus:border-primary focus:ring-primary']" class="w-full bg-surface-vlm border rounded-2xl px-5 py-4 text-[16px] md:text-sm text-text-main input-premium outline-none scroll-mt-[90px] transition-all duration-300" @input="touchField('email')" @blur="touchField('email')">
                <p v-if="formErrors.email" class="mt-2 text-xs text-red-500 font-medium">{{ formErrors.email }}</p>
              </div>
              <div data-error-field="company">
                <label class="block text-[11px] font-bold text-text-muted mb-2.5 uppercase tracking-[0.15em]" for="company">{{ t('contact.company') }}</label>
                <input id="company" v-model="form.company" type="text" :aria-invalid="!!formErrors.company" :class="[formErrors.company ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-outline-variant focus:border-primary focus:ring-primary']" class="w-full bg-surface-vlm border rounded-2xl px-5 py-4 text-[16px] md:text-sm text-text-main input-premium outline-none scroll-mt-[90px] transition-all duration-300" @input="touchField('company')">
                <p v-if="formErrors.company" class="mt-2 text-xs text-red-500 font-medium">{{ formErrors.company }}</p>
              </div>
            </div>
            <div data-error-field="products">
              <label class="block text-[11px] font-bold text-text-muted mb-2.5 uppercase tracking-[0.15em]" for="product-search">{{ t('contact.products') }}</label>
              <div ref="productContainer" class="relative">
                <div :class="[formErrors.products ? 'border-red-400 focus-within:border-red-400 focus-within:ring-red-400' : 'border-outline-variant focus-within:border-primary focus-within:ring-primary']" class="w-full bg-surface-vlm border rounded-2xl px-5 py-4 input-premium transition-all duration-300">
                  <div v-if="selectedProducts.length" class="flex flex-wrap gap-2 mb-2">
                    <span v-for="(product, index) in selectedProducts" :key="product" class="inline-flex items-center gap-1 bg-primary/10 text-primary-deep rounded-full pl-3.5 pr-1.5 py-1 text-xs font-bold transition-all duration-300 hover:bg-primary/20">
                      {{ product }}
                      <button type="button" class="w-5 h-5 rounded-full flex items-center justify-center hover:bg-primary/20 transition-colors" :aria-label="`${t('contact.remove')} ${product}`" @click="removeProduct(index)">
                        <i class="fa text-xs leading-none fa-xmark"></i>
                      </button>
                    </span>
                  </div>
                  <div class="flex items-center gap-3">
                    <i class="fa text-text-muted text-lg fa-magnifying-glass"></i>
                    <input
                      id="product-search"
                      ref="productInput"
                      v-model="searchQuery"
                      type="text"
                      :placeholder="t('contact.productSearch')"
                      autocomplete="off"
                      class="w-full max-md:h-11 bg-transparent text-[16px] md:text-sm text-text-main placeholder:text-text-muted outline-none scroll-mt-[90px]"
                      @focus="showSuggestions = true"
                      @input="showSuggestions = true; touchField('products')"
                      @keydown.enter.prevent="availableProducts[0] && addProduct(availableProducts[0])"
                      @keydown.esc="showSuggestions = false"
                    >
                  </div>
                </div>
                <div v-if="showSuggestions && availableProducts.length" class="absolute left-0 right-0 top-full mt-2 bg-surface-bright border border-outline-variant/30 rounded-2xl shadow-2xl z-20 max-h-96 overflow-y-auto">
                  <div class="px-5 py-3 border-b border-outline-variant/20 bg-surface-vlm text-xs font-semibold text-text-secondary uppercase tracking-wider sticky top-0 backdrop-blur-md rounded-t-2xl">
                    {{ t('contact.suggestions') }}
                  </div>
                  <div
                    v-for="prod in availableProducts"
                    :key="prod.slug"
                    @click="addProduct(prod.name)"
                    class="flex items-center gap-4 p-4 cursor-pointer hover:bg-surface-vlm transition-colors duration-200 border-b border-outline-variant/10 last:border-0 group"
                  >
                    <div class="w-12 h-12 rounded-xl bg-surface flex items-center justify-center overflow-hidden border border-outline-variant/10 group-hover:border-primary/20 transition-colors">
                      <img :src="prod.image" :alt="prod.name" class="w-full h-full object-contain p-1">
                    </div>
                    <div class="flex-1">
                      <div class="font-semibold text-[14px] text-text-main group-hover:text-primary transition-colors">{{ prod.name }}</div>
                      <div class="text-[12px] text-text-secondary">{{ prod.category?.name }}</div>
                    </div>
                  </div>
                </div>

                <!-- Empty state for suggestions -->
                <div v-if="showSuggestions && searchQuery.trim() && !availableProducts.length" class="absolute left-0 right-0 top-full mt-2 bg-surface-bright border border-outline-variant/30 rounded-2xl shadow-2xl z-20">
                  <div class="p-8 flex flex-col items-center justify-center text-center">
                    <i class="fa text-4xl text-outline-variant mb-3 fa-magnifying-glass-minus"></i>
                    <p class="text-text-main font-semibold mb-1">{{ t('contact.noProductsFound') }}</p>
                    <p class="text-text-secondary text-[13px]">{{ t('contact.tryDifferentKeyword') }}</p>
                  </div>
                </div>
              </div>
              <p v-if="formErrors.products" class="mt-2 text-xs text-red-500">{{ formErrors.products }}</p>
            </div>
            <div data-error-field="message">
              <label class="block text-[11px] font-bold text-text-muted mb-2.5 uppercase tracking-[0.15em]" for="message">{{ t('contact.message') }}</label>
              <textarea id="message" v-model="form.message" rows="4" :aria-invalid="!!formErrors.message" :class="[formErrors.message ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-outline-variant focus:border-primary focus:ring-primary']" class="w-full bg-surface-vlm border rounded-2xl px-5 py-4 text-[16px] md:text-sm text-text-main input-premium outline-none scroll-mt-[90px] transition-all duration-300 resize-y" @input="touchField('message')" @blur="touchField('message')"></textarea>
              <p v-if="formErrors.message" class="mt-2 text-xs text-red-500 font-medium">{{ formErrors.message }}</p>
            </div>
            <button type="submit" :disabled="submitting" class="btn btn-primary btn-magnetic w-full md:w-auto mt-4 inline-flex items-center justify-center gap-2 group disabled:opacity-60">
              <span>{{ submitting ? t('contact.submitting') : t('contact.submit') }}</span>
              <i class="fa text-base group-hover:translate-x-1 transition-transform duration-300 fa-paper-plane"></i>
            </button>
          </form>
        </div>

        <!-- Contact Info -->
        <div class="flex flex-col gap-6 reveal-right">
          <div class="bg-surface-bright border border-outline-variant rounded-3xl p-8 md:p-10 shadow-sm">
            <h2 class="text-[24px] text-text-main mb-8 pb-5 border-b border-outline-variant font-bold tracking-tight">{{ t('contact.infoTitle') }}</h2>
            <ul class="space-y-6">
              <li class="flex items-start gap-4 group">
                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0">
                  <i class="fa text-primary-deep text-xl fa-location-dot"></i>
                </div>
                <div>
                  <span class="block text-[11px] font-bold text-text-muted mb-1.5 uppercase tracking-[0.15em]">{{ t('contact.headquarters') }}</span>
                  <a :href="settings?.['social.ggmap'] || 'https://www.google.com/maps/place/C%C3%94NG+TY+TNHH+DV+V%C3%80+TM+CH%C6%A0N+TH%C3%80NH/@10.8100547,106.6079875,140m/data=!3m1!1e3!4m6!3m5!1s0x31752bb2c658a587:0x3362c58ad0f4ce7c!8m2!3d10.8102715!4d106.6081321!16s%2Fg%2F11h6mklcm8'" target="_blank" rel="noopener noreferrer" class="block text-[15px] text-text-secondary leading-relaxed hover:text-primary transition-colors max-md:py-2">{{ settings?.['contact.address'] || '416A Đường CC2, Phường Tây Thạnh, Thành Phố Hồ Chí Minh' }}</a>
                </div>
              </li>
              <li class="flex items-start gap-4 group">
                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0">
                  <i class="fa text-primary-deep text-xl fa-phone"></i>
                </div>
                <div>
                  <span class="block text-[11px] font-bold text-text-muted mb-1.5 uppercase tracking-[0.15em]">{{ t('contact.phoneLabel') }}</span>
                  <a
                    :href="`tel:${(settings?.['contact.phone'] || '0909 292 530').replace(/[^\d+]/g, '')}`"
                    class="block text-[15px] text-primary-deep font-bold hover:text-primary transition-colors duration-300 animated-underline max-md:min-h-[44px] max-md:flex max-md:items-center"
                  >
                    {{ settings?.['contact.phone'] || '0909 292 530' }}
                  </a>
                </div>
              </li>
              <li class="flex items-start gap-4 group">
                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0">
                  <i class="fa text-primary-deep text-xl fa-envelope"></i>
                </div>
                <div>
                  <span class="block text-[11px] font-bold text-text-muted mb-1.5 uppercase tracking-[0.15em]">{{ t('contact.emailLabel') }}</span>
                  <span class="block text-[15px] text-text-secondary leading-relaxed">{{ settings?.['contact.email'] || 'chonthanhco@gmail.com' }}</span>
                </div>
              </li>
              <li class="flex items-start gap-4 group">
                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0">
                  <i class="fa text-primary-deep text-xl fa-clock"></i>
                </div>
                <div>
                  <span class="block text-[11px] font-bold text-text-muted mb-1.5 uppercase tracking-[0.15em]">{{ t('contact.hoursLabel') }}</span>
                  <span class="block text-[15px] text-text-secondary leading-relaxed">{{ settings?.['contact.working_hours'] || 'Thứ 2 - Thứ 6: 08:30 - 17:30' }}</span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Map -->
    <section class="w-full h-[400px] border-t border-outline-variant/20 relative bg-surface-container-high overflow-hidden">
      <iframe
        class="absolute inset-0 w-full h-full"
        :src="mapEmbed"
        style="border: 0"
        allowfullscreen
        loading="lazy"
        referrerpolicy="strict-origin-when-cross-origin"
      ></iframe>
    </section>
  </div>
</template>
