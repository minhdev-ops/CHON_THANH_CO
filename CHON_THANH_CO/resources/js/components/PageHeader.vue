<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import ParticleBackground from './ParticleBackground.vue'
import WireframeTerrain from './WireframeTerrain.vue'

const props = withDefaults(
  defineProps<{
    title: string
    breadcrumbs: { label: string; to?: string }[]
    variant?: 'default' | 'compact' | 'cinematic'
  }>(),
  { variant: 'compact' },
)

const scrollY = ref(0)
const onScroll = () => { scrollY.value = window.scrollY }

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))

const paddingClass = computed(() => {
  if (props.variant === 'cinematic') return 'pt-[var(--page-header-top,320px)] pb-[var(--page-header-bottom,120px)]'
  return 'pt-[var(--page-header-top-compact,280px)] pb-[var(--page-header-bottom-compact,80px)]'
})
</script>

<template>
  <section
    class="relative overflow-hidden hero-parallax"
    :class="[paddingClass, variant === 'cinematic' ? 'noise-overlay' : '']"
  >
    <WireframeTerrain />

    <div
      class="absolute inset-0 pointer-events-none"
      :style="{
        background: variant === 'cinematic'
          ? 'linear-gradient(180deg, rgba(42,36,32,0.4) 0%, rgba(42,36,32,0.1) 40%, rgba(42,36,32,0.4) 100%)'
          : 'linear-gradient(180deg, rgba(42,36,32,0.4) 0%, rgba(42,36,32,0.1) 50%, rgba(42,36,32,0.4) 100%)',
        transform: variant === 'cinematic' ? `translateY(${scrollY * 0.15 + 20}px) scale(1.1)` : 'none',
      }"
    />

    <ParticleBackground v-if="variant === 'cinematic'" />

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
        v-if="breadcrumbs.length"
        class="inline-flex items-center gap-3 mb-5"
      >
        <span class="w-6 h-px bg-primary" />
        <span class="text-primary text-[11px] font-bold tracking-[0.22em] uppercase">
          {{ breadcrumbs[breadcrumbs.length - 1]?.label }}
        </span>
        <span class="w-6 h-px bg-primary" />
      </div>

      <h1
        class="text-white font-bold mb-6 leading-[1.1]"
        :class="variant === 'cinematic'
          ? 'text-[42px] md:text-[56px] lg:text-[68px]'
          : 'text-[32px] md:text-[44px] lg:text-[52px]'"
        style="letter-spacing: -0.02em; text-shadow: 0 4px 30px rgba(0,0,0,0.25);"
      >
        {{ title }}
      </h1>

      <div
        v-if="breadcrumbs.length > 1"
        class="flex items-center justify-center gap-2 text-white/70 text-[13px]"
      >
        <template v-for="(item, index) in breadcrumbs" :key="index">
          <router-link
            v-if="item.to"
            :to="item.to"
            class="hover:text-primary transition-colors duration-300"
          >
            {{ item.label }}
          </router-link>
          <span v-else class="text-primary font-semibold">{{ item.label }}</span>
          <span v-if="index < breadcrumbs.length - 1" class="text-white/30 text-xs">/</span>
        </template>
      </div>
    </div>
  </section>
</template>
