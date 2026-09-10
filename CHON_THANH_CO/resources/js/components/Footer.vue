<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useSettings } from '../composables/useSettings'
import { getYearsOfExperience } from '../utils/experience'

const { settings, load } = useSettings()

const phones = computed(() => {
  const raw = settings.value?.['contact.phone']?.trim() || '0909 292 530'
  return raw.split(/\s+[-/;,|]\s+|\s{2,}/).map((p) => p.trim()).filter(Boolean)
    .map((p) => ({ display: p, href: `tel:${p.replace(/[^\d+]/g, '')}` }))
})

const quickLinks = [
  { label: 'Giới thiệu',  to: '/about' },
  { label: 'Sản phẩm',    to: '/products' },
  { label: 'Dự án',       to: '/projects' },
  { label: 'Chứng nhận',  to: '/certificates' },
  { label: 'Tin tức',     to: '/news' },
  { label: 'Liên hệ',     to: '/contact' },
  { label: 'Chính sách bảo mật', to: '/privacy-policy' },
  { label: 'Điều khoản sử dụng dịch vụ', to: '/terms-of-service' },
]

const productLinks = [
  { label: 'Vải địa kỹ thuật',    slug: 'vai-dia-ky-thuat' },
  { label: 'Lưới địa kỹ thuật',   slug: 'luoi-dia-ky-thuat' },
  { label: 'Thảm chống xói mòn',  slug: 'tham-chong-xoi-mon' },
  { label: 'Đá & Lưới thép',      slug: 'da-luoi-thep' },
  { label: 'Màng chống thấm HDPE', slug: 'mang-chong-tham' },
]

onMounted(() => load())
</script>

