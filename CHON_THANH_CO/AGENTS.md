# AGENTS.md — CHON THANH Geosynthetics

## Project Overview
- **Project**: CHON THANH Geosynthetics company website
- **Tech Stack**: Laravel 12 + Vue 3 (Composition API, `<script setup>`) + TypeScript + Vue Router + Tailwind CSS v4 + Vite
- **Purpose**: B2B geosynthetic materials supplier website with product catalog, project portfolio, certificates, contact form

## Architecture
- **Backend**: Laravel 12 serves the SPA entry point (`resources/views/app.blade.php`)
- **Frontend**: Vue 3 SPA with Vue Router (`resources/js/`)
- **Routing**: All routes handled by Vue Router, Laravel catches all with `/{any}`
- **Styling**: Tailwind CSS v4 with custom design tokens in `resources/css/app.css`
- **Build**: Vite with `@vitejs/plugin-vue` and `@tailwindcss/vite`

## Directory Structure
```
resources/
├── css/app.css                    # Tailwind v4 config + design tokens
├── js/
│   ├── app.ts                     # Vue app entry
│   ├── env.d.ts                   # TypeScript declarations
│   ├── router/index.ts            # Vue Router config
│   ├── types/index.ts             # TypeScript interfaces
│   ├── types/data.ts              # All static data (products, projects, etc.)
│   ├── composables/useScrollReveal.ts  # Intersection Observer scroll animations
│   ├── layouts/AppLayout.vue      # Main layout with Navbar + Footer
│   ├── components/
│   │   ├── Navbar.vue             # Responsive navigation
│   │   └── Footer.vue             # 3-column footer
│   └── pages/
│       ├── HomePage.vue           # Hero, products, why-us, projects, CTA
│       ├── AboutPage.vue          # Timeline, mission, stats, certificates
│       ├── ProductsPage.vue       # Product catalog with filters
│       ├── ProductDetailPage.vue  # Product detail with specs, tabs
│       ├── ProjectsPage.vue       # Project portfolio
│       ├── ProjectDetailPage.vue  # Project detail with gallery
│       ├── CertificatesPage.vue   # Certificates & authorizations
│       ├── ContactPage.vue        # Contact form + info
│       ├── NewsPage.vue           # News & articles
│       ├── FaqPage.vue            # FAQ accordion
│       └── NotFoundPage.vue       # 404 page
└── views/app.blade.php            # SPA entry point
```

## Design System
**See `DESIGN.md` for full details**

## Page Transition
- Vue `<transition name="page">` for route changes
- CSS classes: `.page-enter-active`, `.page-leave-active`, `.page-enter-from`, `.page-leave-to`

## Animation
- Scroll reveal: `.reveal` + `.revealed` classes (Intersection Observer)
- Hover effects: `.hover-lift` (translateY + shadow), `.image-zoom` (scale)

## Commands
- `npm run dev` — Start Vite dev server
- `npm run build` — Build for production
- `php artisan serve` — Start Laravel dev server

## Routes (Vue Router)
| Path | Page | Description |
|------|------|-------------|
| `/` | HomePage | Landing page |
| `/about` | AboutPage | Company info |
| `/products` | ProductsPage | Product listing |
| `/products/:slug` | ProductDetailPage | Single product |
| `/projects` | ProjectsPage | Project portfolio |
| `/projects/:slug` | ProjectDetailPage | Single project |
| `/certificates` | CertificatesPage | Certificates |
| `/contact` | ContactPage | Contact form |
| `/news` | NewsPage | News & articles |
| `/faq` | FaqPage | FAQ accordion |
| `/:pathMatch(.*)*` | NotFoundPage | 404 |
