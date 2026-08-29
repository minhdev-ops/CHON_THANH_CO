import { ref } from 'vue'
import { api } from '../api/client'

const cache = ref<Record<string, string> | null>(null)
let pending: Promise<Record<string, string> | null> | null = null

export function useSettings() {
  async function load(): Promise<Record<string, string> | null> {
    if (cache.value) return cache.value
    if (!pending) {
      pending = api.settings()
        .then((data) => {
          cache.value = data
          return data
        })
        .catch(() => null)
        .finally(() => {
          pending = null
        })
    }
    return pending
  }

  return { settings: cache, load }
}
