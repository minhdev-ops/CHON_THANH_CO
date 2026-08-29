<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const visible = ref(false)

function onScroll() {
  visible.value = window.scrollY > 400
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})
</script>

<template>
  <transition name="scroll-top">
    <button
      v-if="visible"
      type="button"
      aria-label="Cuộn lên đầu trang"
      class="fixed bottom-6 left-6 z-40 flex items-center gap-2 px-4 py-2.5 bg-[#16243D] text-white rounded-full shadow-lg hover:bg-[#B89B88] hover:shadow-xl active:scale-95 transition-all duration-300 cursor-pointer group"
      @click="scrollToTop"
    >
      <span class="material-symbols-outlined text-[20px] group-hover:-translate-y-0.5 transition-transform duration-300">arrow_upward</span>
      <span class="text-[13px] font-semibold tracking-wide hidden sm:inline">Lên đầu trang</span>
    </button>
  </transition>
</template>

<style scoped>
.scroll-top-enter-active,
.scroll-top-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.scroll-top-enter-from,
.scroll-top-leave-to {
  opacity: 0;
  transform: translateX(-16px);
}
</style>
