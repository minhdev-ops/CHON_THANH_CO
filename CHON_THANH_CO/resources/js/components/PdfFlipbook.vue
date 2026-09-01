<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, nextTick, shallowRef, watch } from 'vue'

const props = defineProps<{
  pdfUrl: string
  title?: string
  hideClose?: boolean
  hideHeader?: boolean
}>()

const emit = defineEmits(['close'])

const containerRef = ref<HTMLElement | null>(null)
const flipbookRef = ref<HTMLElement | null>(null)
const loading = ref(true)
const progress = ref(0)
const error = ref('')
const pages = ref<string[]>([])
const currentPage = ref(1)
const totalPages = ref(0)
let pageFlip: any = null

const loadScripts = async () => {
  return new Promise<void>((resolve, reject) => {
    const win = window as any;
    if (win.pdfjsLib && win.St) {
      resolve()
      return
    }

    const scriptPdf = document.createElement('script')
    scriptPdf.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js'
    
    scriptPdf.onload = () => {
      win.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'
      
      const scriptFlip = document.createElement('script')
      scriptFlip.src = 'https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js'
      scriptFlip.onload = () => resolve()
      scriptFlip.onerror = reject
      document.head.appendChild(scriptFlip)
    }
    scriptPdf.onerror = reject
    document.head.appendChild(scriptPdf)
  })
}

const renderPdf = async () => {
  try {
    loading.value = true
    progress.value = 10
    
    await loadScripts()
    
    const win = window as any;
    const pdf = await win.pdfjsLib.getDocument(props.pdfUrl).promise
    totalPages.value = pdf.numPages
    
    // Khởi tạo mảng trống để hiển thị khung lật trang ngay lập tức
    const emptyPages = new Array(totalPages.value).fill('')
    pages.value = [...emptyPages]
    
    loading.value = false
    await nextTick()
    initFlipbook()
    
    // Render từng trang PDF dưới nền
    for (let i = 1; i <= totalPages.value; i++) {
      // Để tránh block UI quá lâu, dùng requestAnimationFrame hoặc setTimeout
      await new Promise(resolve => setTimeout(resolve, 0))
      
      const page = await pdf.getPage(i)
      const viewport = page.getViewport({ scale: 1.5 })
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')
      
      canvas.width = viewport.width
      canvas.height = viewport.height
      
      await page.render({
        canvasContext: ctx!,
        viewport: viewport
      }).promise
      
      pages.value[i - 1] = canvas.toDataURL('image/jpeg', 0.8)
    }
    
  } catch (err: any) {
    console.error('Error rendering PDF:', err)
    error.value = 'Không thể tải tệp PDF. Vui lòng thử lại.'
    loading.value = false
  }
}

const initFlipbook = () => {
  const win = window as any;
  if (!flipbookRef.value || !win.St) return
  
  // Create page-flip instance
  pageFlip = new win.St.PageFlip(flipbookRef.value, {
    width: 500,
    height: 707,
    size: 'stretch',
    minWidth: 315,
    maxWidth: 1000,
    minHeight: 400,
    maxHeight: 1414,
    maxShadowOpacity: 0.5,
    showCover: true,
    mobileScrollSupport: false
  })
  
  // Load pages from elements
  const pageElements = flipbookRef.value.querySelectorAll('.page')
  pageFlip.loadFromHTML(pageElements)
  
  // Listen to flip events
  pageFlip.on('flip', (e: any) => {
    currentPage.value = e.data + 1
  })
}

const nextPage = () => {
  if (pageFlip) pageFlip.flipNext()
}

const prevPage = () => {
  if (pageFlip) pageFlip.flipPrev()
}

onMounted(() => {
  if (props.pdfUrl) {
    renderPdf()
  }
})

watch(() => props.pdfUrl, (newUrl) => {
  if (newUrl) {
    if (pageFlip) {
      pageFlip.destroy()
      pageFlip = null
    }
    pages.value = []
    renderPdf()
  }
})

onBeforeUnmount(() => {
  if (pageFlip) {
    pageFlip.destroy()
  }
})

const downloadPdf = () => {
  const a = document.createElement('a')
  a.href = props.pdfUrl
  a.download = 'ho-so-nang-luc.pdf'
  a.click()
}
</script>

