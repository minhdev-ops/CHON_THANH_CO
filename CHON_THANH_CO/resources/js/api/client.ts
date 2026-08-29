import type {
  Application,
  AboutTimeline,
  Category,
  Certificate,
  FaqItem,
  HomeData,
  NewsCategory,
  NewsItem,
  Product,
  Project,
} from '../types'
import { locale } from '../i18n'

const BASE = '/api/v1'

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

interface QueryParams {
  [key: string]: string | number | undefined | null
}

function buildQuery(params: QueryParams = {}): string {
  const parts = Object.entries(params)
    .filter(([, value]) => value !== undefined && value !== null && value !== '')
    .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(String(value))}`)

  return parts.length ? `?${parts.join('&')}` : ''
}

async function get<T>(path: string, params: QueryParams = {}): Promise<T> {
  const res = await fetch(`${BASE}${path}${buildQuery(params)}`, {
    headers: { Accept: 'application/json', 'X-Locale': locale.value },
  })

  if (!res.ok) {
    let message = `HTTP ${res.status}`
    try {
      const body = await res.json()
      message = body.message || message
    } catch {
      /* ignore parse errors */
    }
    throw new ApiError(res.status, message)
  }

  return res.json()
}

interface CursorResponse<T> {
  data: T[]
  next_cursor: number | null
}

export const api = {
  home: () => get<HomeData>('/home'),
  settings: () => get<Record<string, string>>('/settings'),
  timeline: () => get<{ data: AboutTimeline[] }>('/about/timeline'),

  categories: () => get<{ data: Category[] }>('/categories'),
  applications: () => get<{ data: Application[] }>('/applications'),
  products: (params: QueryParams = {}) => get<CursorResponse<Product>>('/products', params),
  product: (slug: string) => get<{ data: Product }>(`/products/${slug}`),

  projects: (params: QueryParams = {}) => get<CursorResponse<Project>>('/projects', params),
  project: (slug: string) => get<{ data: Project }>(`/projects/${slug}`),

  certificates: () => get<{ data: Certificate[] }>('/certificates'),

  news: (params: QueryParams = {}) => get<CursorResponse<NewsItem>>('/news', params),
  newsItem: (slug: string) => get<{ data: NewsItem }>(`/news/${slug}`),
  newsCategories: () => get<NewsCategory[]>('/news/categories'),

  faqs: () => get<{ data: FaqItem[] }>('/faqs'),

  contact: (payload: Record<string, string | string[]>) =>
    fetch(`${BASE}/contact`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Locale': locale.value,
      },
      body: JSON.stringify(payload),
    }),
}
