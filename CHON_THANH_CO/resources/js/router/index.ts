import { createRouter, createWebHistory } from 'vue-router'
import { t, locale } from '../i18n'
import { watch } from 'vue'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  },
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../pages/HomePage.vue'),
      meta: { titleKey: 'meta.home', descKey: 'meta.desc.home' },
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../pages/AboutPage.vue'),
      meta: { titleKey: 'meta.about', descKey: 'meta.desc.about' },
    },
    {
      path: '/about/capability',
      name: 'about-capability',
      component: () => import('../pages/CapabilityProfilePage.vue'),
      meta: { titleKey: 'meta.capability', descKey: 'meta.desc.capability' },
    },
    {
      path: '/about/certification',
      name: 'about-certification',
      component: () => import('../pages/CertificationDossierPage.vue'),
      meta: { titleKey: 'meta.certification', descKey: 'meta.desc.certification' },
    },
    {
      path: '/products',
      name: 'products',
      component: () => import('../pages/ProductsPage.vue'),
      meta: { titleKey: 'meta.products', descKey: 'meta.desc.products' },
    },
    {
      path: '/products/:slug',
      name: 'product-detail',
      component: () => import('../pages/ProductDetailPage.vue'),
      meta: { titleKey: 'meta.products', descKey: 'meta.desc.products' },
    },
    {
      path: '/projects',
      name: 'projects',
      component: () => import('../pages/ProjectsPage.vue'),
      meta: { titleKey: 'meta.projects', descKey: 'meta.desc.projects' },
    },
    {
      path: '/projects/:slug',
      name: 'project-detail',
      component: () => import('../pages/ProjectDetailPage.vue'),
      meta: { titleKey: 'meta.projects', descKey: 'meta.desc.projects' },
    },
    {
      path: '/certificates',
      name: 'certificates',
      component: () => import('../pages/CertificatesPage.vue'),
      meta: { titleKey: 'meta.certificates', descKey: 'meta.desc.certificates' },
    },
    {
      path: '/contact',
      name: 'contact',
      component: () => import('../pages/ContactPage.vue'),
      meta: { titleKey: 'meta.contact', descKey: 'meta.desc.contact' },
    },
    {
      path: '/news',
      name: 'news',
      component: () => import('../pages/NewsPage.vue'),
      meta: { titleKey: 'meta.news', descKey: 'meta.desc.news' },
    },
    {
      path: '/news/:slug',
      name: 'news-detail',
      component: () => import('../pages/NewsDetailPage.vue'),
      meta: { titleKey: 'meta.news', descKey: 'meta.desc.news' },
    },
    {
      path: '/faq',
      name: 'faq',
      component: () => import('../pages/FaqPage.vue'),
      meta: { titleKey: 'meta.faq', descKey: 'meta.desc.faq' },
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('../pages/NotFoundPage.vue'),
      meta: { titleKey: 'meta.notFound', descKey: 'meta.desc.notFound' },
    },
  ],
})

function setMetaTag(attr: 'name' | 'property', key: string, content: string) {
  let el = document.head.querySelector<HTMLMetaElement>(`meta[${attr}="${key}"]`)
  if (!el) {
    el = document.createElement('meta')
    el.setAttribute(attr, key)
    document.head.appendChild(el)
  }
  el.setAttribute('content', content)
}

function applyMeta() {
  const meta = router.currentRoute.value.meta
  if (meta.titleKey) {
    const title = t(String(meta.titleKey))
    document.title = title
    setMetaTag('property', 'og:title', title)
  }
  if (meta.descKey) {
    setMetaTag('property', 'og:description', t(String(meta.descKey)))
  }
  setMetaTag('property', 'og:url', window.location.href)
}

router.afterEach(() => applyMeta())
watch(locale, () => applyMeta())

export default router
