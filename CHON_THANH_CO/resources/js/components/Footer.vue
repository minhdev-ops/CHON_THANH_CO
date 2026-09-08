<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useSettings } from '../composables/useSettings'
import { getYearsOfExperience } from '../utils/experience'

const { settings, load, socialSettings } = useSettings()

const phones = computed(() => {
  const raw = settings.value?.['contact.phone']?.trim() || '0909 292 530'
  return raw.split(/\s+[-/;,|]\s+|\s{2,}/).map((p) => p.trim()).filter(Boolean)
    .map((p) => ({ display: p, href: `tel:${p.replace(/[^\d+]/g, '')}` }))
})

const quickLinks = [
  { label: 'Giới thiệu',  to: '/about' },
  { label: 'Sản phẩm',    to: '/products' },
  { label: 'Dự án',       to: '/projects' },
  { label: 'Liên hệ',     to: '/contact' },
]

const productLinks = [
  { label: 'Vải địa kỹ thuật',   slug: 'vai-dia-ky-thuat' },
  { label: 'Lưới địa kỹ thuật',  slug: 'luoi-dia-ky-thuat' },
  { label: 'Thảm chống xói mòn', slug: 'tham-chong-xoi-mon' },
  { label: 'Màng chống thấm',    slug: 'mang-chong-tham' },
]

onMounted(() => load())
</script>

