import { ref, onMounted, onUnmounted } from 'vue'

export function useScrollReveal() {
  const observer = ref<IntersectionObserver | null>(null)
  const mutationObserver = ref<MutationObserver | null>(null)
  let forceTimer: number | null = null

  const forceRevealAll = () => {
    document
      .querySelectorAll(
        '.reveal:not(.revealed), .reveal-left:not(.revealed), .reveal-right:not(.revealed), .reveal-scale:not(.revealed), .reveal-up-scale:not(.revealed), .reveal-fade:not(.revealed)',
      )
      .forEach((el) => el.classList.add('revealed'))
    document
      .querySelectorAll('.stagger-grid:not(.revealed)')
      .forEach((el) => el.classList.add('revealed'))
  }

  onMounted(() => {
    const isMobile = window.innerWidth <= 768
    forceTimer = window.setTimeout(() => {
      const hidden = document.querySelectorAll(
        '.reveal:not(.revealed), .reveal-left:not(.revealed), .reveal-right:not(.revealed), .reveal-scale:not(.revealed), .reveal-up-scale:not(.revealed), .reveal-fade:not(.revealed), .stagger-grid:not(.revealed)',
      )
      if (hidden.length > 0) {
        hidden.forEach((el) => el.classList.add('revealed'))
      }
    }, isMobile ? 800 : 1500)

    observer.value = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed')
            observer.value?.unobserve(entry.target)
          }
        })
      },
      { threshold: isMobile ? 0.02 : 0.05, rootMargin: isMobile ? '0px 0px 10px 0px' : '0px 0px -30px 0px' }
    )

    const observeElements = () => {
      document
        .querySelectorAll(
          '.reveal:not(.revealed), .reveal-left:not(.revealed), .reveal-right:not(.revealed), .reveal-scale:not(.revealed), .reveal-up-scale:not(.revealed), .reveal-fade:not(.revealed)',
        )
        .forEach((el) => {
          observer.value?.observe(el)
        })
      document.querySelectorAll('.stagger-grid:not(.revealed)').forEach((el) => {
        observer.value?.observe(el)
      })
    }

    observeElements()

    mutationObserver.value = new MutationObserver(() => {
      observeElements()
    })

    mutationObserver.value.observe(document.body, {
      childList: true,
      subtree: true,
    })
  })

  onUnmounted(() => {
    observer.value?.disconnect()
    mutationObserver.value?.disconnect()
    if (forceTimer) window.clearTimeout(forceTimer)
  })

  return { observer, forceRevealAll }
}
