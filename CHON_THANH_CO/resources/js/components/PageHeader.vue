<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'

const props = withDefaults(
  defineProps<{
    title: string
    breadcrumbs: { label: string; to?: string }[]
    variant?: 'default' | 'compact' | 'cinematic'
    eyebrow?: string
  }>(),
  { variant: 'compact' },
)

const scrollY = ref(0)
const onScroll = () => { scrollY.value = window.scrollY }

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))

// Mobile: app-style compact header (chỉ <768px). Desktop giữ nguyên chiều cao cũ.
const paddingClass = computed(() => {
  if (props.variant === 'cinematic') return 'pt-[108px] pb-9 md:pt-[var(--page-header-top,320px)] md:pb-[var(--page-header-bottom,120px)]'
  return 'pt-[76px] pb-6 md:pt-[var(--page-header-top-compact,280px)] md:pb-[var(--page-header-bottom-compact,80px)]'
})
</script>

<template>
  <section
    class="relative overflow-hidden hero-parallax"
    :class="[paddingClass, variant === 'cinematic' ? 'noise-overlay' : '']"
  >
    <div
      class="absolute inset-0 pointer-events-none"
      :style="{
        background: variant === 'cinematic'
          ? 'linear-gradient(180deg, rgba(42,36,32,0.82) 0%, rgba(42,36,32,0.55) 40%, rgba(42,36,32,0.82) 100%)'
          : 'linear-gradient(180deg, rgba(42,36,32,0.92) 0%, rgba(42,36,32,0.72) 50%, rgba(42,36,32,0.92) 100%)',
        transform: variant === 'cinematic' ? `translateY(${scrollY * 0.15 + 20}px) scale(1.1)` : 'none',
      }"
    />

    <div
      v-if="variant === 'cinematic'"
      class="absolute inset-0 pointer-events-none"
      style="background:
        radial-gradient(ellipse at 15% 40%, rgba(184,155,136,0.25) 0%, transparent 50%),
        radial-gradient(ellipse at 85% 25%, rgba(139,107,88,0.2) 0%, transparent 45%),
        radial-gradient(ellipse at 50% 85%, rgba(184,155,136,0.12) 0%, transparent 50%);
      "
    />

    <div
      class="relative z-10 max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop text-center flex flex-col items-center reveal"
    >
      <div
        v-if="eyebrow || breadcrumbs.length"
        class="inline-flex items-center justify-center mb-2 md:mb-5 max-w-2xl px-4 text-center"
      >
        <span class="text-primary text-[11px] md:text-[12px] font-bold tracking-[0.18em] md:tracking-[0.22em] uppercase line-clamp-1">
          {{ eyebrow || breadcrumbs[breadcrumbs.length - 1]?.label }}
        </span>
      </div>

      <h1
        class="text-white font-bold mb-3 md:mb-6 leading-[1.15] md:leading-[1.1] max-w-[22ch] md:max-w-none line-clamp-2 md:line-clamp-none"
        :class="variant === 'cinematic'
          ? 'text-[26px] sm:text-[34px] md:text-[56px] lg:text-[68px]'
          : 'text-[24px] sm:text-[30px] md:text-[44px] lg:text-[52px]'"
        style="letter-spacing: -0.02em; text-shadow: 0 2px 12px rgba(0,0,0,0.5), 0 4px 30px rgba(0,0,0,0.3);"
      >
        {{ title }}
      </h1>

      <div
        v-if="breadcrumbs.length > 1"
        class="flex flex-wrap items-center justify-center gap-x-1.5 md:gap-x-2 gap-y-1 text-white/70 text-[12px] md:text-[13px] max-w-full"
      >
        <template v-for="(item, index) in breadcrumbs" :key="index">
          <router-link
            v-if="item.to"
            :to="item.to"
            class="hover:text-primary transition-colors duration-300 whitespace-nowrap max-md:py-2"
            :class="index < breadcrumbs.length - 2 ? 'hidden md:inline' : ''"
          >
            {{ item.label }}
          </router-link>
          <span
            v-else
            class="text-primary font-semibold max-w-[42vw] md:max-w-full truncate md:whitespace-normal"
            :class="index < breadcrumbs.length - 2 ? 'hidden md:inline' : ''"
          >{{ item.label }}</span>
          <span
            v-if="index < breadcrumbs.length - 1"
            class="text-white/30 text-xs"
            :class="index < breadcrumbs.length - 2 ? 'hidden md:inline' : ''"
          >/</span>
        </template>
      </div>
    </div>
  </section>
</template>
