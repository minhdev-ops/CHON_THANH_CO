import { chromium } from 'playwright'

const routes = [
  { path: '/products', name: 'products' },
  { path: '/projects', name: 'projects' },
  { path: '/news', name: 'news' }
]

const browser = await chromium.launch()
const context = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await context.newPage()

for (const { path, name } of routes) {
  console.log(`\n→ ${path}`)
  await page.goto(`http://127.0.0.1:8000${path}`, { waitUntil: 'networkidle' })
  await page.waitForTimeout(1200)
  const h1 = await page.locator('h1').first().textContent()
  console.log(`  H1: ${h1?.trim().slice(0, 80)}`)
  await page.screenshot({ path: `/tmp/r2-${name}.png`, fullPage: false })
  await page.screenshot({ path: `/tmp/r2-${name}-full.png`, fullPage: true })
}

// Test products index view
await page.goto('http://127.0.0.1:8000/products', { waitUntil: 'networkidle' })
await page.waitForTimeout(800)
const listBtn = page.locator('button[title="Danh sách"], button[title="List"]').first()
if (await listBtn.count() > 0) {
  await listBtn.click()
  await page.waitForTimeout(600)
  await page.screenshot({ path: '/tmp/r2-products-list.png', fullPage: true })
  console.log('\n→ products list view captured')
}

// Mobile test
await context.close()
const mobile = await browser.newContext({ viewport: { width: 390, height: 844 } })
const m = await mobile.newPage()
await m.goto('http://127.0.0.1:8000/products', { waitUntil: 'networkidle' })
await m.waitForTimeout(800)
await m.screenshot({ path: '/tmp/r2-products-mobile.png', fullPage: true })
console.log('\n→ products mobile captured')

await browser.close()
console.log('\nDone.')
