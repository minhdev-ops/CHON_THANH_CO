<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useSettings } from '../composables/useSettings'
import { buildContactLink } from '../utils/contactLink'

/**
 * Thanh điều hướng dưới (mobile app tab bar).
 * Chỉ hiển thị dưới 768px — desktop giữ nguyên floating contact widget.
 */
const route = useRoute()
const { settings, load } = useSettings()

const sheetOpen = ref(false)
const menuOpen = ref(false)
let menuObserver: MutationObserver | null = null

// Trang chi tiết sản phẩm có thanh CTA báo giá riêng ở đáy → không chồng 2 thanh.
const hiddenOnRoute = computed(() => /^\/products\/[^/]+/.test(route.path))
const visible = computed(() => !hiddenOnRoute.value && !menuOpen.value)

const hotline = computed(() => settings.value?.['contact.phone'] || '0909 292 530')
const hotlineHref = computed(() => `tel:${hotline.value.replace(/[^\d+]/g, '')}`)
const quoteLink = computed(() => buildContactLink({ subject: 'quote-24h' }))

const tabs = computed(() => [
  { key: 'home', to: '/', label: 'Trang chủ', icon: 'fa-house', match: (p: string) => p === '/' },
  { key: 'products', to: '/products', label: 'Sản phẩm', icon: 'fa-boxes-stacked', match: (p: string) => p.startsWith('/products') },
  { key: 'projects', to: '/projects', label: 'Dự án', icon: 'fa-layer-group', match: (p: string) => p.startsWith('/projects') },
  { key: 'news', to: '/news', label: 'Tin tức', icon: 'fa-newspaper', match: (p: string) => p.startsWith('/news') },
])

const isActive = (match: (p: string) => boolean) => match(route.path)

const openSheet = () => {
  sheetOpen.value = true
  document.body.style.overflow = 'hidden'
}
const closeSheet = () => {
  // Chỉ mở khoá scroll khi sheet đang mở — tránh ghi đè khoá scroll của drawer menu.
  if (!sheetOpen.value) return
  sheetOpen.value = false
  document.body.style.overflow = ''
}

const quickContacts = computed(() => [
  {
    name: 'Hotline tư vấn',
    value: hotline.value,
    href: hotlineHref.value,
    external: false,
    icon: 'fa-phone',
    color: '#D84315',
    bg: 'rgba(216,67,21,0.10)',
  },
  {
    name: 'Zalo',
    value: 'Chat Zalo',
    href: settings.value?.['social.zalo']?.trim() || 'https://zalo.me/0909292530',
    external: true,
    icon: 'fa-comment-dots',
    color: '#0068FF',
    bg: 'rgba(0,104,255,0.10)',
  },
  {
    name: 'WhatsApp',
    value: 'Nhắn WhatsApp',
    href: settings.value?.['social.whatsapp']?.trim() || 'https://wa.me/84909292530',
    external: true,
    icon: 'fa-whatsapp',
    color: '#128C4B',
    bg: 'rgba(37,211,102,0.12)',
  },
  {
    name: 'WeChat',
    value: 'WeChat',
    href: settings.value?.['social.wechat']?.trim() || 'https://wechat.com',
    external: true,
    icon: 'fa-comment',
    color: '#07A64F',
    bg: 'rgba(7,193,96,0.12)',
  },
  {
    name: 'Bản đồ',
    value: 'Chỉ đường đến công ty',
    href: settings.value?.['social.ggmap']?.trim() || 'https://maps.app.goo.gl/xstN8U1DZsLERZeGA',
    external: true,
    icon: 'fa-location-dot',
    color: '#4A403B',
    bg: 'rgba(74,64,59,0.08)',
  },
])

/** Ẩn tab bar khi drawer menu mở + báo cho layout biết để chừa khoảng trống đáy. */
const syncShell = () => {
  const open = document.documentElement.classList.contains('mobile-menu-open')
  menuOpen.value = open
  document.documentElement.classList.toggle('mobile-tabbar', !hiddenOnRoute.value && !open)
  // Trang có thanh CTA riêng ở đáy (vd. chi tiết sản phẩm) vẫn cần chừa khoảng trống.
  document.documentElement.classList.toggle('mobile-page-bottombar', hiddenOnRoute.value && !open)
  if (open) closeSheet()
}

onMounted(() => {
  load()
  syncShell()
  menuObserver = new MutationObserver(syncShell)
  menuObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
})

onBeforeUnmount(() => {
  menuObserver?.disconnect()
  document.documentElement.classList.remove('mobile-tabbar', 'mobile-page-bottombar')
  document.body.style.overflow = ''
})

watch(hiddenOnRoute, () => syncShell())
watch(() => route.path, () => closeSheet())
</script>

