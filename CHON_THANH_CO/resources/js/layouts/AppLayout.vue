<script setup lang="ts">
import { useScrollReveal } from '../composables/useScrollReveal'
import Navbar from '../components/Navbar.vue'
import Footer from '../components/Footer.vue'
import FloatingContactWidget from '../components/FloatingContactWidget.vue'
import MobileTabBar from '../components/MobileTabBar.vue'
import ScrollToTop from '../components/ScrollToTop.vue'

useScrollReveal()
</script>

<template>
  <div class="app-shell antialiased flex flex-col min-h-screen bg-canvas text-text-primary">
    <Navbar />
    <main class="flex-grow">
      <router-view v-slot="{ Component }">
        <transition name="page" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
    <Footer />
    <FloatingContactWidget />
    <MobileTabBar />
    <ScrollToTop />
  </div>
</template>
