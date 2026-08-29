<script setup lang="ts">
import { api } from '../api/client'
import type { Category } from '../types'
import { useRoute } from 'vue-router'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useSettings } from '../composables/useSettings'
import { t, locale, setLocale } from '../i18n'

interface NavLinkItem     { type: 'link';     labelKey: string; to: string }
interface NavDropdownItem { type: 'dropdown'; name: 'about' | 'products'; labelKey: string; to: string }
type NavItem = NavLinkItem | NavDropdownItem

const route         = useRoute()
const isScrolled    = ref(false)
const isMobileOpen  = ref(false)
const mobileSection = ref<string | null>(null)
const openDropdown  = ref<string | null>(null)
const categories    = ref<Category[]>([])
const { settings, load } = useSettings()

const hotline     = computed(() => settings.value?.phone || settings.value?.['contact.phone'] || '0909 292 530')
const hotlineHref = computed(() => `tel:${hotline.value.replace(/[^\d+]/g, '')}`)
const email       = computed(() => settings.value?.['contact.email'] || 'chonthanhtco@gmail.com')

const aboutSubLinks = [
  { labelKey: 'nav.aboutUs',       to: '/about' },
  { labelKey: 'nav.capability',    to: '/about/capability' },
  { labelKey: 'nav.certification', to: '/about/certification' },
]

const navItems: NavItem[] = [
  { type: 'link',                        labelKey: 'nav.home',         to: '/' },
  { type: 'dropdown', name: 'about',    labelKey: 'nav.about',        to: '/about' },
  { type: 'dropdown', name: 'products', labelKey: 'nav.products',     to: '/products' },
  { type: 'link',                        labelKey: 'nav.projects',     to: '/projects' },
  { type: 'link',                        labelKey: 'nav.news',         to: '/news' },
  { type: 'link',                        labelKey: 'nav.contact',      to: '/contact' },
]

const handleScroll = () => { isScrolled.value = window.scrollY > 40 }

onMounted(async () => {
  window.addEventListener('scroll', handleScroll, { passive: true })
  handleScroll()
  load()
  try { const res = await api.categories(); categories.value = res.data } catch {}
})
onUnmounted(() => window.removeEventListener('scroll', handleScroll))
watch(() => route.path, () => { closeDropdown(); isMobileOpen.value = false })

const isActive = (path: string) => {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}
const closeDropdown       = () => { openDropdown.value = null }
const toggleMobileSection = (name: string) => {
  mobileSection.value = mobileSection.value === name ? null : name
}
</script>

