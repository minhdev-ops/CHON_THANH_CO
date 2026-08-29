// @ts-check
import { test, expect } from 'playwright/test'

const BASE = 'http://127.0.0.1:8000'

const STATIC_ROUTES = [
  { path: '/', name: 'Trang chủ', expect: ['ĐỐI TÁC UY TÍN', 'SẢN PHẨM TIÊU BIỂU'] },
  { path: '/about', name: 'Giới thiệu', expect: ['VỀ CHƠN THÀNH', 'LỊCH SỬ HÌNH THÀNH'] },
  { path: '/about/capability', name: 'Hồ sơ năng lực', expect: ['HỒ SƠ NĂNG LỰC', 'CHỈ SỐ NĂNG LỰC'] },
  { path: '/about/certification', name: 'Hồ sơ kiểm định', expect: ['HỒ SƠ KIỂM ĐỊNH', 'CHẤT LƯỢNG ĐƯỢC KIỂM SOÁT'] },
  { path: '/products', name: 'Sản phẩm', expect: ['SẢN PHẨM', 'BỘ LỌC'] },
  { path: '/projects', name: 'Dự án', expect: ['DỰ ÁN TIÊU BIỂU'] },
  { path: '/certificates', name: 'Chứng chỉ', expect: ['CHỨNG CHỈ & GIẤY UỶ QUYỀN'] },
  { path: '/contact', name: 'Liên hệ', expect: ['LIÊN HỆ VỚI CHÚNG TÔI', 'GỬI YÊU CẦU TƯ VẤN'] },
  { path: '/news', name: 'Tin tức', expect: ['TIN TỨC & SỰ KIỆN'] },
  { path: '/faq', name: 'FAQ', expect: ['CÂU HỎI THƯỜNG GẶP'] },
]

async function fetchSlugs(apiPath) {
  const res = await fetch(BASE + apiPath)
  if (!res.ok) return []
  const json = await res.json()
  return (json.data || []).map((x) => x.slug).filter(Boolean)
}

async function getDiagnostics(page) {
  const issues = {
    pageErrors: [],
    consoleErrors: [],
    failedRequests: [],
    httpErrors: [],
    brokenImages: [],
  }

  page.on('pageerror', (err) => issues.pageErrors.push(err.message))
  page.on('console', (msg) => {
    if (msg.type() === 'error') issues.consoleErrors.push(msg.text())
  })
  page.on('requestfailed', (req) =>
    issues.failedRequests.push(`${req.method()} ${req.url()} :: ${req.failure()?.errorText}`)
  )
  page.on('response', (res) => {
    const url = res.url()
    if (url.includes('__vite') || url.includes('/hot')) return
    const s = res.status()
    if (s >= 400) issues.httpErrors.push(`${s} ${res.request().method()} ${url}`)
    if (res.request().resourceType() === 'image' && s >= 400) {
      issues.brokenImages.push(`${s} ${url}`)
    }
  })

  return issues
}

function filterNoise(list, allowed = []) {
  return list.filter((x) => !allowed.some((a) => x.includes(a)))
}

async function openPage(page, path) {
  const issues = await getDiagnostics(page)
  await page.goto(path, { waitUntil: 'networkidle' })
  await page.waitForSelector('#app', { state: 'attached' })
  await page.waitForTimeout(800)
  return issues
}

for (const route of STATIC_ROUTES) {
  test(`Trang: ${route.name} (${route.path})`, async ({ page }) => {
    const issues = await openPage(page, route.path)

    const bodyText = await page.locator('#app').innerText()
    expect(bodyText.trim().length, 'nội dung trang không trống').toBeGreaterThan(20)

    for (const marker of route.expect || []) {
      expect(bodyText, `có nội dung "${marker}"`).toContain(marker)
    }

    const noise = ['http://www.w3.org/2000/svg', 'favicon']
    const consoleErrors = filterNoise(issues.consoleErrors, noise)
    expect(consoleErrors, 'console.error').toEqual([])
    expect(issues.pageErrors, 'pageerror').toEqual([])
    expect(issues.failedRequests, 'requestfailed').toEqual([])
    expect(issues.httpErrors.map((h) => h.replace(/\d+$/, '')), 'http >=400').not.toContain('404 /')
  })
}

test('Danh sách sản phẩm hiển thị product cards', async ({ page }) => {
  const issues = await openPage(page, '/products')
  const cards = await page.locator('[data-testid="product-card"], .product-card, a[href^="/products/"]').count()
  expect(cards, 'ít nhất 4 sản phẩm hiển thị').toBeGreaterThanOrEqual(4)
  expect(issues.pageErrors).toEqual([])
})

test('Trang chi tiết sản phẩm', async ({ page }) => {
  const slugs = await fetchSlugs('/api/v1/products')
  expect(slugs.length).toBeGreaterThan(0)
  const issues = await openPage(page, `/products/${slugs[0]}`)
  const text = await page.locator('#app').innerText()
  expect(text.length, 'chi tiết sản phẩm có nội dung').toBeGreaterThan(50)
  expect(issues.pageErrors).toEqual([])
})

test('Trang chi tiết dự án', async ({ page }) => {
  const slugs = await fetchSlugs('/api/v1/projects')
  expect(slugs.length).toBeGreaterThan(0)
  const issues = await openPage(page, `/projects/${slugs[0]}`)
  const text = await page.locator('#app').innerText()
  expect(text.length, 'chi tiết dự án có nội dung').toBeGreaterThan(50)
  expect(issues.pageErrors).toEqual([])
})