<template>
  <div class="pdf-flipbook-container w-full h-full flex flex-col relative bg-surface-vlm overflow-hidden" ref="containerRef">
    <!-- Header Controls -->
    <div v-if="!hideHeader" class="flex items-center justify-between gap-4 px-6 md:px-8 py-4 border-b border-outline-variant bg-surface-bright shrink-0 z-10">
      <div class="flex items-center gap-4">
        <h3 class="font-bold text-lg text-text-main truncate hidden sm:block">{{ title || 'Xem Tài Liệu (3D)' }}</h3>
      </div>
      
      <div class="flex items-center gap-3 flex-shrink-0">
        <!-- Pagination controls -->
        <div v-if="!loading && !error && pages.length > 0" class="flex items-center bg-surface-vlm rounded-full border border-outline-variant p-1 mr-2">
          <button @click="prevPage" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white transition-colors" :disabled="currentPage <= 1">
            <span class="material-symbols-outlined text-lg">chevron_left</span>
          </button>
          <span class="text-sm font-medium px-3 text-text-main">
            {{ currentPage }} / {{ totalPages }}
          </span>
          <button @click="nextPage" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white transition-colors" :disabled="currentPage >= totalPages">
            <span class="material-symbols-outlined text-lg">chevron_right</span>
          </button>
        </div>
        
        <button type="button" class="btn bg-primary text-white hover:bg-primary-dark py-2 px-4 rounded-full font-bold text-[13px] flex items-center justify-center gap-2 transition-all duration-300" @click="downloadPdf">
          <span class="material-symbols-outlined text-[16px]">download</span> Tải bản gốc
        </button>
        
        <button v-if="!hideClose" type="button" class="w-10 h-10 rounded-full flex items-center justify-center bg-surface-vlm border border-outline-variant hover:bg-outline-variant hover:text-primary transition-all duration-300" @click="$emit('close')">
          <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
      </div>
    </div>

    <!-- Viewer Area -->
    <div class="flex-1 relative flex items-center justify-center overflow-hidden p-4 md:p-12 bg-surface-vlm" :class="{'rounded-2xl': hideHeader}">
      
      <!-- Floating Navigation -->
      <button v-show="!loading && !error && pages.length > 0" @click="prevPage" class="absolute left-2 md:left-6 top-1/2 -translate-y-1/2 z-30 w-10 h-10 md:w-14 md:h-14 rounded-full bg-white shadow-xl border border-outline-variant flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300 group disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-text-main disabled:cursor-not-allowed">
        <span class="material-symbols-outlined text-[24px] md:text-[32px] group-hover:-translate-x-1 transition-transform disabled:transform-none">chevron_left</span>
      </button>
      
      <button v-show="!loading && !error && pages.length > 0" @click="nextPage" class="absolute right-2 md:right-6 top-1/2 -translate-y-1/2 z-30 w-10 h-10 md:w-14 md:h-14 rounded-full bg-white shadow-xl border border-outline-variant flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300 group disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-text-main disabled:cursor-not-allowed">
        <span class="material-symbols-outlined text-[24px] md:text-[32px] group-hover:translate-x-1 transition-transform disabled:transform-none">chevron_right</span>
      </button>

      <!-- Pagination floating -->
      <div v-if="hideHeader && !loading && !error && pages.length > 0" class="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 bg-white/90 backdrop-blur-md px-6 py-2.5 rounded-full shadow-lg border border-outline-variant text-text-main font-bold text-[15px]">
        {{ currentPage }} / {{ totalPages }}
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="absolute inset-0 flex flex-col items-center justify-center bg-surface-vlm z-20">
        <div class="w-16 h-16 border-4 border-outline-variant border-t-primary rounded-full animate-spin mb-4"></div>
        <p class="text-text-main font-semibold mb-2">Đang xử lý tệp PDF...</p>
        <div class="w-64 h-2 bg-outline-variant rounded-full overflow-hidden">
          <div class="h-full bg-primary transition-all duration-300" :style="{ width: `${progress}%` }"></div>
        </div>
        <p class="text-text-secondary text-sm mt-2">{{ progress }}%</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="absolute inset-0 flex flex-col items-center justify-center z-20 px-4 text-center">
        <span class="material-symbols-outlined text-error text-5xl mb-4">error</span>
        <p class="text-error font-semibold mb-4">{{ error }}</p>
        <button @click="renderPdf" class="btn bg-primary text-white py-2 px-6 rounded-full font-bold">Thử lại</button>
      </div>

      <!-- Flipbook container -->
      <div v-show="!loading && !error && pages.length > 0" class="flipbook-wrapper w-full max-w-5xl h-full relative perspective-[2000px]">
        <div ref="flipbookRef" class="flipbook mx-auto shadow-2xl">
          <div v-for="(page, index) in pages" :key="index" class="page bg-white relative overflow-hidden" :class="{'page-cover': index === 0 || index === pages.length - 1}">
            <div class="page-content w-full h-full bg-surface-bright flex items-center justify-center">
              <img v-if="page" :src="page" class="w-full h-full object-contain pointer-events-none select-none" />
              <div v-else class="flex flex-col items-center justify-center opacity-50">
                <div class="w-8 h-8 border-4 border-outline-variant border-t-primary rounded-full animate-spin mb-2"></div>
                <span class="text-xs font-medium text-text-secondary">Đang tải...</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.flipbook-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
}

.flipbook {
  background-color: transparent;
}

.page {
  background-color: #fff;
  border: 1px solid #e0e0e0;
  box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
}

.page-cover {
  background-color: #f8f9fa;
}

.page-content {
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
