<script setup lang="ts">
import { t } from '../i18n'

defineProps<{
  message: string
}>()

const emit = defineEmits<{ retry: [] }>()
</script>

<template>
  <div class="py-20 text-center">
    <div class="w-16 h-16 rounded-full bg-brand-orange/10 flex items-center justify-center mx-auto mb-4">
      <i class="fa text-3xl text-brand-orange/40 fa-circle-exclamation"></i>
    </div>
    <p class="text-on-surface-variant mb-6">{{ message }}</p>
    <button
      type="button"
      class="inline-flex items-center gap-2 bg-brand-orange hover:bg-primary-dark text-white font-semibold px-6 py-3 rounded-full transition-all duration-300 shadow-md hover:shadow-lg"
      @click="emit('retry')"
    >
      <i class="fa text-lg fa-refresh"></i> {{ t('common.retry') }}
    </button>
  </div>
</template>