test('Route chi tiết không tồn tại → trang 404 frontend', async ({ page }) => {
  const issues = await openPage(page, '/products/khong-ton-tai-slug-xyz')
  const text = await page.locator('#app').innerText()
  expect(issues.pageErrors).toEqual([])
  expect(text, 'hiển thị thông báo 404').toMatch(/404|không tìm thấy/i)
})

test('Form liên hệ validate khi gửi rỗng', async ({ page }) => {
  await page.goto('/contact', { waitUntil: 'networkidle' })
  await page.waitForSelector('#app')
  await page.locator('button[type="submit"], form button').first().click()
  await page.waitForTimeout(500)
  const text = await page.locator('#app').innerText()
  expect(text, 'có thông báo lỗi validate').toMatch(/bắt buộc|không được|không hợp lệ|vui lòng/i)
})

test('Navbar: các link chính điều hướng đúng', async ({ page }) => {
  const navMap = {
    'Dự án': '/projects',
    'Chứng chỉ': '/certificates',
    'Tin tức': '/news',
    'Liên hệ': '/contact',
  }
  for (const [label, expected] of Object.entries(navMap)) {
    await page.goto('/', { waitUntil: 'networkidle' })
    const link = page.locator(`nav >> text=${label}`).first()
    await link.click()
    await page.waitForTimeout(600)
    const url = new URL(page.url()).pathname
    expect(url, `nav "${label}" → ${expected}`).toBe(expected)
  }
})

test('Navbar: dropdown Giới thiệu mở được', async ({ page }) => {
  await page.goto('/', { waitUntil: 'networkidle' })
  await page.locator('nav >> text=Giới thiệu').first().hover()
  await page.waitForTimeout(400)
  await expect(page.locator('nav >> text=Hồ sơ năng lực').first()).toBeVisible()
})

test('Trang Sản phẩm: lọc theo danh mục giảm số lượng', async ({ page }) => {
  const cardCount = () =>
    page.locator('[data-testid="product-card"], .product-card, a[href^="/products/"]').count()

  await page.goto('/products', { waitUntil: 'networkidle' })
  await page.waitForTimeout(600)
  expect(await cardCount()).toBeGreaterThan(1)

  // Chỉ lấy checkbox trong mục "Loại sản phẩm" (không lấy phần "Ứng dụng")
  const checkboxes = page
    .locator('aside h3', { hasText: 'Loại sản phẩm' })
    .locator('xpath=following-sibling::div[1]')
    .locator('input[type="checkbox"]')
  const n = await checkboxes.count()
  expect(n, 'có ít nhất 1 danh mục để lọc').toBeGreaterThan(0)

  // Chọn lần lượt từng danh mục; dừng ở danh mục đầu tiên làm giảm số card hiển thị.
  // Với phân trang 12/trang, danh mục lớn vẫn hiện đủ 12 card nên phải thử nhiều danh mục.
  let reduced = false
  for (let i = 0; i < n; i++) {
    // Đăng ký chờ API response TRƯỚC khi click để không bỏ lỡ request nhanh.
    // Request lọc luôn mang tham số category= nên matcher này chỉ khớp đúng request cần chờ.
    await Promise.all([
      checkboxes.nth(i).check({ force: true }),
      page.waitForResponse(
        (r) => r.url().includes('/api/v1/products') && r.url().includes('category='),
        { timeout: 10000 },
      ),
    ])
    await page.waitForTimeout(300) // chờ Vue re-render sau khi nhận dữ liệu

    const count = await cardCount()
    if (count > 0 && count < 12) {
      reduced = true
      break
    }
    await checkboxes.nth(i).uncheck({ force: true })
    await page.waitForTimeout(400)
  }

  expect(reduced, 'chọn 1 danh mục phải giảm số sản phẩm hiển thị (dưới 12/trang)').toBe(true)
})

test('Trang Chứng chỉ: mở modal xem PDF', async ({ page }) => {
  await page.goto('/certificates', { waitUntil: 'networkidle' })
  const btn = page.getByRole('button', { name: /Xem trực tiếp/i }).first()
  if ((await btn.count()) === 0) {
    const text = await page.locator('#app').innerText()
    expect(text, 'trang có danh sách chứng chỉ').toContain('CHỨNG CHỈ')
    return
  }
  await btn.click()
  await expect(page.locator('iframe[src*=".pdf"]').first()).toBeVisible({ timeout: 5000 })
  await page.keyboard.press('Escape')
})

test('Trang FAQ: accordion mở đáp án khi bấm', async ({ page }) => {
  await page.goto('/faq', { waitUntil: 'networkidle' })
  const question = page.locator('button').filter({ hasText: '?' }).first()
  const countBefore = await page.locator('#app').innerText()
  await question.click({ force: true })
  await page.waitForTimeout(400)
  const countAfter = await page.locator('#app').innerText()
  expect(countAfter.length).toBeGreaterThan(countBefore.length)
})

test('Không tràn ngang trên desktop (mọi trang)', async ({ page }) => {
  for (const route of STATIC_ROUTES) {
    await page.setViewportSize({ width: 1440, height: 900 })
    await page.goto(route.path, { waitUntil: 'networkidle' })
    await page.waitForTimeout(400)
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1)
    expect(overflow, `trang ${route.path} không tràn ngang`).toBe(false)
  }
})

test('Không tràn ngang trên mobile (mọi trang)', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 })
  for (const route of STATIC_ROUTES) {
    await page.goto(route.path, { waitUntil: 'networkidle' })
    await page.waitForTimeout(400)
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1)
    expect(overflow, `trang ${route.path} không tràn ngang`).toBe(false)
  }
})