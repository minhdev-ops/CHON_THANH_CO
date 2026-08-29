// @ts-check
import { test, expect } from 'playwright/test'

const BASE = 'http://127.0.0.1:8000'
const ADMIN_PATH = process.env.ADMIN_PATH || 'admin'
const ADMIN_USERNAME = process.env.ADMIN_USERNAME || 'admin'
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'admin12345'

/** Tạo mã sản phẩm duy nhất để test chạy lại nhiều lần không đụng dữ liệu cũ. */
const uniqueCode = () => `E2E-${Date.now().toString(36).toUpperCase()}`

async function login(page) {
  await page.goto(`/${ADMIN_PATH}/login`, { waitUntil: 'networkidle' })
  await page.locator('input[name="username"]').fill(ADMIN_USERNAME)
  await page.locator('input[name="password"]').fill(ADMIN_PASSWORD)
  await page.locator('button[type="submit"]').click()
  await page.waitForURL(`**/${ADMIN_PATH}`, { timeout: 10000 })
}

test.describe('Luồng admin: đăng nhập → tạo sản phẩm → hiển thị public', () => {
  /** Mã sản phẩm tạo ra trong test — dùng để cleanup sau test (kể cả khi fail). */
  let createdCode = null

  test.afterEach(async ({ page }) => {
    if (!createdCode) return

    // Dọn dẹp qua admin: đăng nhập (nếu chưa), tìm và xóa sản phẩm
    try {
      await login(page)
      await page.goto(`/${ADMIN_PATH}/products?q=${encodeURIComponent(createdCode)}`, { waitUntil: 'networkidle' })
      const row = page.locator('tr', { hasText: createdCode })
      if ((await row.count()) > 0) {
        page.once('dialog', (dialog) => dialog.accept())
        await row.locator('form button[type="submit"]', { hasText: 'Xóa' }).click()
        await page.waitForTimeout(800)
      }
    } catch {
      // Không ném lỗi khi cleanup — test đã pass/fail theo kết quả chính
    } finally {
      createdCode = null
    }
  })

  test('từ chối truy cập khi chưa đăng nhập', async ({ page }) => {
    await page.goto(`/${ADMIN_PATH}/products`, { waitUntil: 'networkidle' })
    await page.waitForURL(`**/${ADMIN_PATH}/login`, { timeout: 10000 })
    expect(page.url()).toContain(`/${ADMIN_PATH}/login`)
  })

  test('đăng nhập với sai mật khẩu hiển thị lỗi', async ({ page }) => {
    await page.goto(`/${ADMIN_PATH}/login`, { waitUntil: 'networkidle' })
    await page.locator('input[name="username"]').fill(ADMIN_USERNAME)
    await page.locator('input[name="password"]').fill('sai-mat-khau-123')
    await page.locator('button[type="submit"]').click()
    await page.waitForTimeout(600)
    const text = await page.locator('body').innerText()
    expect(text, 'hiển thị thông báo sai thông tin đăng nhập').toContain('Thông tin đăng nhập không chính xác')
  })

  test('đăng nhập → tạo sản phẩm → kiểm tra hiển thị trên trang public', async ({ page }) => {
    // 1. Đăng nhập
    await login(page)

    // 2. Vào form tạo sản phẩm
    await page.goto(`/${ADMIN_PATH}/products/create`, { waitUntil: 'networkidle' })

    const code = uniqueCode()
    createdCode = code
    const nameVi = `Vải E2E kiểm thử ${code}`

    // Điền form
    await page.locator('input[name="code"]').fill(code)
    await page.locator('input[name="image"]').fill('/images/products/geotextile-roll.jpg')
    await page.locator('select[name="category_id"]').selectOption({ index: 1 }) // chọn danh mục đầu tiên
    await page.locator('input[name="translations[vi][name]"]').fill(nameVi)
    await page.locator('textarea[name="translations[vi][description]"]').fill('Sản phẩm được tạo tự động bởi test E2E Playwright để kiểm tra luồng quản trị.')
    await page.locator('input[name="is_active"]').check({ force: true })

    // Submit form (chọn form tạo sản phẩm — phân biệt với nút logout trong sidebar)
    await page.locator('form:has(input[name="code"]) button[type="submit"]').click()
    await page.waitForURL(`**/${ADMIN_PATH}/products`, { timeout: 10000 })

    // Xác nhận flash success
    const bodyAfterCreate = await page.locator('body').innerText()
    expect(bodyAfterCreate, 'có thông báo đã tạo sản phẩm').toContain('Đã tạo sản phẩm')

    // 3. Kiểm tra sản phẩm xuất hiện trên trang public /products
    await page.goto('/products', { waitUntil: 'networkidle' })
    await page.waitForTimeout(1000)
    const publicText = await page.locator('#app').innerText()
    expect(publicText, 'sản phẩm mới xuất hiện trên trang sản phẩm public').toContain(nameVi)

    // Lấy slug thật từ link của product card (slug được tạo từ tên tiếng Việt)
    const card = page.locator(`a[href^="/products/"]:has-text("${nameVi}")`).first()
    const href = await card.getAttribute('href')
    expect(href, 'product card có link chi tiết').toMatch(/^\/products\/.+/)

    // 4. Kiểm tra trang chi tiết sản phẩm public
    await page.goto(href, { waitUntil: 'networkidle' })
    await page.waitForTimeout(800)
    const detailText = await page.locator('#app').innerText()
    expect(detailText, 'trang chi tiết public hiển thị mã sản phẩm').toContain(code)
  })
})