<template>
  <header class="fixed w-full z-50 transition-all duration-300">
    <!-- Top info bar (Light gray bg like template) -->
    <div
      class="hidden lg:block w-full bg-[#F7F3F0] text-[#6B5D55] overflow-hidden transition-all duration-300 border-b border-[#E4D8D0]"
      :class="isScrolled ? 'h-0 opacity-0' : 'h-[44px] opacity-100'"
    >
      <div class="max-w-[1600px] mx-auto px-[var(--spacing-margin-desktop)] h-full flex items-center justify-between text-[13px] font-medium">
        <div class="flex items-center gap-6">
          <a href="https://www.google.com/maps/place/C%C3%94NG+TY+TNHH+DV+V%C3%80+TM+CH%C6%A0N+TH%C3%80NH/@10.8100547,106.6079875,140m/data=!3m1!1e3!4m6!3m5!1s0x31752bb2c658a587:0x3362c58ad0f4ce7c!8m2!3d10.8102715!4d106.6081321!16s%2Fg%2F11h6mklcm8?entry=ttu&g_ep=EgoyMDI2MDgyNS4wIKXMDSoASAFQAw%3D%3D" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 hover:text-[#B89B88] transition-colors duration-200 cursor-pointer">
            <span class="material-symbols-outlined text-[16px] text-[#B89B88]">location_on</span>
            {{ settings?.['contact.address'] || 'Find A Location' }}
          </a>
          <span class="w-px h-4 bg-[#E4D8D0]"></span>
          <a :href="`mailto:${email}`" class="flex items-center gap-2 hover:text-[#B89B88] transition-colors duration-200 cursor-pointer">
            <span class="material-symbols-outlined text-[16px] text-[#B89B88]">mail</span>
            {{ email }}
          </a>
        </div>

        <div class="flex items-center gap-5">
          <!-- Socials -->
          <div class="flex items-center gap-3 text-[#B89B88]">
            <a href="#" class="hover:text-[#4A403B] transition-colors" title="WeChat">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-6.656-6.088V8.89c-.135-.01-.27-.027-.407-.03zm-2.53 3.274c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.969-.982z"/></svg>
            </a>
            <a href="#" class="hover:text-[#4A403B] transition-colors" title="WhatsApp">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            </a>
            <a href="#" class="hover:text-[#4A403B] transition-colors" title="Zalo">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.49 10.2722v-.4496h1.3467v6.3218h-.7704a.576.576 0 01-.5763-.5729l-.0006.0005a3.273 3.273 0 01-1.9372.6321c-1.8138 0-3.2844-1.4697-3.2844-3.2823 0-1.8125 1.4706-3.2822 3.2844-3.2822a3.273 3.273 0 011.9372.6321l.0006.0005zM6.9188 7.7896v.205c0 .3823-.051.6944-.2995 1.0605l-.03.0343c-.0542.0615-.1815.206-.2421.2843L2.024 14.8h4.8948v.7682a.5764.5764 0 01-.5767.5761H0v-.3622c0-.4436.1102-.6414.2495-.8476L4.8582 9.23H.1922V7.7896h6.7266zm8.5513 8.3548a.4805.4805 0 01-.4803-.4798v-7.875h1.4416v8.3548H15.47zM20.6934 9.6C22.52 9.6 24 11.0807 24 12.9044c0 1.8252-1.4801 3.306-3.3066 3.306-1.8264 0-3.3066-1.4808-3.3066-3.306 0-1.8237 1.4802-3.3044 3.3066-3.3044zm-10.1412 5.253c1.0675 0 1.9324-.8645 1.9324-1.9312 0-1.065-.865-1.9295-1.9324-1.9295s-1.9324.8644-1.9324 1.9295c0 1.0667.865 1.9312 1.9324 1.9312zm10.1412-.0033c1.0737 0 1.945-.8707 1.945-1.9453 0-1.073-.8713-1.9436-1.945-1.9436-1.0753 0-1.945.8706-1.945 1.9436 0 1.0746.8697 1.9453 1.945 1.9453z"/></svg>
            </a>
          </div>

          <span class="w-px h-4 bg-[#E4D8D0]"></span>

          <!-- Language Dropdown -->
          <div class="flex items-center gap-1 cursor-pointer hover:text-[#B89B88]">
            <span class="material-symbols-outlined text-[16px] text-[#B89B88]">language</span>
            <span>English</span>
            <span class="material-symbols-outlined text-[16px]">expand_more</span>
          </div>

        </div>
      </div>
    </div>

    <!-- Main Navigation (Background #B89B88 as explicitly requested) -->
    <div
      class="w-full bg-[#B89B88] transition-all duration-300"
      :class="isScrolled ? 'shadow-md py-2' : 'py-4 lg:py-6'"
    >
      <div class="max-w-[1600px] mx-auto px-[var(--spacing-margin-mobile)] md:px-[var(--spacing-margin-desktop)] flex justify-between items-center h-full">

        <!-- Logo -->
        <div class="flex shrink-0 justify-start items-center">
          <router-link to="/" class="flex items-center group" aria-label="CHƠN THÀNH Geosynthetics">
            <img src="/images/logo.svg" alt="Logo" class="h-16 lg:h-20 w-auto object-contain drop-shadow-md -ml-2 xl:-ml-4">
          </router-link>
        </div>

        <!-- Center Navigation Container (The "khung" made White to stand out on #B89B88, and LARGE) -->
        <div class="hidden lg:flex flex-1 mx-6 xl:mx-12 bg-white rounded-[50px] items-center justify-center shadow-xl py-1 transition-all duration-300">

          <!-- Links -->
          <div class="flex items-center px-6">
            <template v-for="item in navItems" :key="item.type === 'link' ? item.to : item.name">

              <!-- Simple link -->
              <router-link
                v-if="item.type === 'link'"
                :to="item.to"
                class="px-8 xl:px-10 py-[24px] text-[19px] xl:text-[20px] font-bold transition-colors duration-200 whitespace-nowrap tracking-wide"
                :class="isActive(item.to) ? 'text-[#B89B88]' : 'text-[#16243D] hover:text-[#B89B88]'"
              >
                {{ t(item.labelKey) }}
              </router-link>

              <!-- Dropdown -->
              <div
                v-else
                class="relative group/dd flex"
                @mouseenter="openDropdown = item.name"
                @mouseleave="closeDropdown()"
              >
                <router-link
                  :to="item.to"
                  class="flex items-center gap-1 px-8 xl:px-10 py-[24px] text-[19px] xl:text-[20px] font-bold transition-colors duration-200 whitespace-nowrap tracking-wide"
                  :class="isActive(item.to) || openDropdown === item.name ? 'text-[#B89B88]' : 'text-[#16243D] group-hover/dd:text-[#B89B88]'"
                  @click="closeDropdown()"
                >
                  {{ t(item.labelKey) }}
                  <span class="material-symbols-outlined text-[22px] transition-transform duration-200 group-hover/dd:rotate-180">expand_more</span>
                </router-link>

                <!-- Dropdown Menu -->
                <transition name="dropdown">
                  <div v-show="openDropdown === item.name" class="absolute left-0 top-full z-50 pt-2">
                    <div class="bg-white border border-[#E4D8D0] rounded-[10px] shadow-xl p-3 min-w-[240px]">
                      <template v-if="item.name === 'about'">
                        <router-link
                          v-for="link in aboutSubLinks" :key="link.to" :to="link.to"
                          class="block px-4 py-3 rounded-lg text-[16px] font-semibold text-[#4A403B] hover:text-white hover:bg-[#B89B88] transition-colors"
                          @click="closeDropdown()"
                        >{{ t(link.labelKey) }}</router-link>
                      </template>
                      <template v-else>
                        <router-link
                          to="/products"
                          class="block px-4 py-3 rounded-lg text-[16px] font-semibold text-[#4A403B] hover:text-white hover:bg-[#B89B88] transition-colors mb-1"
                          @click="closeDropdown()"
                        >{{ t('nav.allProducts') }}</router-link>
                        <div class="h-px bg-[#F7F3F0] my-2 mx-2"></div>
                        <router-link
                          v-for="cat in categories" :key="cat.slug"
                          :to="{ path: '/products', query: { category: cat.slug } }"
                          class="block px-4 py-3 rounded-lg text-[15px] font-medium text-[#6B5D55] hover:text-white hover:bg-[#B89B88] transition-colors"
                          @click="closeDropdown()"
                        >{{ cat.name }}</router-link>
                      </template>
                    </div>
                  </div>
                </transition>
              </div>
            </template>
          </div>
        </div>

        <!-- Right Action / Hotline -->
        <div class="flex shrink-0 justify-end items-center">
          <!-- Right Phone (Desktop only) -->
          <div class="hidden xl:flex items-center shrink-0 bg-white rounded-[50px] p-2 pe-6 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 group hotline-cluster">
            <a :href="hotlineHref" class="relative flex items-center justify-center shrink-0">
              <!-- Pulsing background ring -->
              <div class="absolute inset-0 rounded-full bg-[#D84315] opacity-30 animate-ping group-hover:bg-[#D84315]/50"></div>
              <!-- Icon circle -->
              <div class="w-[64px] h-[64px] rounded-full bg-[#D84315] text-white flex items-center justify-center relative z-10 shadow-lg group-hover:scale-110 transition-all duration-300">
                <span class="material-symbols-outlined text-[30px] animate-ring">call</span>
              </div>
              <!-- Small dot -->
              <div class="absolute -top-1 -right-1 w-6 h-6 bg-[#B89B88] rounded-full flex items-center justify-center z-20 shadow-sm border-2 border-white group-hover:bg-[#16243D] transition-colors">
                <span class="material-symbols-outlined text-[14px] text-white">chat</span>
              </div>
            </a>
            <div class="flex items-center ms-5 group/hotline">
              <a :href="hotlineHref" class="text-[28px] font-black text-[#D84315] group-hover:text-[#B89B88] transition-colors leading-none tracking-tight animate-hotline-nudge tabular-nums">{{ hotline }}</a>
            </div>
          </div>

          <!-- Hamburger (Mobile) -->
          <button
            class="lg:hidden w-12 h-12 flex flex-col items-center justify-center gap-[6px] rounded-lg bg-white/10 text-white border border-white/30 shadow-sm"
            @click="isMobileOpen = !isMobileOpen"
          >
            <span class="w-6 h-[2px] rounded-full bg-current transition-all duration-300" :style="isMobileOpen ? 'transform: translateY(8px) rotate(45deg)' : ''"></span>
            <span class="w-6 h-[2px] rounded-full bg-current transition-all duration-300" :style="isMobileOpen ? 'opacity:0' : ''"></span>
            <span class="w-6 h-[2px] rounded-full bg-current transition-all duration-300" :style="isMobileOpen ? 'transform: translateY(-8px) rotate(-45deg)' : ''"></span>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition name="page">
      <div v-if="isMobileOpen" class="lg:hidden absolute top-full left-0 w-full bg-white border-t border-[#E4D8D0] shadow-lg max-h-[calc(100dvh-80px)] overflow-y-auto">
        <div class="p-4 flex flex-col gap-1">
          <template v-for="item in navItems" :key="item.type === 'link' ? item.to : item.name">
            <router-link
              v-if="item.type === 'link'"
              :to="item.to"
              class="px-4 py-3 rounded-xl text-[15px] font-bold text-[#4A403B] hover:bg-[#F7F3F0] hover:text-[#B89B88]"
              @click="isMobileOpen = false"
            >{{ t(item.labelKey) }}</router-link>

            <div v-else>
              <button
                class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-[15px] font-bold text-[#4A403B] hover:bg-[#F7F3F0] hover:text-[#B89B88]"
                @click="toggleMobileSection(item.name)"
              >
                <span>{{ t(item.labelKey) }}</span>
                <span class="material-symbols-outlined transition-transform" :class="mobileSection === item.name ? 'rotate-180' : ''">expand_more</span>
              </button>
              <div v-if="mobileSection === item.name" class="px-4 py-2 pl-8 flex flex-col gap-2">
                <template v-if="item.name === 'about'">
                  <router-link
                    v-for="link in aboutSubLinks" :key="link.to" :to="link.to"
                    class="text-[14px] font-medium text-[#6B5D55] hover:text-[#B89B88]"
                    @click="isMobileOpen = false"
                  >{{ t(link.labelKey) }}</router-link>
                </template>
                <template v-else>
                  <router-link
                    v-for="cat in categories" :key="cat.slug"
                    :to="{ path: '/products', query: { category: cat.slug } }"
                    class="text-[14px] font-medium text-[#6B5D55] hover:text-[#B89B88]"
                    @click="isMobileOpen = false"
                  >{{ cat.name }}</router-link>
                </template>
              </div>
            </div>
          </template>

          <div class="border-t border-[#E4D8D0] mt-4 pt-4 flex flex-col gap-3">
            <router-link to="/contact" class="w-full text-center py-3 bg-[#B89B88] text-white rounded-xl font-bold">
              Get a Quote
            </router-link>
          </div>
        </div>
      </div>
    </transition>
  </header>
</template>

<style scoped>
.hotline-cluster {
  animation: hotline-glow 2.5s ease-in-out infinite;
}
@keyframes hotline-glow {
  0%, 100% {
    box-shadow: 0 4px 20px rgba(216,67,21,0.15);
  }
  50% {
    box-shadow: 0 4px 20px rgba(216,67,21,0.15), 0 0 24px 6px rgba(216,67,21,0.18);
  }
}
.hotline-cluster:hover {
  animation-play-state: paused;
}
</style>
