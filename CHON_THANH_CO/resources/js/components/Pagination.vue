<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    currentPage: number
    totalPages: number
    totalItems?: number
    pageSize?: number
    itemName?: string
  }>(),
  {
    totalItems: undefined,
    pageSize: undefined,
    itemName: 'kết quả',
  }
)

const emit = defineEmits<{
  (e: 'update:currentPage', page: number): void
  (e: 'change', page: number): void
}>()

const itemRange = computed(() => {
  if (props.totalItems === undefined || !props.pageSize) return null
  if (props.totalItems === 0) return { from: 0, to: 0, total: 0 }
  const from = (props.currentPage - 1) * props.pageSize + 1
  const to = Math.min(props.currentPage * props.pageSize, props.totalItems)
  return { from, to, total: props.totalItems }
})

const visiblePages = computed<(number | string)[]>(() => {
  const total = props.totalPages
  const current = props.currentPage

  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }

  if (current <= 4) {
    return [1, 2, 3, 4, 5, '...', total]
  }

  if (current >= total - 3) {
    return [1, '...', total - 4, total - 3, total - 2, total - 1, total]
  }

  return [1, '...', current - 1, current, current + 1, '...', total]
})

const goToPage = (page: number | string) => {
  if (typeof page !== 'number') return
  if (page < 1 || page > props.totalPages || page === props.currentPage) return
  emit('update:currentPage', page)
  emit('change', page)
}
</script>

<template>
  <nav
    v-if="totalPages > 1 || (totalItems !== undefined && totalItems > 0)"
    aria-label="Phân trang"
    class="flex flex-col sm:flex-row items-center justify-between gap-4 py-8 border-t border-outline-variant/60"
  >
    <!-- Result counter info -->
    <div v-if="itemRange" class="text-[13px] text-text-secondary font-medium order-2 sm:order-1">
      <span>Hiển thị </span>
      <span class="font-bold text-text-main tabular-nums">{{ itemRange.from }}–{{ itemRange.to }}</span>
      <span> trên </span>
      <span class="font-bold text-text-main tabular-nums">{{ itemRange.total }}</span>
      <span> {{ itemName }}</span>
    </div>
    <div v-else class="order-2 sm:order-1"></div>

    <!-- Page navigation controls -->
    <div v-if="totalPages > 1" class="flex items-center gap-1.5 sm:gap-2 order-1 sm:order-2">
      <!-- Previous button -->
      <button
        type="button"
        class="inline-flex items-center justify-center gap-1 min-w-[36px] h-9 px-2.5 rounded-md border border-outline-variant text-[13px] font-semibold transition-all duration-200"
        :class="
          currentPage <= 1
            ? 'opacity-40 cursor-not-allowed text-text-muted bg-transparent'
            : 'text-text-main hover:bg-surface-vlm hover:border-primary/40 active:scale-95 cursor-pointer shadow-2xs'
        "
        :disabled="currentPage <= 1"
        @click="goToPage(currentPage - 1)"
        aria-label="Trang trước"
      >
        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        <span class="hidden md:inline">Trước</span>
      </button>

      <!-- Page Numbers -->
      <template v-for="(p, idx) in visiblePages" :key="idx">
        <span
          v-if="p === '...'"
          class="min-w-[32px] h-9 flex items-center justify-center text-text-muted text-[13px] font-bold select-none"
        >
          •••
        </span>
        <button
          v-else
          type="button"
          class="min-w-[36px] h-9 px-3 rounded-md text-[13px] font-bold transition-all duration-200 cursor-pointer tabular-nums"
          :class="
            p === currentPage
              ? 'bg-primary text-white shadow-sm border border-primary ring-2 ring-primary/20 scale-105'
              : 'border border-outline-variant text-text-main hover:bg-surface-vlm hover:border-primary/40 hover:text-primary active:scale-95'
          "
          :aria-current="p === currentPage ? 'page' : undefined"
          @click="goToPage(p)"
        >
          {{ p }}
        </button>
      </template>

      <!-- Next button -->
      <button
        type="button"
        class="inline-flex items-center justify-center gap-1 min-w-[36px] h-9 px-2.5 rounded-md border border-outline-variant text-[13px] font-semibold transition-all duration-200"
        :class="
          currentPage >= totalPages
            ? 'opacity-40 cursor-not-allowed text-text-muted bg-transparent'
            : 'text-text-main hover:bg-surface-vlm hover:border-primary/40 active:scale-95 cursor-pointer shadow-2xs'
        "
        :disabled="currentPage >= totalPages"
        @click="goToPage(currentPage + 1)"
        aria-label="Trang sau"
      >
        <span class="hidden md:inline">Sau</span>
        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
      </button>
    </div>
  </nav>
</template>
