<script setup lang="ts">
import { computed } from 'vue'
import { useSettings } from '../composables/useSettings'

defineProps<{
  title?: string
  text?: string
  linkTo?: string
  linkLabel?: string
  secondaryLabel?: string
  secondaryTo?: string
}>()

const { settings, load } = useSettings()
load()

const hotline = computed(() => settings.value?.phone || settings.value?.['contact.phone'] || '0909 292 530')
const hotlineHref = computed(() => `tel:${hotline.value.replace(/[^\d+]/g, '')}`)
</script>

<template>
  <section class="relative py-16 md:py-24 overflow-hidden bg-transparent">
    <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop">
      <!-- Floating CTA Card -->
      <div class="relative bg-[#B89B88] text-white rounded-[40px] p-10 md:p-16 overflow-hidden shadow-[0_20px_60px_rgba(184,155,136,0.25)] reveal">
        
        <!-- Decorative Background inside the card -->
        <div class="absolute inset-0 opacity-15 dot-pattern pointer-events-none"></div>
        <div class="absolute -top-[50%] -left-[10%] w-[60%] h-[150%] bg-gradient-to-br from-white/30 to-transparent rotate-12 -z-10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-[50%] -right-[10%] w-[50%] h-[150%] bg-gradient-to-tl from-[#16243D]/10 to-transparent -rotate-12 -z-10 blur-3xl pointer-events-none"></div>
        
        <!-- Geometric Accent Shapes -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-white/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>

        <div class="relative z-10 text-center">
          <div class="max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-3 text-[12px] font-bold tracking-[0.18em] uppercase text-white/90 justify-center mb-6">
              <div class="w-7 h-[2px] bg-white/90 rounded-sm"></div>
              Bước tiếp theo
            </div>
            
            <h2 class="text-[32px] md:text-[46px] font-extrabold leading-[1.15] tracking-[-0.02em] mb-6 drop-shadow-sm text-white">
              {{ title || 'CẦN TƯ VẤN? LIÊN HỆ NGAY' }}
            </h2>
            
            <p class="text-white/90 text-[18px] leading-relaxed mb-10 max-w-2xl mx-auto font-medium">
              {{ text || 'Đội ngũ kỹ sư của chúng tôi sẵn sàng hỗ trợ giải pháp tối ưu cho dự án của bạn.' }}
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
              <router-link
                :to="linkTo || '/contact'"
                class="group inline-flex items-center gap-2 bg-white text-[#B89B88] font-bold text-[14px] uppercase tracking-[0.15em] py-4 px-10 rounded-[50px] shadow-lg shadow-black/10 hover:bg-[#16243D] hover:text-white hover:shadow-xl hover:shadow-[#16243D]/20 transition-all duration-300 hover:-translate-y-1"
              >
                {{ linkLabel || 'GỬI YÊU CẦU' }}
                <span class="material-symbols-outlined text-[20px] transition-transform group-hover:translate-x-1">arrow_forward</span>
              </router-link>
              
              <div class="hidden sm:block w-px h-12 bg-white/30"></div>
              
              <!-- Animated Phone Number -->
              <!-- Animated Phone Number -->
              <div class="flex items-center shrink-0 bg-white rounded-[50px] p-2 pe-6 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 group hotline-cluster">
                <a :href="hotlineHref" class="relative flex items-center justify-center shrink-0">
                  <div class="absolute inset-0 rounded-full bg-[#D84315] opacity-30 animate-ping group-hover:bg-[#D84315]/50"></div>
                  <div class="w-[64px] h-[64px] rounded-full bg-[#D84315] text-white flex items-center justify-center relative z-10 shadow-lg group-hover:scale-110 transition-all duration-300">
                    <span class="material-symbols-outlined text-[30px] animate-ring">call</span>
                  </div>
                  <div class="absolute -top-1 -right-1 w-6 h-6 bg-[#B89B88] rounded-full flex items-center justify-center z-20 shadow-sm border-2 border-white group-hover:bg-[#16243D] transition-colors">
                    <span class="material-symbols-outlined text-[14px] text-white">chat</span>
                  </div>
                </a>
                <div class="flex items-center ms-5 group/hotline">
                  <a :href="hotlineHref" class="text-[28px] font-black text-[#D84315] group-hover:text-[#B89B88] transition-colors leading-none tracking-tight animate-hotline-nudge tabular-nums">{{ hotline }}</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
