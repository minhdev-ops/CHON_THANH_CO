import { ref, computed } from 'vue'
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

  const socialSettings = computed(() => {
    if (!cache.value) return []
    const networks = []
    
    for (const [key, val] of Object.entries(cache.value)) {
      if (key.startsWith('social.') && val && key !== 'social.ggmap') {
        const network = key.replace('social.', '')
        let icon = '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>'
        
        if (network === 'facebook') icon = '<path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>'
        if (network === 'zalo') icon = '<path d="M21.1 10.999c0-5.523-4.992-10-11.1-10C3.892.999-.1 5.476-.1 10.999c0 3.12 1.624 5.922 4.148 7.82l-1.077 3.328a.56.56 0 00.707.69l3.856-1.57a11.512 11.512 0 002.466.264c6.108 0 11.1-4.477 11.1-10z"/>'
        if (network === 'messenger') icon = '<path d="M12 2C6.477 2 2 6.14 2 11.25c0 2.91 1.528 5.495 3.865 7.151v3.916l3.553-1.956c.82.227 1.684.349 2.582.349 5.523 0 10-4.14 10-9.25C22 6.14 17.523 2 12 2zm1.09 13.064l-2.793-2.983-5.44 2.983 5.967-6.326 2.85 2.982 5.385-2.982-5.97 6.326z"/>'
        if (network === 'tiktok') icon = '<path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93v8.12c0 2.28-.73 4.54-2.12 6.32-1.35 1.75-3.32 2.93-5.5 3.29-2.15.35-4.41.05-6.36-1.02-1.93-1.05-3.41-2.65-4.18-4.66-.78-2-.84-4.27-.2-6.3.62-1.99 1.95-3.66 3.73-4.69 1.74-.99 3.8-1.34 5.76-.99V12.2c-1.15-.22-2.35-.11-3.42.34-1.08.45-2.02 1.25-2.6 2.25-.59 1-.87 2.2-.82 3.39.05 1.18.45 2.31 1.15 3.23.71.91 1.7 1.55 2.83 1.83 1.15.28 2.37.21 3.48-.23 1.09-.44 2.02-1.23 2.6-2.22.58-1 .85-2.19.8-3.37v-13.4c0-1.32.01-2.65.01-3.98z"/>'
        if (network === 'youtube') icon = '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>'

        networks.push({ name: network, url: val, icon })
      }
    }
    return networks
  })

  return { settings: cache, load, socialSettings }
}
