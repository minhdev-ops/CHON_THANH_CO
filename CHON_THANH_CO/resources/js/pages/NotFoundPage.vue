<script setup lang="ts">
import { t } from '../i18n'
</script>

<template>
  <div class="min-h-[90vh] flex items-center justify-center px-margin-mobile relative overflow-hidden bg-surface">
    <!-- Decorative background -->
    <div class="absolute inset-0 dot-pattern opacity-[0.03]"></div>
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-primary/10 rounded-full blur-[120px] animate-pulse-slow"></div>
      <div class="absolute bottom-1/4 right-1/4 w-56 h-56 bg-primary/5 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s"></div>
    </div>

    <div class="text-center reveal max-w-lg relative z-10">
      <!-- 404 Number -->
      <div class="relative mb-10">
        <span class="text-[160px] md:text-[220px] font-bold leading-none select-none tracking-tight text-primary/15">404</span>
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="w-28 h-28 rounded-3xl bg-surface-bright border border-outline-variant flex items-center justify-center animate-float shadow-[0_8px_32px_rgba(184,155,136,0.12)]">
            <span class="material-symbols-outlined text-[48px] text-primary-deep">explore_off</span>
          </div>
        </div>
      </div>

      <h2 class="text-[28px] md:text-[36px] text-text-main mb-5 font-extrabold tracking-tight">{{ t('nf.title') }}</h2>
      <p class="text-text-secondary mb-12 leading-[1.8] text-[15px] md:text-[16px] max-w-md mx-auto">{{ t('nf.text') }}</p>

      <div class="flex flex-wrap justify-center gap-4 mb-12">
        <router-link to="/" class="btn bg-primary text-white hover:bg-primary-dark rounded-full inline-flex items-center gap-2 group py-3.5 px-8 text-[15px] font-bold shadow-[0_4px_20px_rgba(184,155,136,0.3)] transition-all duration-300 hover:shadow-lg">
          <span class="material-symbols-outlined text-[18px] group-hover:-translate-y-0.5 transition-transform">home</span>
          {{ t('nf.backHome') }}
        </router-link>
        <router-link to="/products" class="btn border border-outline-variant text-text-main hover:border-primary hover:text-primary rounded-full inline-flex items-center gap-2 group py-3.5 px-8 text-[15px] font-bold transition-all duration-300">
          <span class="material-symbols-outlined text-[18px] group-hover:-translate-y-0.5 transition-transform">category</span>
          {{ t('nav.products') }}
        </router-link>
        <router-link to="/contact" class="btn border border-outline-variant text-text-main hover:border-primary hover:text-primary rounded-full inline-flex items-center gap-2 group py-3.5 px-8 text-[15px] font-bold transition-all duration-300">
          <span class="material-symbols-outlined text-[18px] group-hover:-translate-y-0.5 transition-transform">mail</span>
          {{ t('nav.contact') }}
        </router-link>
      </div>

      <!-- Decorative line -->
      <div class="flex items-center justify-center gap-3 text-text-muted">
        <div class="w-12 h-px bg-outline-variant"></div>
        <span class="material-symbols-outlined text-sm">more_horiz</span>
        <div class="w-12 h-px bg-outline-variant"></div>
      </div>
    </div>
  </div>
</template>
