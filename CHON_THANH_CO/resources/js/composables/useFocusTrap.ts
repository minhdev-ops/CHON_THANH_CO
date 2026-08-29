import { watch, onBeforeUnmount, type Ref } from 'vue'

const FOCUSABLE =
  'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), iframe, [tabindex]:not([tabindex="-1"])'

export function useFocusTrap(container: Ref<HTMLElement | null>, open: Ref<boolean>) {
  let previouslyFocused: HTMLElement | null = null

  const onKeydown = (e: KeyboardEvent) => {
    if (e.key !== 'Tab') return
    const el = container.value
    if (!el) return
    const focusables = Array.from(el.querySelectorAll<HTMLElement>(FOCUSABLE))
    if (!focusables.length) return
    const first = focusables[0]
    const last = focusables[focusables.length - 1]
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault()
      last.focus()
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault()
      first.focus()
    }
  }

  watch(
    open,
    (isOpen) => {
      if (isOpen) {
        const el = container.value
        if (!el) return
        previouslyFocused = document.activeElement as HTMLElement | null
        const focusables = Array.from(el.querySelectorAll<HTMLElement>(FOCUSABLE))
        ;(focusables[0] || el).focus()
        document.addEventListener('keydown', onKeydown)
      } else {
        document.removeEventListener('keydown', onKeydown)
        previouslyFocused?.focus()
        previouslyFocused = null
      }
    },
    { immediate: true, flush: 'post' },
  )

  onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown))
}
