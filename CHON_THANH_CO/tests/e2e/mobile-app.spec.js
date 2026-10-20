// @ts-check
import { test, expect } from 'playwright/test'

const BASE = 'http://127.0.0.1:8000'
const MOBILE = { width: 390, height: 844 }

const MOBILE_ROUTES = ['/', '/about', '/products', '/projects', '/news', '/contact', '/certificates', '/faq']

async function firstProductSlug() {
  const res = await fetch(BASE + '/api/v1/products')
  if (!res.ok) return null
  const json = await res.json()
  return (json.data || [])[0]?.slug ?? null
}

async function openMobile(page, path) {
  await page.setViewportSize(MOBILE)
  await page.goto(path, { waitUntil: 'networkidle' })
  await page.waitForTimeout(1200)
}

test.describe('Mobile app shell', () => {
  test('thanh điều hướng dưới hiển thị và chuyển trang', async ({ page }) => {
    await openMobile(page, '/')

    const tabbar = page.locator('.mobile-tabbar-ui')
    await expect(tabbar).toBeVisible()

    // 5 mục: 4 điều hướng + Liên hệ
    const items = tabbar.locator('a, button')
    await expect(items).toHaveCount(5)
    await expect(tabbar).toContainText('Trang chủ')
    await expect(tabbar).toContainText('Sản phẩm')
    await expect(tabbar).toContainText('Liên hệ')

    await tabbar.getByText('Sản phẩm').click()
    await page.waitForTimeout(700)
    expect(new URL(page.url()).pathname).toBe('/products')
    await expect(tabbar.locator('a').nth(1)).toHaveAttribute('aria-current', 'page')
  })

  test('nút Liên hệ mở bottom sheet với các kênh liên lạc', async ({ page }) => {
    await openMobile(page, '/')

    const sheet = page.getByRole('dialog', { name: 'Liên hệ nhanh' })
    await expect(sheet).toBeHidden()

    await page.locator('.mobile-tabbar-ui').getByText('Liên hệ').click()
    await expect(sheet).toBeVisible()

    for (const label of ['Hotline tư vấn', 'Zalo', 'WhatsApp', 'Bản đồ']) {
      await expect(sheet.getByText(label, { exact: false }).first()).toBeVisible()
    }
    await expect(sheet).toContainText('Yêu cầu báo giá')

    await sheet.getByRole('button', { name: 'Đóng' }).click()
    await expect(sheet).toBeHidden()
    // Không được khoá scroll sau khi đóng
    const overflow = await page.evaluate(() => document.body.style.overflow)
    expect(overflow).toBe('')
  })

  test('trang chi tiết sản phẩm dùng thanh CTA riêng, ẩn tab bar', async ({ page }) => {
    const slug = await firstProductSlug()
    test.skip(!slug, 'không có sản phẩm để kiểm tra')

    await openMobile(page, `/products/${slug}`)
    await expect(page.locator('.mobile-tabbar-ui')).toBeHidden()

    // Thanh CTA cố định riêng của trang chi tiết sản phẩm
    const stickyBar = page.getByTestId('product-mobile-cta')
    await expect(stickyBar).toBeVisible()

    const callBtn = stickyBar.getByRole('link', { name: /Gọi tư vấn/i })
    await expect(callBtn).toBeVisible()
    await expect(callBtn).toHaveAttribute('href', /^tel:/)
    await expect(stickyBar.getByRole('link', { name: /BÁO GIÁ/i })).toBeVisible()

    const barHeight = await stickyBar.evaluate((el) => el.getBoundingClientRect().height)
    expect(barHeight, 'thanh CTA đủ lớn để bấm').toBeGreaterThanOrEqual(60)
  })

  test('header mobile gọn, không chiếm quá nửa màn hình', async ({ page }) => {
    await openMobile(page, '/products')

    const metrics = await page.evaluate(() => {
      const header = document.querySelector('header')
      const hero = document.querySelector('section.hero-parallax')
      return {
        header: header ? header.getBoundingClientRect().height : 0,
        hero: hero ? hero.getBoundingClientRect().height : 0,
        title: parseFloat(getComputedStyle(document.querySelector('section.hero-parallax h1')).fontSize),
      }
    })

    expect(metrics.header, 'app bar ≤ 64px').toBeLessThanOrEqual(64)
    expect(metrics.hero, 'page header ≤ 260px').toBeLessThanOrEqual(260)
    expect(metrics.title, 'tiêu đề trang đọc được trên mobile').toBeGreaterThanOrEqual(20)
  })

  test('mọi trang mobile không tràn ngang và chữ đủ lớn', async ({ page }) => {
    for (const route of MOBILE_ROUTES) {
      await page.setViewportSize(MOBILE)
      await page.goto(route, { waitUntil: 'networkidle' })
      await page.waitForTimeout(800)

      const result = await page.evaluate(() => {
        const vw = document.documentElement.clientWidth
        const tooTiny = []
        document.querySelectorAll('body p, body li, body span, body a, body div').forEach((el) => {
          if (el.children.length) return
          if (!(el.textContent || '').trim()) return
          if (el.getBoundingClientRect().height === 0) return
          const fs = parseFloat(getComputedStyle(el).fontSize)
          if (fs < 10) tooTiny.push(`${fs}px: ${el.textContent.trim().slice(0, 24)}`)
        })
        return {
          overflow: document.documentElement.scrollWidth - vw,
          tooTiny,
        }
      })

      expect(result.overflow, `${route} không tràn ngang trên mobile`).toBeLessThanOrEqual(1)
      expect(result.tooTiny, `${route} không còn chữ < 10px`).toEqual([])
    }
  })

  test('tab bar và nút lọc đạt chuẩn vùng chạm 44px', async ({ page }) => {
    await openMobile(page, '/products')

    const sizes = await page.evaluate(() => {
      const rect = (el) => (el ? el.getBoundingClientRect() : null)
      const tabs = [...document.querySelectorAll('.mobile-tabbar-ui a, .mobile-tabbar-ui button')].map((el) => rect(el).height)
      const filterBtn = document.querySelector('main button[aria-label^="BỘ LỌC"]')
      const pager = [...document.querySelectorAll('nav[aria-label="Phân trang"] button')]
        .map((el) => rect(el).height)
        .filter((h) => h > 0)
      return { tabs, filter: filterBtn ? rect(filterBtn).height : null, pager }
    })

    for (const h of sizes.tabs) expect(h, 'tab bar item ≥ 44px').toBeGreaterThanOrEqual(44)
    expect(sizes.filter, 'nút lọc ≥ 44px').toBeGreaterThanOrEqual(44)
    expect(sizes.pager.length, 'có nút phân trang trên mobile').toBeGreaterThan(0)
    for (const h of sizes.pager) expect(h, 'nút phân trang ≥ 40px').toBeGreaterThanOrEqual(40)
  })

  test('footer mobile dạng accordion, gọn hơn danh sách dài', async ({ page }) => {
    await openMobile(page, '/')

    const footer = page.locator('footer')
    await footer.scrollIntoViewIfNeeded()
    await page.waitForTimeout(500)

    const height = await footer.evaluate((el) => el.getBoundingClientRect().height)
    expect(height, 'footer mobile ≤ 900px').toBeLessThanOrEqual(900)

    const content = page.locator('#footer-mobile-products')
    await expect(content).toBeHidden()
    await footer.getByRole('button', { name: 'Sản phẩm' }).click()
    await expect(content).toBeVisible()
  })

  test('thanh cố định dưới không che nội dung cuối trang', async ({ page }) => {
    const slug = await firstProductSlug()
    const routes = ['/', '/products', '/news', ...(slug ? [`/products/${slug}`] : [])]

    for (const route of routes) {
      await openMobile(page, route)
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight))
      await page.waitForTimeout(600)

      const result = await page.evaluate(() => {
        const footer = document.querySelector('.footer-mobile')
        const tabbar = document.querySelector('.mobile-tabbar-ui')
        const cta = document.querySelector('[data-testid="product-mobile-cta"]')
        const tabbarShown = tabbar && getComputedStyle(tabbar).display !== 'none'
        const barTop = tabbarShown ? tabbar.getBoundingClientRect().top : (cta ? cta.getBoundingClientRect().top : null)
        const visible = [...footer.querySelectorAll('*')].filter((el) => el.getBoundingClientRect().height > 0)
        const last = visible[visible.length - 1]
        return {
          barTop,
          lastBottom: last ? last.getBoundingClientRect().bottom : null,
        }
      })

      expect(result.barTop, `${route} có thanh cố định dưới`).not.toBeNull()
      expect(
        result.barTop - result.lastBottom,
        `${route} không bị thanh cố định che nội dung cuối footer`,
      ).toBeGreaterThan(0)
    }
  })

  test('drawer menu ẩn tab bar và khoá cuộn nền', async ({ page }) => {
    await openMobile(page, '/')

    await page.getByRole('button', { name: /mở menu/i }).click()
    await page.waitForTimeout(500)

    await expect(page.locator('.mobile-tabbar-ui')).toBeHidden()
    const locked = await page.evaluate(() => ({
      body: document.body.style.overflow,
      shell: document.documentElement.classList.contains('mobile-tabbar'),
    }))
    expect(locked.body).toBe('hidden')
    expect(locked.shell).toBe(false)
  })

  test('drawer bổ sung cho tab bar, không lặp lại cùng bộ mục', async ({ page }) => {
    await openMobile(page, '/')

    await page.getByRole('button', { name: /mở menu/i }).click()
    await page.waitForTimeout(500)

    const panel = page.locator('#mobile-nav-panel')
    await expect(panel).toBeVisible()

    // Các đích chính đã nằm ở tab bar dưới → drawer không lặp lại
    for (const href of ['/', '/products', '/projects', '/news', '/contact']) {
      await expect(
        panel.locator(`div.flex-1 a[href="${href}"]`),
        `drawer không lặp lại liên kết ${href} của tab bar`,
      ).toHaveCount(0)
    }

    // Nội dung bổ sung: nhóm giới thiệu + tài liệu + danh mục sản phẩm
    for (const label of ['Về Chơn Thành', 'Hồ sơ năng lực', 'Chứng chỉ', 'Hỏi đáp', 'Danh mục sản phẩm']) {
      await expect(panel.getByText(label, { exact: true }).first()).toBeVisible()
    }

    // Accordion danh mục mở ra danh sách ngành hàng, đóng lại được
    const toggle = panel.getByRole('button', { name: /Tất cả sản phẩm/ })
    await expect(toggle).toHaveAttribute('aria-expanded', 'false')
    await toggle.click()
    await expect(toggle).toHaveAttribute('aria-expanded', 'true')
    await expect(panel.locator('#mobile-category-list a').first()).toBeVisible()
  })

  test('desktop không bị ảnh hưởng bởi thay đổi mobile', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 })
    await page.goto('/products', { waitUntil: 'networkidle' })
    await page.waitForTimeout(1000)

    const desktop = await page.evaluate(() => {
      const g = (s) => {
        const el = document.querySelector(s)
        return el ? Math.round(el.getBoundingClientRect().height) : null
      }
      const tabbar = document.querySelector('.mobile-tabbar-ui')
      return {
        tabbarVisible: !!tabbar && getComputedStyle(tabbar).display !== 'none',
        header: g('header'),
        logo: g('header img'),
        hero: g('section.hero-parallax'),
        footer: g('footer'),
        floatingWidget: !!document.querySelector('.widget-btn'),
        shellPadding: getComputedStyle(document.querySelector('.app-shell')).paddingBottom,
      }
    })

    expect(desktop.tabbarVisible, 'tab bar không hiện trên desktop').toBe(false)
    expect(desktop.header, 'chiều cao header desktop giữ nguyên').toBe(202)
    expect(desktop.logo, 'logo desktop giữ nguyên').toBe(110)
    expect(desktop.hero, 'page header desktop giữ nguyên').toBe(502)
    expect(desktop.footer, 'footer desktop giữ nguyên').toBe(482)
    expect(desktop.floatingWidget, 'widget liên hệ desktop vẫn hiển thị').toBe(true)
    expect(desktop.shellPadding, 'desktop không có padding đáy cho tab bar').toBe('0px')
  })
})
