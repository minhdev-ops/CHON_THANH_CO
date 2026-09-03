<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    eyebrow?: string
    kicker?: string
    title?: string
    subtitle?: string
    align?: 'left' | 'center' | 'right'
    compact?: boolean
    inverted?: boolean
  }>(),
  { align: 'left', compact: false, inverted: false },
)

const displayEyebrow = computed(() => props.eyebrow || props.kicker)
</script>

<template>
  <div
    class="w-full flex flex-col"
    :class="[
      align === 'center' ? 'items-center text-center' : align === 'right' ? 'items-end text-right' : 'items-start text-left',
      compact ? 'mb-6' : 'mb-12',
    ]"
  >
    <div
      v-if="displayEyebrow"
      class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-primary/10 border border-primary/20 mb-5"
    >
      <span class="text-primary text-[11px] font-extrabold tracking-[0.15em] uppercase">
        {{ displayEyebrow }}
      </span>
    </div>

    <h2
      v-if="title"
      class="font-extrabold leading-[1.15] tracking-[-0.02em] mb-0 max-w-3xl"
      :class="[
        compact ? 'text-[28px] md:text-[36px]' : 'text-[32px] md:text-[44px]',
        inverted ? 'text-canvas' : 'text-text-main',
      ]"
    >
      {{ title }}
    </h2>
    <div
      v-if="title"
      :class="[
        'w-12 h-1 bg-primary rounded-full mt-5',
        align === 'center' ? 'mx-auto' : align === 'right' ? 'ml-auto' : 'mr-auto',
      ]"
    ></div>

    <p
      v-if="subtitle"
      class="text-[15px] md:text-[16px] font-normal leading-relaxed mt-5 max-w-2xl"
      :class="inverted ? 'text-white/80' : 'text-text-secondary'"
    >
      {{ subtitle }}
    </p>
  </div>
</template>
