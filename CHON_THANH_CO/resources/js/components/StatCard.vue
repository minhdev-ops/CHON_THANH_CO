<script setup lang="ts">
defineProps<{
  icon: string
  value: string
  label: string
}>()
</script>

<template>
  <div class="bg-surface-bright border border-outline-variant rounded-2xl p-7 hover:shadow-[0_8px_30px_rgba(51,45,41,0.06)] hover:border-primary/30 hover:-translate-y-1 transition-all duration-500 group card-premium">
    <div class="flex items-start gap-4">
      <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0 group-hover:bg-primary group-hover:text-white transition-all duration-400">
        <span class="material-symbols-outlined text-xl text-primary-deep group-hover:text-white">{{ icon }}</span>
      </div>
      <div>
        <div class="text-2xl font-bold text-text-main mb-1">{{ value }}</div>
        <div class="text-[11px] font-bold text-text-muted uppercase tracking-[0.15em]">{{ label }}</div>
      </div>
    </div>
  </div>
</template>
