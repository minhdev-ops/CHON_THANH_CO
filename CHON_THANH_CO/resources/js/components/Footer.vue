<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useSettings } from '../composables/useSettings'
import { getYearsOfExperience } from '../utils/experience'

const { settings, load } = useSettings()

const phones = computed(() => {
  const raw = settings.value?.['contact.phone']?.trim() || '0909 292 530'
  return raw.split(/\s+[-/;,|]\s+|\s{2,}/).map((p) => p.trim()).filter(Boolean)
    .map((p) => ({ display: p, href: `tel:${p.replace(/[^\d+]/g, '')}` }))
})

const productLinks = [
  { label: 'Vải địa kỹ thuật',    slug: 'vai-dia-ky-thuat' },
  { label: 'Lưới địa kỹ thuật',   slug: 'luoi-dia-ky-thuat' },
  { label: 'Thảm chống xói mòn',  slug: 'tham-chong-xoi-mon' },
  { label: 'Rọ đá & Lưới thép',   slug: 'ro-da-luoi-thep' },
  { label: 'Màng chống thấm HDPE', slug: 'mang-chong-tham' },
]

const quickLinks = [
  { label: 'Giới thiệu',  to: '/about' },
  { label: 'Sản phẩm',    to: '/products' },
  { label: 'Dự án',       to: '/projects' },
  { label: 'Chứng nhận',  to: '/certificates' },
  { label: 'Tin tức',     to: '/news' },
  { label: 'Liên hệ',     to: '/contact' },
]

onMounted(() => load())
</script>

