<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

const props = withDefaults(
  defineProps<{
    src?: string
    alt?: string
    fallbackSrc?: string
    aspectRatio?: string
    imageClass?: string
    containerClass?: string
    eager?: boolean
    rootMargin?: string
  }>(),
  {
    src: '',
    alt: '',
    fallbackSrc: '/images/products/geotextile-roll.jpg',
    aspectRatio: '',
    imageClass: 'w-full h-full object-cover',
    containerClass: '',
    eager: false,
    rootMargin: '200px',
  }
)

const containerRef = ref<HTMLElement | null>(null)
const isIntersected = ref(props.eager)
const isLoaded = ref(false)
const hasError = ref(false)
const currentSrc = ref('')

let observer: IntersectionObserver | null = null

const startLoading = () => {
  if (isIntersected.value) return
  isIntersected.value = true
  if (props.src) {
    currentSrc.value = props.src
  } else if (props.fallbackSrc) {
    currentSrc.value = props.fallbackSrc
  }
}

const initObserver = () => {
  if (props.eager || typeof window === 'undefined' || !('IntersectionObserver' in window)) {
    isIntersected.value = true
    currentSrc.value = props.src || props.fallbackSrc
    return
  }

  if (containerRef.value) {
    const isMobile = window.innerWidth <= 768
    observer = new IntersectionObserver(
      (entries) => {
        const [entry] = entries
        if (entry && entry.isIntersecting) {
          startLoading()
          if (containerRef.value && observer) {
            observer.unobserve(containerRef.value)
            observer.disconnect()
            observer = null
          }
        }
      },
      {
        rootMargin: isMobile ? '50px' : props.rootMargin,
        threshold: 0.01,
      }
    )
    observer.observe(containerRef.value)
  }
}

const onImageLoad = () => {
  isLoaded.value = true
  hasError.value = false
}

const onImageError = () => {
  if (!hasError.value && props.fallbackSrc && currentSrc.value !== props.fallbackSrc) {
    hasError.value = true
    currentSrc.value = props.fallbackSrc
  } else {
    isLoaded.value = true
  }
}

watch(
  () => props.src,
  (newSrc) => {
    if (newSrc) {
      hasError.value = false
      if (isIntersected.value) {
        isLoaded.value = false
        currentSrc.value = newSrc
      }
    }
  }
)

onMounted(() => {
  if (props.eager) {
    currentSrc.value = props.src || props.fallbackSrc
  } else {
    initObserver()
  }
})

onBeforeUnmount(() => {
  if (observer) {
    observer.disconnect()
    observer = null
  }
})
</script>

<template>
  <div
    ref="containerRef"
    class="relative overflow-hidden bg-surface-vlm select-none"
    :class="[aspectRatio, containerClass]"
  >
    <!-- Skeleton loader placeholder while not loaded -->
    <div
      v-if="!isLoaded"
      class="absolute inset-0 w-full h-full bg-surface-vlm animate-shimmer flex items-center justify-center z-0"
    >
      <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center opacity-60">
        <span class="material-symbols-outlined text-[18px] text-primary/40 animate-pulse">image</span>
      </div>
    </div>

    <!-- The actual image -->
    <img
      v-if="isIntersected && currentSrc"
      :src="currentSrc"
      :alt="alt"
      :class="[
        imageClass,
        'transition-opacity duration-500 ease-out',
        isLoaded ? 'opacity-100' : 'opacity-0'
      ]"
      loading="lazy"
      decoding="async"
      @load="onImageLoad"
      @error="onImageError"
    />

    <!-- Slot for overlay elements like badges, gradients, buttons -->
    <slot />
  </div>
</template>
