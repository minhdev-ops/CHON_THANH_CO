<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useSettings } from '../composables/useSettings'
import { t } from '../i18n'
import ggMapImg from '../../image/gg_map.png'
import zaloImg from '../../image/zalo.png'

const { settings, load } = useSettings()

onMounted(() => load())

const hotlineIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>`

const whatsappIcon = `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>`

const wechatIcon = `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-6.656-6.088V8.89c-.135-.01-.27-.027-.407-.03zm-2.53 3.274c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.969-.982z"/></svg>`

const contacts = computed(() => [
  {
    name: t('widget.hotline'),
    url: 'tel:0909292530',
    external: false,
    image: undefined,
    icon: hotlineIcon,
    variant: 'hotline',
  },
  {
    name: 'Zalo',
    url: settings.value?.['social.zalo']?.trim() || 'https://zalo.me/0909292530',
    external: true,
    image: zaloImg,
    variant: 'zalo',
  },
  {
    name: 'WhatsApp',
    url: settings.value?.['social.whatsapp']?.trim() || 'https://wa.me/84909292530',
    external: true,
    image: undefined,
    icon: whatsappIcon,
    variant: 'whatsapp',
  },
  {
    name: 'WeChat',
    url: settings.value?.['social.wechat']?.trim() || 'https://wechat.com',
    external: true,
    image: undefined,
    icon: wechatIcon,
    variant: 'wechat',
  },
  {
    name: t('widget.map'),
    url: settings.value?.['social.ggmap']?.trim() || 'https://maps.app.goo.gl/xstN8U1DZsLERZeGA',
    external: true,
    image: ggMapImg,
    variant: 'map',
  },
])

const isVisible = ref(true)

const variantStyles: Record<string, { bg: string; shadow: string; hoverShadow: string; pulseColor: string }> = {
  hotline: { bg: 'bg-[#D84315]', shadow: 'shadow-[0_4px_20px_rgba(216,67,21,0.5)]', hoverShadow: 'shadow-[0_8px_32px_rgba(216,67,21,0.6)]', pulseColor: 'rgba(216,67,21,0.6)' },
  zalo: { bg: 'bg-[#0068FF]', shadow: 'shadow-[0_4px_20px_rgba(0,104,255,0.4)]', hoverShadow: 'shadow-[0_8px_32px_rgba(0,104,255,0.5)]', pulseColor: 'rgba(0,104,255,0.5)' },
  whatsapp: { bg: 'bg-[#25D366]', shadow: 'shadow-[0_4px_20px_rgba(37,211,102,0.4)]', hoverShadow: 'shadow-[0_8px_32px_rgba(37,211,102,0.5)]', pulseColor: 'rgba(37,211,102,0.5)' },
  wechat: { bg: 'bg-[#07C160]', shadow: 'shadow-[0_4px_20px_rgba(7,193,96,0.4)]', hoverShadow: 'shadow-[0_8px_32px_rgba(7,193,96,0.5)]', pulseColor: 'rgba(7,193,96,0.5)' },
  map: { bg: 'bg-white', shadow: 'shadow-[0_4px_20px_rgba(0,0,0,0.15)]', hoverShadow: 'shadow-[0_8px_32px_rgba(0,0,0,0.2)]', pulseColor: 'rgba(0,0,0,0.15)' },
}
</script>

