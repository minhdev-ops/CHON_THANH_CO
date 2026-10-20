// @ts-check
import { test, expect } from 'playwright/test'

const BASE = 'http://127.0.0.1:8000'

async function api(path) {
  const res = await fetch(BASE + path)
  if (!res.ok) return null
  return res.json()
}

async function openPage(page, path) {
  await page.goto(path, { waitUntil: 'networkidle' })
  await page.waitForSelector('#app', { state: 'attached' })
  await page.waitForTimeout(800)
}

async function collectErrors(page) {
  const issues = { pageErrors: [], httpErrors: [] }
  page.on('pageerror', (err) => issues.pageErrors.push(err.message))
  page.on('response', (res) => {
    const s = res.status()
    if (s >= 400) issues.httpErrors.push(`${s} ${res.request().method()} ${res.url()}`)
  })
  return issues
}

test('Trang tin tức liệt kê các bài viết', async ({ page }) => {
  const issues = await collectErrors(page)
  await openPage(page, '/news')
  const body = await page.locator('#app').innerText()
  expect(body.trim().length, 'có nội dung tin tức').toBeGreaterThan(20)
  expect(body).toContain('TIN TỨC')
  expect(issues.pageErrors).toEqual([])
})

test('Trang chi tiết tin tức hiển thị nội dung bài viết', async ({ page }) => {
  const json = await api('/api/v1/news')
  const slug = json?.data?.[0]?.slug
  test.skip(!slug, 'chưa có bài viết nào')

  const issues = await collectErrors(page)
  await openPage(page, `/news/${slug}`)
  const body = await page.locator('#app').innerText()
  expect(body.length, 'supports nội dung bài viết').toBeGreaterThan(50)
  expect(issues.pageErrors).toEqual([])
})

test('Route tin tức không tồn tại hiển thị thông báo', async ({ page }) => {
  const issues = await collectErrors(page)
  await openPage(page, '/news/slug-khong-ton-tai-xyz')
  const body = await page.locator('#app').innerText()
  expect(issues.pageErrors).toEqual([])
  expect(body).toMatch(/404|không tìm thấy|không tồn tại/i)
})

test('Form liên hệ gửi thành công khi hợp lệ', async ({ page }) => {
  const issues = await collectErrors(page)
  await page.goto('/contact', { waitUntil: 'networkidle' })
  await page.waitForSelector('#app')

  await page.fill('#name', 'Nguyễn Văn A')
  await page.fill('#phone', '0912345678')
  await page.fill('#email', 'nguyenvana@example.com')
  await page.fill('#message', 'Xin báo giá vải địa kỹ thuật.')

  await page.locator('button[type="submit"]').first().click()

  // Chờ API response POST /api/v1/contact
  await page.waitForResponse((r) => r.url().includes('/api/v1/contact') && r.request().method() === 'POST', { timeout: 10000 })

  await page.waitForTimeout(500)
  const body = await page.locator('#app').innerText()
  expect(body, 'hiển thị thông báo thành công').toMatch(/cảm ơn|thành công/i)
  expect(issues.pageErrors).toEqual([])
})

test('Menu mobile mở và đóng được', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 })
  await openPage(page, '/')

  // Hamburger trên app bar mobile (tab bar dưới cũng là <nav> nên phải chỉ đích danh)
  const toggle = page.locator('header button[aria-controls="mobile-nav-panel"]')
  await expect(toggle).toBeVisible()
  await toggle.click()
  await page.waitForTimeout(500)
  await expect(toggle).toHaveAttribute('aria-expanded', 'true')

  const panel = page.locator('#mobile-nav-panel')
  await expect(panel).toBeVisible()
  // Drawer chỉ chứa các mục bổ sung cho tab bar dưới (không lặp lại tab bar)
  await expect(panel.getByRole('link', { name: 'Hồ sơ năng lực' })).toBeVisible()
  await expect(panel.getByRole('link', { name: 'Chứng chỉ' })).toBeVisible()

  // Panel che mất hamburger nên đóng bằng cách chạm vùng nền (scrim)
  await page.getByTestId('mobile-nav-overlay').click({ position: { x: 10, y: 400 } })
  await page.waitForTimeout(500)
  await expect(toggle).toHaveAttribute('aria-expanded', 'false')
  await expect(panel).toBeHidden()
})

test('Mobile menu: điều hướng tới Hồ sơ năng lực', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 })
  await openPage(page, '/')

  const panel = page.locator('#mobile-nav-panel')
  await page.locator('header button[aria-controls="mobile-nav-panel"]').click()
  await page.waitForTimeout(500)
  await expect(panel).toBeVisible()

  // Drawer liệt kê sẵn nhóm "Về Chơn Thành" (không còn accordion trùng tab bar)
  const capability = panel.getByRole('link', { name: 'Hồ sơ năng lực' })
  await expect(capability).toBeVisible()
  await capability.click()
  await expect(page).toHaveURL(/about\/capability/, { timeout: 8000 })
})

test('Chuyển ngôn ngữ sang tiếng Anh trên trang chủ', async ({ page }) => {
  await openPage(page, '/')

  // Bộ chọn ngôn ngữ thực tế của site là dropdown trong thanh thông tin trên cùng
  // (nút có icon quả địa cầu).
  const langBtn = page.locator('header button:has(i.fa-globe)').first()
  await expect(langBtn).toBeVisible()
  await expect(langBtn).toContainText('Tiếng Việt')
  await langBtn.click()
  await page.waitForTimeout(300)

  await page.locator('header button').filter({ hasText: 'English' }).first().click()
  await page.waitForTimeout(800)

  await expect(langBtn).toContainText('English')
  await expect(page.locator('html')).toHaveAttribute('lang', 'en')
  await expect(page.locator('header nav, header').getByText('About', { exact: true }).first()).toBeVisible()
})

test('Bộ lọc sản phẩm theo query param category', async ({ page }) => {
  const json = await api('/api/v1/categories')
  const slug = json?.data?.[0]?.slug
  test.skip(!slug, 'chưa có danh mục nào')

  await page.goto(`/products?category=${slug}`, { waitUntil: 'networkidle' })
  await page.waitForSelector('#app', { state: 'attached' })
  await page.waitForResponse(
    (r) => r.url().includes('/api/v1/products') && r.url().includes('category='),
    { timeout: 10000 },
  ).catch(() => {})
  await page.waitForTimeout(800)

  const cards = page.locator('[data-testid="product-card"], .product-card, a[href^="/products/"]')
  await expect(cards.first()).toBeVisible({ timeout: 8000 })
  expect(await cards.count(), 'hiển thị sản phẩm khi lọc theo category').toBeGreaterThan(0)
})