<template>
  <footer class="site-footer">
    <div class="footer-accent"></div>

    <!-- ═══ Desktop ═══ -->
    <div class="footer-desktop">
      <!-- Brand -->
      <div class="footer-brand">
        <router-link to="/" class="footer-logo-link">
          <img src="/images/logo.svg" alt="CHƠN THÀNH Logo" class="footer-logo-img">
          <div>
            <span class="footer-logo-name">CHƠN THÀNH</span>
            <span class="footer-logo-sub">GEOSYNTHETICS</span>
          </div>
        </router-link>
        <p class="footer-desc">
          {{ settings?.['company.description'] || `Nhà phân phối chính thức vật liệu địa kỹ thuật ARITEX và các sản phẩm gia cố nền, chống thấm, chống xói mòn phục vụ hạ tầng giao thông, thủy lợi và bảo vệ môi trường tại Việt Nam và xuất khẩu.` }}
        </p>
        <div class="footer-badges">
          <div class="footer-badge">
            <span class="material-symbols-outlined text-white text-[18px] fill">workspace_premium</span>
            <span>ISO 9001:2015</span>
          </div>
          <div class="footer-badge-divider"></div>
          <div class="footer-badge">
            <span class="material-symbols-outlined text-white text-[18px] fill">workspace_premium</span>
            <span>TCVN 9844</span>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="footer-links-col">
        <h3 class="footer-heading">Điều hướng</h3>
        <nav class="footer-nav">
          <router-link v-for="link in quickLinks" :key="link.to" :to="link.to"
            class="footer-nav-link">
            <span class="footer-dot"></span>
            {{ link.label }}
          </router-link>
        </nav>
      </div>

      <!-- Product Links -->
      <div class="footer-links-col">
        <h3 class="footer-heading">Sản phẩm</h3>
        <nav class="footer-nav">
          <router-link v-for="p in productLinks" :key="p.slug" :to="`/products?category=${p.slug}`"
            class="footer-nav-link">
            <span class="footer-dot"></span>
            {{ p.label }}
          </router-link>
        </nav>
      </div>

      <!-- Contact Info -->
      <div class="footer-links-col">
        <h3 class="footer-heading">Liên hệ</h3>
        <div class="footer-contact">
          <a :href="settings?.['social.ggmap'] || 'https://www.google.com/maps'" target="_blank" rel="noopener noreferrer" class="footer-contact-item">
            <span class="material-symbols-outlined footer-contact-icon">location_on</span>
            <span class="footer-contact-text">{{ settings?.['contact.address'] || '416A Đường CC2, Phường Tây Thạnh, Thành Phố Hồ Chí Minh' }}</span>
          </a>
          <div class="footer-contact-item">
            <span class="material-symbols-outlined footer-contact-icon">call</span>
            <div class="footer-contact-text">
              <a v-for="phone in phones" :key="phone.href" :href="phone.href" class="footer-phone">{{ phone.display }}</a>
            </div>
          </div>
          <div class="footer-contact-item">
            <span class="material-symbols-outlined footer-contact-icon">mail</span>
            <span class="footer-contact-text">{{ settings?.['contact.email'] || 'chonthanhco@gmail.com' }}</span>
          </div>
          <div class="footer-contact-item">
            <span class="material-symbols-outlined footer-contact-icon">schedule</span>
            <span class="footer-contact-text">{{ settings?.['contact.working_hours'] || 'Thứ 2 - Thứ 6: 08:30 - 17:30' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ Mobile ═══ -->
    <div class="footer-mobile">
      <div class="footer-mobile-header">
        <router-link to="/" class="footer-logo-link">
          <img src="/images/logo.svg" alt="Logo" class="h-11 w-auto object-contain">
          <div>
            <span class="block font-extrabold text-[20px] leading-tight text-white tracking-tight">CHƠN THÀNH</span>
            <span class="block text-[11px] font-bold tracking-[0.3em] uppercase text-white/50">GEOSYNTHETICS</span>
          </div>
        </router-link>
        <p class="text-white/55 text-[14px] text-center mt-1">Vật liệu địa kỹ thuật hàng đầu Việt Nam</p>
      </div>

      <div class="px-6 mb-6">
        <h4 class="text-[12px] font-bold text-white/40 uppercase tracking-[0.2em] mb-3">Liên kết nhanh</h4>
        <div class="space-y-0 mb-5">
          <router-link v-for="link in quickLinks" :key="link.to" :to="link.to"
            class="flex items-center justify-between py-3 border-b border-white/8 text-white/85 text-[16px] font-medium hover:text-white transition-colors">
            <span>{{ link.label }}</span>
            <span class="material-symbols-outlined text-white/30 text-[18px]">chevron_right</span>
          </router-link>
        </div>

        <h4 class="text-[12px] font-bold text-white/40 uppercase tracking-[0.2em] mb-3">Sản phẩm</h4>
        <div class="space-y-0">
          <router-link v-for="p in productLinks" :key="p.slug" :to="`/products?category=${p.slug}`"
            class="flex items-center justify-between py-3 border-b border-white/8 text-white/85 text-[16px] font-medium hover:text-white transition-colors">
            <span>{{ p.label }}</span>
            <span class="material-symbols-outlined text-white/30 text-[18px]">chevron_right</span>
          </router-link>
        </div>
      </div>

      <div class="px-6 mb-6 space-y-4">
        <h4 class="text-[12px] font-bold text-white/40 uppercase tracking-[0.2em]">Liên hệ</h4>
        <a :href="settings?.['social.ggmap'] || 'https://www.google.com/maps'" target="_blank" rel="noopener noreferrer"
          class="flex items-start gap-3 group">
          <span class="material-symbols-outlined text-white/50 text-[20px] mt-0.5 shrink-0 group-hover:text-white transition-colors">location_on</span>
          <span class="text-white/75 text-[15px] leading-snug group-hover:text-white transition-colors">{{ settings?.['contact.address'] || '416A Đường CC2, P. Sơn Kỳ, Q. Tân Phú, TP.HCM' }}</span>
        </a>
        <div class="flex items-start gap-3">
          <span class="material-symbols-outlined text-white/50 text-[20px] mt-0.5 shrink-0">call</span>
          <div class="flex flex-col gap-1.5">
            <a v-for="phone in phones" :key="phone.href" :href="phone.href"
              class="text-white/75 text-[15px] font-medium tabular-nums hover:text-white transition-colors">{{ phone.display }}</a>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-white/50 text-[20px] shrink-0">mail</span>
          <span class="text-white/75 text-[15px]">{{ settings?.['contact.email'] || 'chonthanhco@gmail.com' }}</span>
        </div>
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-white/50 text-[20px] shrink-0">schedule</span>
          <span class="text-white/75 text-[15px]">{{ settings?.['contact.working_hours'] || 'T2 – T6: 8:30 AM – 5:30 PM' }}</span>
        </div>
      </div>

      <div class="px-6 pt-5 border-t border-white/15">
        <div class="flex justify-center gap-3 mb-3">
          <router-link to="/privacy-policy" class="text-white/50 text-[12px] hover:text-white transition-colors">Chính sách bảo mật</router-link>
          <span class="text-white/30">|</span>
          <router-link to="/terms-of-service" class="text-white/50 text-[12px] hover:text-white transition-colors">Điều khoản sử dụng</router-link>
        </div>
        <p class="text-white/40 text-[12px] text-center leading-relaxed">
          &copy; {{ new Date().getFullYear() }} CHƠN THÀNH Geosynthetics
        </p>
      </div>
    </div>

    <!-- Desktop bottom bar -->
    <div class="footer-bottom-desktop">
      <div class="footer-bottom-inner">
        <p class="footer-bottom-copy">&copy; {{ new Date().getFullYear() }} CHƠN THÀNH Geosynthetics. All rights reserved.</p>
        <div class="footer-bottom-links">
          <router-link to="/privacy-policy" class="footer-bottom-link">Chính sách bảo mật</router-link>
          <span class="footer-bottom-divider"></span>
          <router-link to="/terms-of-service" class="footer-bottom-link">Điều khoản sử dụng</router-link>
          <span class="footer-bottom-divider"></span>
          <span>ĐKKD 0303792837</span>
          <span class="footer-bottom-divider"></span>
          <span>ISO 9001:2015</span>
          <span class="footer-bottom-divider"></span>
          <span>TCVN 9844:2013</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* ═══ Footer Base ═══ */
.site-footer {
  width: 100%;
  margin-top: auto;
  position: relative;
  overflow: hidden;
  background-color: #B89B88;
}

.footer-accent {
  height: 2px;
  background: rgba(255, 255, 255, 0.3);
  width: 100%;
}

/* ═══ Desktop Footer ═══ */
.footer-desktop {
  display: none;
}

/* ═══ Mobile Footer ═══ */
.footer-mobile {
  display: block;
  position: relative;
  z-index: 10;
  padding-top: 2rem;
  padding-bottom: 1.5rem;
}

.footer-mobile-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1.75rem;
  padding-left: 1.5rem;
  padding-right: 1.5rem;
}

/* ═══ Desktop Bottom Bar ═══ */
.footer-bottom-desktop {
  display: none;
}

/* ═══ Responsive: Tablet (≥768px) ═══ */
@media (min-width: 768px) {
  .footer-desktop {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
    position: relative;
    z-index: 10;
    max-width: var(--spacing-max-width);
    margin: 0 auto;
    padding: 3.5rem var(--spacing-margin-desktop) 2.5rem;
  }

  .footer-mobile {
    display: none;
  }

  .footer-bottom-desktop {
    display: block;
    position: relative;
    z-index: 10;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
  }
}

/* ═══ Responsive: Desktop (≥1024px) ═══ */
@media (min-width: 1024px) {
  .footer-desktop {
    grid-template-columns: 4fr 2.5fr 3fr 3.5fr;
    gap: 2rem;
  }
}

/* ═══ Footer Brand ═══ */
.footer-brand {
  padding-right: 1rem;
}

.footer-logo-link {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.footer-logo-img {
  height: 3.5rem;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
}

.footer-logo-name {
  display: block;
  font-weight: 700;
  font-size: 24px;
  line-height: 1.2;
  color: #fff;
}

.footer-logo-sub {
  display: block;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.26em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.85);
}

.footer-desc {
  color: rgba(255, 255, 255, 0.9);
  font-size: 16px;
  line-height: 1.7;
  margin-bottom: 1.5rem;
}

.footer-badges {
  display: inline-flex;
  flex-wrap: wrap;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 0.5rem;
  padding: 0.375rem 0.5rem;
  margin-bottom: 1.75rem;
  gap: 0.25rem;
}

.footer-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.75rem;
  font-size: 13px;
  font-weight: 700;
  color: #fff;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.footer-badge-divider {
  width: 1px;
  height: 1.5rem;
  background: rgba(255, 255, 255, 0.3);
  margin: 0 0.25rem;
}