<template>
  <!-- Mobile: fixed bottom bar with 5 buttons -->
  <div class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-lg border-t border-black/8 safe-area-bottom">
    <div class="flex items-stretch">
      <a
        v-for="(contact, i) in contacts"
        :key="contact.name"
        :href="contact.url"
        :target="contact.external ? '_blank' : undefined"
        :rel="contact.external ? 'noopener noreferrer' : undefined"
        class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2.5 transition-all duration-200 active:bg-black/5 min-w-0 relative overflow-visible"
        :class="[
          i < contacts.length - 1 ? 'border-r border-black/8' : '',
          contact.variant === 'hotline' ? 'text-[#D84315]' : '',
          contact.variant === 'zalo' ? 'text-[#0068FF]' : '',
          contact.variant === 'whatsapp' ? 'text-[#25D366]' : '',
          contact.variant === 'wechat' ? 'text-[#07C160]' : '',
        ]"
      >
        <div class="relative z-10 flex items-center justify-center w-10 h-10 rounded-full bg-black/5 group-hover:bg-black/10 transition-colors">
          <!-- Pulse ring for ALL buttons (mobile) -->
          <div class="absolute inset-0 rounded-full animate-pulse-ring" :style="{ borderColor: variantStyles[contact.variant].pulseColor }" aria-hidden="true"></div>

          <!-- Hotline exclusive: stronger alarm rings (mobile) -->
          <div v-if="contact.variant === 'hotline'" class="absolute inset-0 rounded-full animate-hotline-ring-extreme" aria-hidden="true"></div>
          <div v-if="contact.variant === 'hotline'" class="absolute inset-0 rounded-full animate-hotline-ring-strong" style="animation-delay: 0.4s;" aria-hidden="true"></div>

          <img
            v-if="contact.image"
            :src="contact.image"
            class="w-5 h-5 object-contain animate-icon-float"
            :alt="contact.name"
          />
          <span v-else class="animate-icon-float" v-html="contact.icon"></span>
        </div>
        <span class="text-[11px] font-bold tracking-wide truncate w-full text-center mt-1">{{ contact.name }}</span>
      </a>
    </div>
  </div>

  <!-- Desktop: floating vertical buttons -->
  <div v-show="isVisible" class="hidden md:flex fixed bottom-24 right-6 z-50 flex-col items-center gap-4" role="navigation" aria-label="Liên hệ nhanh">
    <a
      v-for="(contact, index) in contacts"
      :key="contact.name"
      :href="contact.url"
      :target="contact.external ? '_blank' : undefined"
      :rel="contact.external ? 'noopener noreferrer' : undefined"
      class="widget-btn relative group flex items-center justify-center w-[60px] h-[60px] rounded-full transition-all duration-500 hover:scale-110 active:scale-95"
      :class="[
        variantStyles[contact.variant].bg,
        'text-white',
        variantStyles[contact.variant].shadow,
        'hover:' + variantStyles[contact.variant].hoverShadow,
      ]"
      :style="{
        '--pulse-color': variantStyles[contact.variant].pulseColor,
        animationDelay: `${index * 150}ms`,
      }"
    >
      <!-- Pulse ring for ALL buttons (subtle attention) -->
      <div class="absolute inset-0 rounded-full animate-pulse-ring" :style="{ borderColor: variantStyles[contact.variant].pulseColor }" aria-hidden="true"></div>

      <!-- Hotline exclusive: stronger alarm rings -->
      <div v-if="contact.variant === 'hotline'" class="absolute inset-0 rounded-full animate-hotline-ring-extreme" aria-hidden="true"></div>
      <div v-if="contact.variant === 'hotline'" class="absolute inset-0 rounded-full animate-hotline-ring-strong" style="animation-delay: 0.4s;" aria-hidden="true"></div>

      <!-- Button content with icon animation -->
      <div class="relative z-10 flex items-center justify-center">
        <img
          v-if="contact.image"
          :src="contact.image"
          class="w-8 h-8 object-contain animate-icon-float"
          :alt="contact.name"
        />
        <span v-else class="animate-icon-float" v-html="contact.icon"></span>
      </div>

      <!-- Tooltip label -->
      <span class="widget-tooltip absolute right-[calc(100%+16px)] top-1/2 -translate-y-1/2 px-4 py-2 bg-[#1a1a1a] text-white text-xs font-semibold rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible pointer-events-none transition-all duration-300 whitespace-nowrap shadow-xl transform group-hover:translate-x-0">
        {{ contact.name }}
        <span class="absolute right-[-6px] top-1/2 -translate-y-1/2 w-0 h-0 border-3 border-transparent border-l-[#1a1a1a]"></span>
      </span>

    </a>
  </div>
</template>

<style scoped>
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom, 0px);
}

.widget-btn {
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  position: relative;
}

/* Staggered entrance animation */
.widget-btn {
  animation: widget-enter 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}