<template>
  <footer class="w-full mt-auto relative overflow-hidden bg-primary">
    <!-- Top accent -->
    <div class="h-[2px] bg-white/30 w-full"></div>

    <!-- ═══ Desktop ═══ -->
    <div class="hidden md:block relative z-10 grid grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-8 max-w-max-width mx-auto px-[var(--spacing-margin-desktop)] pt-14 pb-10">
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
        <div class="inline-flex flex-wrap items-center bg-white/10 border border-white/20 rounded-lg p-1.5 mb-7 gap-1">
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
        <div class="flex gap-3">
          <a v-for="social in socialSettings" :key="social.name" :href="social.url" target="_blank" rel="noopener noreferrer"
            class="w-10 h-10 rounded-full bg-white/15 border border-white/25 flex items-center justify-center text-white hover:bg-white hover:text-primary transition-all duration-300 cursor-pointer shadow-sm hover:shadow-md hover:-translate-y-1"
            :aria-label="social.name" :title="social.name">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" v-html="social.icon"></svg>
          </a>
        </div>
      </div>
      <!-- Quick Links -->
      <div class="lg:col-span-2">
        <h3 class="text-[14px] font-bold text-white/70 uppercase tracking-[0.22em] mb-4 pb-3 border-b border-white/20">Điều hướng</h3>
        <nav class="flex flex-col gap-2.5">
          <router-link v-for="link in quickLinks" :key="link.to" :to="link.to"
            class="flex items-center gap-2.5 text-white/80 hover:text-white text-[16px] font-medium transition-all duration-200 group cursor-pointer">
            <span class="w-[4px] h-[4px] rounded-full bg-white/50 group-hover:bg-white transition-colors duration-200"></span>
            {{ link.label }}
          </router-link>
        </nav>
      </div>
      <!-- Product Links -->
      <div class="lg:col-span-3">
        <h3 class="text-[14px] font-bold text-white/70 uppercase tracking-[0.22em] mb-4 pb-3 border-b border-white/20">Sản phẩm</h3>
        <nav class="flex flex-col gap-2.5">
          <router-link v-for="p in productLinks" :key="p.slug" :to="`/products?category=${p.slug}`"
            class="flex items-center gap-2.5 text-white/80 hover:text-white text-[16px] font-medium transition-all duration-200 group cursor-pointer">
            <span class="w-[3px] h-[3px] rounded-full bg-white/50 group-hover:bg-white transition-colors duration-200"></span>
            {{ p.label }}
          </router-link>
        </nav>
      </div>
      <!-- Contact Info -->
      <div class="lg:col-span-3">
        <h3 class="text-[14px] font-bold text-white/70 uppercase tracking-[0.22em] mb-4 pb-3 border-b border-white/20">Liên hệ</h3>
        <div class="flex flex-col gap-3.5">
          <a :href="settings?.['social.ggmap'] || 'https://www.google.com/maps'" target="_blank" rel="noopener noreferrer" class="flex items-start gap-3 hover:text-white transition-colors">
            <span class="material-symbols-outlined text-white text-[22px] mt-0.5 shrink-0">location_on</span>
            <span class="text-white/80 text-[16px] leading-relaxed">{{ settings?.['contact.address'] || '416A Đường CC2, P. Sơn Kỳ, Q. Tân Phú, TP.HCM' }}</span>
          </a>
          <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-white text-[22px] shrink-0">call</span>
            <div class="flex flex-col gap-1">
              <a v-for="phone in phones" :key="phone.href" :href="phone.href"
                class="text-white/80 hover:text-white text-[16px] transition-colors duration-200 tabular-nums cursor-pointer">{{ phone.display }}</a>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-white text-[22px] shrink-0">mail</span>
            <span class="text-white/80 text-[16px]">{{ settings?.['contact.email'] || 'chonthanhco@gmail.com' }}</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-white text-[22px] shrink-0">schedule</span>
            <span class="text-white/80 text-[16px]">{{ settings?.['contact.working_hours'] || 'T2 – T6: 8:30 AM – 5:30 PM' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ Mobile ═══ -->
    <div class="md:hidden relative z-10 pt-8 pb-6">

      <!-- 1. Logo + tagline -->
      <div class="flex flex-col items-center mb-7 px-6">
        <router-link to="/" class="flex items-center gap-3 mb-2">
          <img src="/images/logo.svg" alt="Logo" class="h-11 w-auto object-contain">
          <div>
            <span class="block font-extrabold text-[20px] leading-tight text-white tracking-tight">CHƠN THÀNH</span>
            <span class="block text-[9px] font-bold tracking-[0.3em] uppercase text-white/50">GEOSYNTHETICS</span>
          </div>
        </router-link>
        <p class="text-white/55 text-[14px] text-center mt-1">Vật liệu địa kỹ thuật hàng đầu Việt Nam</p>
      </div>

      <!-- 1. Logo + tagline -->
      <div class="flex justify-center gap-4 mb-7">
        <a v-for="social in socialSettings" :key="social.name" :href="social.url" target="_blank" rel="noopener noreferrer"
          class="w-11 h-11 rounded-full bg-white/10 flex items-center justify-center text-white/70 hover:bg-white hover:text-primary transition-all duration-300"
          :aria-label="social.name">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" v-html="social.icon"></svg>
        </a>
      </div>

      <!-- 4. Links section -->
      <div class="px-6 mb-6">
        <!-- Quick links -->
        <h4 class="text-[12px] font-bold text-white/40 uppercase tracking-[0.2em] mb-3">Liên kết nhanh</h4>
        <div class="space-y-0 mb-5">
          <router-link v-for="link in quickLinks" :key="link.to" :to="link.to"
            class="flex items-center justify-between py-3 border-b border-white/8 text-white/85 text-[16px] font-medium hover:text-white transition-colors">
            <span>{{ link.label }}</span>
            <span class="material-symbols-outlined text-white/30 text-[18px]">chevron_right</span>
          </router-link>
        </div>

        <!-- Product links -->
        <h4 class="text-[12px] font-bold text-white/40 uppercase tracking-[0.2em] mb-3">Sản phẩm</h4>
        <div class="space-y-0">
          <router-link v-for="p in productLinks" :key="p.slug" :to="`/products?category=${p.slug}`"
            class="flex items-center justify-between py-3 border-b border-white/8 text-white/85 text-[16px] font-medium hover:text-white transition-colors">
            <span>{{ p.label }}</span>
            <span class="material-symbols-outlined text-white/30 text-[18px]">chevron_right</span>
          </router-link>
        </div>
      </div>

      <!-- 5. Contact info -->
      <div class="px-6 mb-6 space-y-4">
        <h4 class="text-[12px] font-bold text-white/40 uppercase tracking-[0.2em]">Liên hệ</h4>
        <a :href="settings?.['social.ggmap'] || 'https://www.google.com/maps'" target="_blank" rel="noopener noreferrer"
          class="flex items-start gap-3 group">
          <span class="material-symbols-outlined text-white/50 text-[20px] mt-0.5 shrink-0 group-hover:text-white transition-colors">location_on</span>
          <span class="text-white/75 text-[15px] leading-snug group-hover:text-white transition-colors">{{ settings?.['contact.address'] || '416A Đường CC2, P. Sơn Kỳ, Q. Tân Phú, TP.HCM' }}</span>
        </a>
        <div class="flex items-start gap-3">
          <span class="material-symbols-outlined text-white/50 text-[20px] mt-0.5 shrink-0">call</span>
          <div class="flex flex-col gap-1.5">
            <a v-for="phone in phones" :key="phone.href" :href="phone.href"
              class="text-white/75 text-[15px] font-medium tabular-nums hover:text-white transition-colors">{{ phone.display }}</a>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-white/50 text-[20px] shrink-0">mail</span>
          <span class="text-white/75 text-[15px]">{{ settings?.['contact.email'] || 'chonthanhco@gmail.com' }}</span>
        </div>
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-white/50 text-[20px] shrink-0">schedule</span>
          <span class="text-white/75 text-[15px]">{{ settings?.['contact.working_hours'] || 'T2 – T6: 8:30 AM – 5:30 PM' }}</span>
        </div>
      </div>

      <!-- 6. Bottom -->
      <div class="px-6 pt-5 border-t border-white/15">
        <p class="text-white/40 text-[12px] text-center leading-relaxed">
          &copy; {{ new Date().getFullYear() }} CHƠN THÀNH Geosynthetics
        </p>
      </div>
    </div>

    <!-- Desktop bottom bar -->
    <div class="hidden md:block relative z-10 border-t border-white/15">
      <div class="max-w-max-width mx-auto px-[var(--spacing-margin-desktop)] py-4 flex items-center justify-between">
        <p class="text-white/60 text-[15px]">
          &copy; {{ new Date().getFullYear() }} CHƠN THÀNH Geosynthetics
        </p>
        <div class="flex items-center gap-4 text-[15px] text-white/50">
          <span>ĐKKD 0303792837</span>
          <span class="w-px h-2.5 bg-white/20"></span>
          <span>ISO 9001:2015</span>
          <span class="w-px h-2.5 bg-white/20"></span>
          <span>TCVN 9844:2013</span>
        </div>
      </div>
    </div>
  </footer>
</template>
