import { ref, watch, type Ref } from 'vue'
import { t, locale } from '../i18n'

export function useApiData<T>(loader: () => Promise<T>, fallback?: T | (() => T | null) | null) {
  const data = ref<T | null>(null) as Ref<T | null>
  const loading = ref(true)
  const error = ref<string | null>(null)

  const resolveFallback = (): T | null => {
    if (fallback == null) return null
    return typeof fallback === 'function' ? (fallback as () => T | null)() : fallback
  }

  // Initialize with fallback immediately so the page has data even if API fails/slows
  data.value = resolveFallback()

  const load = async () => {
    loading.value = true
    error.value = null
    try {
      const result = await loader()
      data.value = result
    } catch (e) {
      error.value = e instanceof Error ? e.message : t('common.errorGeneric')
      const fb = resolveFallback()
      if (fb != null) data.value = fb
    } finally {
      loading.value = false
    }
  }

  load()

  watch(locale, () => load())

  return { data, loading, error, load }
}
