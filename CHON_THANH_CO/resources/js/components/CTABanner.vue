<script setup lang="ts">
defineProps<{
  title?: string
  text?: string
  linkTo?: string
  linkLabel?: string
  secondaryLabel?: string
  secondaryTo?: string
}>()
</script>

<template>
  <section class="relative bg-text-main text-canvas py-20 md:py-24 overflow-hidden">
    <div class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop text-center">
      <div class="max-w-2xl mx-auto">
        <div class="kicker kicker-primary justify-center mb-5">Bước tiếp theo</div>
        <h2 class="text-[28px] md:text-[40px] font-bold leading-[1.1] tracking-[-0.025em] mb-4">
          {{ title || 'Sẵn sàng tư vấn miễn phí' }}
        </h2>
        <p v-if="text" class="text-white/80 text-[16px] leading-relaxed mb-8">
          {{ text }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <router-link
            :to="linkTo || '/contact'"
            class="inline-flex items-center gap-2 bg-canvas text-text-main font-bold text-[12px] uppercase tracking-[0.15em] py-3.5 px-8 hover:bg-primary hover:text-canvas transition-colors duration-300"
          >
            {{ linkLabel || 'Liên hệ tư vấn' }}
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </router-link>
          <router-link
            v-if="secondaryTo"
            :to="secondaryTo"
            class="text-white/70 hover:text-canvas text-[12px] font-bold uppercase tracking-[0.15em] py-3.5 px-4 transition-colors duration-300"
          >
            {{ secondaryLabel || 'Xem thêm' }} →
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>
