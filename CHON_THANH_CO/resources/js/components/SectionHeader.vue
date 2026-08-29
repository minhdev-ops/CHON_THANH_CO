<script setup lang="ts">
withDefaults(
  defineProps<{
    eyebrow?: string
    title?: string
    subtitle?: string
    align?: 'left' | 'center' | 'right'
    compact?: boolean
    inverted?: boolean
  }>(),
  { align: 'left', compact: false, inverted: false },
)
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
      v-if="eyebrow"
      class="inline-flex items-center gap-3 mb-5"
    >
      <span class="w-6 h-px bg-primary" />
      <span class="text-primary text-[11px] font-bold tracking-[0.22em] uppercase">
        {{ eyebrow }}
      </span>
      <span v-if="align === 'center'" class="w-6 h-px bg-primary" />
    </div>

    <h2
      v-if="title"
      class="font-bold leading-[1.05] tracking-[-0.02em] mb-0 max-w-3xl"
      :class="[
        compact ? 'text-[28px] md:text-[36px]' : 'text-[32px] md:text-[48px]',
        inverted ? 'text-canvas' : 'text-text-primary',
      ]"
    >
      {{ title }}
    </h2>

    <p
      v-if="subtitle"
      class="text-[15px] md:text-[16px] font-normal leading-relaxed mt-5 max-w-2xl"
      :class="inverted ? 'text-white/80' : 'text-text-secondary'"
    >
      {{ subtitle }}
    </p>
  </div>
</template>