<template>
  <!-- Footer — primary #B89B88 -->
  <footer class="w-full mt-auto relative overflow-hidden bg-primary">
    <!-- Top accent line -->
    <div class="h-[3px] bg-white/30 w-full"></div>

    <!-- Decorative -->
    <div class="absolute top-0 right-0 w-80 h-80 rounded-full bg-white/5 -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 rounded-full bg-black/5 translate-y-1/2 -translate-x-1/4 pointer-events-none"></div>

    <!-- Main Content -->
    <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop pt-14 pb-10">

      <!-- Brand -->
      <div class="lg:col-span-4 lg:pr-8">
        <router-link to="/" class="inline-flex items-center gap-3 mb-5 group">
          <img src="/images/logo.svg" alt="CHƠN THÀNH Logo" class="h-14 w-auto object-contain drop-shadow-md">
          <div>
            <span class="block font-bold text-[24px] leading-tight text-white">CHƠN THÀNH</span>
            <span class="block text-[10px] font-bold tracking-[0.26em] uppercase text-white/70">GEOSYNTHETICS</span>
          </div>
        </router-link>

        <p class="text-white/80 text-[16px] leading-relaxed mb-6">
          {{ settings?.['company.description'] || `Nhà cung cấp vật liệu địa kỹ thuật hàng đầu Việt Nam với hơn ${getYearsOfExperience()} năm kinh nghiệm.` }}
        </p>

        <!-- Cert badges -->
        <div class="inline-flex items-center bg-white/10 border border-white/20 rounded-lg p-1.5 mb-7">
          <div class="flex items-center gap-2 px-3 py-1">
            <span class="material-symbols-outlined text-white text-[18px] fill">workspace_premium</span>
            <span class="text-[13px] font-bold text-white tracking-widest uppercase">ISO 9001:2015</span>
          </div>
          <div class="w-px h-6 bg-white/30 mx-1"></div>
          <div class="flex items-center gap-2 px-3 py-1">
            <span class="material-symbols-outlined text-white text-[18px] fill">workspace_premium</span>
            <span class="text-[13px] font-bold text-white tracking-widest uppercase">TCVN 9844</span>
          </div>
        </div>

        <!-- Social -->
        <div class="flex gap-3">
          <a
            v-if="settings?.['social.facebook']"
            :href="settings['social.facebook']"
            target="_blank" rel="noopener noreferrer"
            class="w-10 h-10 rounded-full bg-white/15 border border-white/25 flex items-center justify-center text-white hover:bg-white hover:text-primary transition-all duration-300 cursor-pointer shadow-sm hover:shadow-md hover:-translate-y-1"
            aria-label="Facebook"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
          </a>
          <a
            v-if="settings?.['social.zalo']"
            :href="settings['social.zalo']"
            target="_blank" rel="noopener noreferrer"
            class="w-10 h-10 rounded-full bg-white/15 border border-white/25 flex items-center justify-center text-white hover:bg-white hover:text-primary transition-all duration-300 cursor-pointer shadow-sm hover:shadow-md hover:-translate-y-1"
            aria-label="Zalo"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M21.1 10.999c0-5.523-4.992-10-11.1-10C3.892.999-.1 5.476-.1 10.999c0 3.12 1.624 5.922 4.148 7.82l-1.077 3.328a.56.56 0 00.707.69l3.856-1.57a11.512 11.512 0 002.466.264c6.108 0 11.1-4.477 11.1-10z"/></svg>
          </a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="lg:col-span-2">
        <h3 class="text-[14px] font-bold text-white/70 uppercase tracking-[0.22em] mb-4 pb-3 border-b border-white/20">
          Điều hướng
        </h3>
        <nav class="flex flex-col gap-2.5">
          <router-link
            v-for="link in quickLinks" :key="link.to" :to="link.to"
            class="flex items-center gap-2.5 text-white/80 hover:text-white text-[16px] font-medium transition-all duration-200 group cursor-pointer"
          >
            <span class="w-[4px] h-[4px] rounded-full bg-white/50 group-hover:bg-white transition-colors duration-200"></span>
            {{ link.label }}
          </router-link>
        </nav>
      </div>

      <!-- Product Links -->
      <div class="lg:col-span-3">
        <h3 class="text-[14px] font-bold text-white/70 uppercase tracking-[0.22em] mb-4 pb-3 border-b border-white/20">
          Sản phẩm
        </h3>
        <nav class="flex flex-col gap-2.5">
          <router-link
            v-for="p in productLinks" :key="p.slug"
            :to="`/products?category=${p.slug}`"
            class="flex items-center gap-2.5 text-white/80 hover:text-white text-[16px] font-medium transition-all duration-200 group cursor-pointer"
          >
            <span class="w-[3px] h-[3px] rounded-full bg-white/50 group-hover:bg-white transition-colors duration-200"></span>
            {{ p.label }}
          </router-link>
        </nav>
      </div>

      <!-- Contact Info -->
      <div class="lg:col-span-3">
        <h3 class="text-[14px] font-bold text-white/70 uppercase tracking-[0.22em] mb-4 pb-3 border-b border-white/20">
          Liên hệ
        </h3>
        <div class="flex flex-col gap-3.5">
          <a href="https://www.google.com/maps/place/C%C3%94NG+TY+TNHH+DV+V%C3%80+TM+CH%C6%A0N+TH%C3%80NH/@10.8100547,106.6079875,140m/data=!3m1!1e3!4m6!3m5!1s0x31752bb2c658a587:0x3362c58ad0f4ce7c!8m2!3d10.8102715!4d106.6081321!16s%2Fg%2F11h6mklcm8?entry=ttu&g_ep=EgoyMDI2MDgyNS4wIKXMDSoASAFQAw%3D%3D" target="_blank" rel="noopener noreferrer" class="flex items-start gap-3 hover:text-white transition-colors">
            <span class="material-symbols-outlined text-white text-[22px] mt-0.5 shrink-0">location_on</span>
            <span class="text-white/80 text-[16px] leading-relaxed">
              {{ settings?.['contact.address'] || '416A Đường CC2, Phường Tây Thạnh, Thành Phố Hồ Chí Minh' }}
            </span>
          </a>
          <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-white text-[22px] shrink-0">call</span>
            <div class="flex flex-col gap-1">
              <a
                v-for="phone in phones" :key="phone.href"
                :href="phone.href"
                class="text-white/80 hover:text-white text-[16px] transition-colors duration-200 tabular-nums cursor-pointer"
              >{{ phone.display }}</a>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-white text-[22px] shrink-0">mail</span>
            <span class="text-white/80 text-[16px]">
              {{ settings?.['contact.email'] || 'chonthanhtco@gmail.com' }}
            </span>
          </div>
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-white text-[22px] shrink-0">schedule</span>
            <span class="text-white/80 text-[16px]">
              {{ settings?.['contact.working_hours'] || 'T2 – T6: 8:30 AM – 5:30 PM' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom bar -->
    <div class="relative z-10 border-t border-white/20">
      <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-4 flex flex-col md:flex-row items-center justify-between gap-3">
        <p class="text-white/70 text-[15px]">
          &copy; {{ new Date().getFullYear() }} CHƠN THÀNH Geosynthetics. All rights reserved.
        </p>
        <div class="flex items-center gap-4 text-[15px] text-white/70">
          <span class="tracking-wider">ĐKKD 0303792837</span>
          <span class="w-px h-3 bg-white/30"></span>
          <span class="tracking-wider">ISO 9001:2015</span>
          <span class="w-px h-3 bg-white/30"></span>
          <span class="tracking-wider">TCVN 9844:2013</span>
        </div>
      </div>
    </div>
  </footer>
</template>