@keyframes widget-enter {
  0% {
    opacity: 0;
    transform: translateX(60px) scale(0.8);
  }
  100% {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
}

/* Pulse ring - continuous attention grabber */
@keyframes pulse-ring {
  0% {
    transform: scale(1);
    opacity: 0.6;
  }
  50% {
    transform: scale(1.35);
    opacity: 0.2;
  }
  100% {
    transform: scale(1.6);
    opacity: 0;
  }
}

.animate-pulse-ring {
  animation: pulse-ring 2.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
}

/* Tooltip styles */
.widget-tooltip {
  transform: translateX(8px);
}

.widget-btn:hover .widget-tooltip {
  transform: translateX(0);
  opacity: 1;
  visibility: visible;
}

/* Focus visible for accessibility */
.widget-btn:focus-visible {
  outline: 3px solid var(--pulse-color);
  outline-offset: 4px;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .widget-btn,
  .animate-pulse-ring,
  .animate-icon-float,
  .animate-hotline-ring-extreme,
  .animate-hotline-ring-strong {
    animation: none !important;
  }
  .widget-btn {
    opacity: 1;
    transform: none;
  }
}

/* Hotline priority - stronger pulse */
.widget-btn:first-child .animate-pulse-ring {
  animation-duration: 1.8s;
  animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Pulse ring - PROMINENT attention grabber for ALL buttons */
@keyframes pulse-ring {
  0% {
    transform: scale(1);
    opacity: 0.8;
  }
  30% {
    transform: scale(1.4);
    opacity: 0.4;
  }
  60% {
    transform: scale(1.6);
    opacity: 0.2;
  }
  100% {
    transform: scale(1.8);
    opacity: 0;
  }
}

.animate-pulse-ring {
  animation: pulse-ring 1.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

/* Icon float animation - NOTICEABLE vertical float for ALL icons */
@keyframes icon-float {
  0%, 100% { transform: translateY(0) scale(1); }
  40% { transform: translateY(-6px) scale(1.08); }
  50% { transform: translateY(-4px) scale(1.04); }
}

.animate-icon-float {
  animation: icon-float 1.2s ease-in-out infinite;
}

/* Hotline EXCLUSIVE: Extreme alarm ring - FAST & BRIGHT */
@keyframes hotline-ring-extreme {
  0% {
    box-shadow:
      0 0 0 0 rgba(216, 67, 21, 1),
      0 0 50px 10px rgba(216, 67, 21, 0.8),
      0 0 100px 20px rgba(216, 67, 21, 0.5);
  }
  25% {
    box-shadow:
      0 0 0 12px rgba(216, 67, 21, 0.8),
      0 0 70px 16px rgba(216, 67, 21, 0.6),
      0 0 140px 28px rgba(216, 67, 21, 0.4);
  }
  50% {
    box-shadow:
      0 0 0 24px rgba(216, 67, 21, 0.5),
      0 0 90px 22px rgba(216, 67, 21, 0.5),
      0 0 180px 36px rgba(216, 67, 21, 0.35);
  }
  75% {
    box-shadow:
      0 0 0 36px rgba(216, 67, 21, 0.25),
      0 0 110px 28px rgba(216, 67, 21, 0.3),
      0 0 220px 44px rgba(216, 67, 21, 0.2);
  }
  100% {
    box-shadow:
      0 0 0 0 rgba(216, 67, 21, 0),
      0 0 60px 12px rgba(216, 67, 21, 0.15),
      0 0 120px 24px rgba(216, 67, 21, 0.08);
  }
}

.animate-hotline-ring-extreme {
  animation: hotline-ring-extreme 0.8s ease-out infinite;
}

/* Hotline EXCLUSIVE: Strong alarm ring - FAST */
@keyframes hotline-ring-strong {
  0% {
    box-shadow:
      0 0 0 0 rgba(216, 67, 21, 1),
      0 0 40px 8px rgba(216, 67, 21, 0.6);
  }
  40% {
    box-shadow:
      0 0 0 16px rgba(216, 67, 21, 0.6),
      0 0 60px 14px rgba(216, 67, 21, 0.4);
  }
  70% {
    box-shadow:
      0 0 0 30px rgba(216, 67, 21, 0.3),
      0 0 80px 18px rgba(216, 67, 21, 0.25);
  }
  100% {
    box-shadow:
      0 0 0 0 rgba(216, 67, 21, 0),
      0 0 40px 8px rgba(216, 67, 21, 0.15);
  }
}

.animate-hotline-ring-strong {
  animation: hotline-ring-strong 0.6s ease-out infinite;
}
</style>