<template>
  <nav
    v-show="visible"
    class="mobile-tabbar-ui md:hidden fixed inset-x-0 bottom-0 z-40 bg-surface-1/95 backdrop-blur-xl border-t border-outline-variant shadow-[0_-6px_24px_rgba(74,64,59,0.08)]"
    aria-label="Điều hướng nhanh"
  >
    <div class="grid grid-cols-5 items-stretch">
      <router-link
        v-for="tab in tabs"
        :key="tab.key"
        :to="tab.to"
        class="group relative flex flex-col items-center justify-center gap-1 pt-2 pb-1.5 min-h-[56px] text-[11px] font-semibold leading-none transition-colors duration-200 active:bg-primary/5"
        :class="isActive(tab.match) ? 'text-primary-deep' : 'text-text-muted'"
        :aria-current="isActive(tab.match) ? 'page' : undefined"
      >
        <span
          class="flex items-center justify-center w-11 h-6 rounded-full transition-colors duration-200"
          :class="isActive(tab.match) ? 'bg-primary/15' : 'bg-transparent'"
        >
          <i class="fa text-[17px]" :class="tab.icon"></i>
        </span>
        <span class="truncate px-0.5">{{ tab.label }}</span>
      </router-link>

      <button
        type="button"
        class="relative flex flex-col items-center justify-center gap-1 pt-2 pb-1.5 min-h-[56px] text-[11px] font-semibold leading-none text-[#D84315] active:bg-[#D84315]/5 transition-colors duration-200"
        aria-haspopup="dialog"
        :aria-expanded="sheetOpen"
        aria-label="Liên hệ nhanh"
        @click="openSheet"
      >
        <span class="flex items-center justify-center w-11 h-6 rounded-full bg-[#D84315]/10">
          <i class="fa text-[17px] fa-headset"></i>
        </span>
        <span class="truncate px-0.5">Liên hệ</span>
      </button>
    </div>
  </nav>

  <!-- ═══ Contact bottom sheet ═══ -->
  <Teleport to="body">
    <Transition name="sheet">
      <div
        v-if="sheetOpen"
        class="md:hidden fixed inset-0 z-[80]"
        role="dialog"
        aria-modal="true"
        aria-label="Liên hệ nhanh"
      >
        <div class="absolute inset-0 bg-[#2A2420]/50 backdrop-blur-[2px]" @click="closeSheet"></div>

        <div class="absolute inset-x-0 bottom-0 bg-surface-1 rounded-t-[24px] shadow-[0_-16px_48px_rgba(26,26,26,0.22)] max-h-[86dvh] flex flex-col">
          <div class="pt-2.5 pb-1 flex justify-center shrink-0">
            <span class="w-10 h-1.5 rounded-full bg-outline-variant"></span>
          </div>

          <div class="px-5 pb-3 flex items-start justify-between gap-3 shrink-0">
            <div>
              <p class="text-[11px] font-bold text-primary uppercase tracking-[0.18em]">Chơn Thành</p>
              <h2 class="text-[18px] font-extrabold text-text-main leading-tight">Liên hệ nhanh</h2>
            </div>
            <button
              type="button"
              class="w-11 h-11 shrink-0 -mr-1 rounded-full bg-surface-vlm text-text-main flex items-center justify-center active:scale-95 transition-transform"
              aria-label="Đóng"
              @click="closeSheet"
            >
              <i class="fa text-[18px] fa-xmark"></i>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto overscroll-contain px-4 pb-2">
            <a
              v-for="c in quickContacts"
              :key="c.name"
              :href="c.href"
              :target="c.external ? '_blank' : undefined"
              :rel="c.external ? 'noopener noreferrer' : undefined"
              class="flex items-center gap-3 min-h-[56px] px-3 py-2.5 rounded-2xl active:bg-surface-vlm transition-colors"
            >
              <span class="w-11 h-11 rounded-full flex items-center justify-center shrink-0" :style="{ backgroundColor: c.bg, color: c.color }">
                <i class="fa text-[18px]" :class="[c.icon, c.icon === 'fa-whatsapp' || c.icon === 'fa-comment-dots' ? 'fa-brands' : '']"></i>
              </span>
              <span class="flex-1 min-w-0">
                <span class="block text-[15px] font-bold text-text-main leading-tight">{{ c.name }}</span>
                <span class="block text-[13px] text-text-secondary truncate tabular-nums">{{ c.value }}</span>
              </span>
              <i class="fa text-[14px] text-text-muted fa-chevron-right"></i>
            </a>
          </div>

          <div class="shrink-0 px-4 pt-3 pb-[calc(1rem+env(safe-area-inset-bottom,0px))] border-t border-outline-variant bg-surface-1">
            <router-link
              :to="quoteLink"
              class="w-full min-h-[50px] rounded-xl bg-primary-deep text-white text-[13px] font-bold uppercase tracking-[0.12em] flex items-center justify-center gap-2 shadow-md active:scale-[0.99] transition-transform"
              @click="closeSheet"
            >
              <i class="fa text-[16px] fa-file-pen"></i>
              Yêu cầu báo giá
            </router-link>
            <p class="mt-2 text-center text-[11px] text-text-muted flex items-center justify-center gap-1.5">
              <i class="fa text-[12px] fa-clock"></i>
              {{ settings?.['contact.working_hours'] || 'T2 – T6: 8:30 – 17:30' }}
            </p>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.mobile-tabbar-ui {
  padding-bottom: env(safe-area-inset-bottom, 0px);
}

.sheet-enter-active,
.sheet-leave-active {
  transition: opacity 0.25s ease;
}
.sheet-enter-active > div:last-child,
.sheet-leave-active > div:last-child {
  transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}
.sheet-enter-from,
.sheet-leave-to {
  opacity: 0;
}
.sheet-enter-from > div:last-child,
.sheet-leave-to > div:last-child {
  transform: translateY(100%);
}
</style>