/* ═══ Footer Links Columns ═══ */
.footer-links-col {
  /* columns */
}

.footer-heading {
  font-size: 14px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.95);
  text-transform: uppercase;
  letter-spacing: 0.22em;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.footer-nav {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.footer-nav-link {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  color: rgba(255, 255, 255, 0.8);
  font-size: 16px;
  font-weight: 500;
  transition: color 0.2s ease;
  text-decoration: none;
}

.footer-nav-link:hover {
  color: #fff;
}

.footer-dot {
  width: 4px;
  height: 4px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.5);
  flex-shrink: 0;
  transition: background 0.2s ease;
}

.footer-nav-link:hover .footer-dot {
  background: #fff;
}

/* ═══ Footer Contact ═══ */
.footer-contact {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.footer-contact-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  text-decoration: none;
  color: inherit;
  transition: color 0.2s ease;
}

a.footer-contact-item:hover {
  color: #fff;
}

.footer-contact-icon {
  color: rgba(255, 255, 255, 0.9);
  font-size: 22px;
  margin-top: 2px;
  flex-shrink: 0;
}

.footer-contact-text {
  color: rgba(255, 255, 255, 0.8);
  font-size: 16px;
  line-height: 1.6;
}

.footer-phone {
  color: rgba(255, 255, 255, 0.8);
  font-size: 16px;
  transition: color 0.2s ease;
  display: block;
  font-variant-numeric: tabular-nums;
  text-decoration: none;
}

.footer-phone:hover {
  color: #fff;
}

/* ═══ Desktop Bottom Bar ═══ */
.footer-bottom-inner {
  max-width: var(--spacing-max-width);
  margin: 0 auto;
  padding: 1rem var(--spacing-margin-desktop);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.footer-bottom-copy {
  color: rgba(255, 255, 255, 0.75);
  font-size: 15px;
  margin: 0;
  flex-shrink: 0;
  white-space: nowrap;
}

.footer-bottom-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 15px;
  color: rgba(255, 255, 255, 0.5);
}

.footer-bottom-links {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.5);
  flex-wrap: nowrap;
  white-space: nowrap;
}

.footer-bottom-link {
  color: rgba(255, 255, 255, 0.5);
  text-decoration: none;
  transition: color 0.2s ease;
  white-space: nowrap;
}

.footer-bottom-link:hover {
  color: #fff;
}

.footer-bottom-divider {
  width: 1px;
  height: 0.625rem;
  background: rgba(255, 255, 255, 0.2);
  flex-shrink: 0;
}
</style>
