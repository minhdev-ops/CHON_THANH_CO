<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { t } from '../i18n'

const props = withDefaults(
  defineProps<{
    count: number
    perViewMobile?: number
    perViewSm?: number
    perViewLg?: number
    perViewXl?: number
    noGap?: boolean
    loop?: boolean
  }>(),
  {
    perViewMobile: 1,
    perViewSm: 2,
    perViewLg: 3,
    perViewXl: 4,
    noGap: false,
    loop: false,
  },
)

const viewport = ref<number>(typeof window !== 'undefined' ? window.innerWidth : 1280)
const page = ref(0)

const perView = computed(() => {
  if (viewport.value >= 1280) return props.perViewXl
  if (viewport.value >= 1024) return props.perViewLg
  if (viewport.value >= 640) return props.perViewSm
  return props.perViewMobile
})

const pageCount = computed(() => Math.max(1, Math.ceil(props.count / perView.value)))

const currentPage = computed(() => Math.min(page.value, pageCount.value - 1))

const canPrev = computed(() => props.loop ? pageCount.value > 1 : currentPage.value > 0)
const canNext = computed(() => props.loop ? pageCount.value > 1 : currentPage.value < pageCount.value - 1)

function prev() {
  if (canPrev.value) {
    page.value = page.value > 0 ? page.value - 1 : pageCount.value - 1
  }
}

function next() {
  if (canNext.value) {
    page.value = page.value < pageCount.value - 1 ? page.value + 1 : 0
  }
}

function onResize() {
  viewport.value = window.innerWidth
  page.value = 0
}

onMounted(() => window.addEventListener('resize', onResize))
onUnmounted(() => window.removeEventListener('resize', onResize))
</script>

<template>
  <div class="relative group">
    <div class="overflow-hidden" :class="{ 'px-1': !noGap }">
      <div
        class="flex transition-transform duration-500 ease-out"
        :style="{ transform: `translateX(-${currentPage * 100}%)` }"
      >
        <div
          v-for="i in count"
          :key="i"
          class="shrink-0"
          :class="{ 'px-2.5': !noGap }"
          :style="{ flexBasis: `${100 / perView}%`, maxWidth: `${100 / perView}%` }"
        >
          <slot :index="i - 1" />
        </div>
      </div>
    </div>

    <button
      v-if="canPrev"
      type="button"
      :aria-label="t('common.prev')"
      :class="[
        'absolute top-1/2 -translate-y-1/2 z-10 flex items-center justify-center w-11 h-11 rounded-full bg-white text-primary shadow-lg ring-1 ring-outline-variant hover:bg-primary hover:text-white active:scale-95 transition-all duration-200 cursor-pointer',
        noGap ? 'left-4 md:left-8' : '-left-4 md:-left-5'
      ]"
      @click="prev"
    >
      <span class="material-symbols-outlined">chevron_left</span>
    </button>
    <button
      v-if="canNext"
      type="button"
      :aria-label="t('common.next')"
      :class="[
        'absolute top-1/2 -translate-y-1/2 z-10 flex items-center justify-center w-11 h-11 rounded-full bg-white text-primary shadow-lg ring-1 ring-outline-variant hover:bg-primary hover:text-white active:scale-95 transition-all duration-200 cursor-pointer',
        noGap ? 'right-4 md:right-8' : '-right-4 md:-right-5'
      ]"
      @click="next"
    >
      <span class="material-symbols-outlined">chevron_right</span>
    </button>

    <div v-if="pageCount > 1" :class="['flex justify-center gap-2', noGap ? 'absolute bottom-6 left-1/2 -translate-x-1/2 z-10' : 'mt-8']">
      <button
        v-for="(_, i) in pageCount"
        :key="i"
        type="button"
        :aria-label="`${t('common.page')} ${i + 1}`"
        class="h-2.5 rounded-full transition-all duration-300 cursor-pointer"
        :class="[
          i === currentPage ? 'w-8 bg-primary' : 'w-2.5 bg-outline hover:bg-outline-variant',
          noGap && i !== currentPage ? 'bg-white/50 hover:bg-white' : ''
        ]"
        @click="page = i"
      ></button>
    </div>
  </div>
</template>
