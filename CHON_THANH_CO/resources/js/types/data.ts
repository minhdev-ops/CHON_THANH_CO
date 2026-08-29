import type { NavLink } from './index'

export const navLinks: NavLink[] = [
  { label: 'Trang chủ', to: '/' },
  { label: 'Giới thiệu', to: '/about' },
  { label: 'Sản phẩm', to: '/products' },
  { label: 'Dự án', to: '/projects' },
  { label: 'Chứng chỉ', to: '/certificates' },
  { label: 'Tin tức', to: '/news' },
  { label: 'Liên hệ', to: '/contact' },
]

export const navLinkKeys: Record<string, string> = {
  '/': 'nav.home',
  '/about': 'nav.about',
  '/products': 'nav.products',
  '/projects': 'nav.projects',
  '/certificates': 'nav.certificates',
  '/news': 'nav.news',
  '/contact': 'nav.contact',
}
