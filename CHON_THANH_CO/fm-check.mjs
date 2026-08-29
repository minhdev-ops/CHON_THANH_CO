// @ts-check
import { chromium } from 'playwright'
const BASE = 'http://127.0.0.1:8000'
const browser = await chromium.launch()
const page = await browser.newPage()
const errors = []
page.on('pageerror', (e) => errors.push(e.message))

await page.goto(BASE + '/admin/login', { waitUntil: 'networkidle' })
await page.fill('input[name="username"]', 'admin')
await page.fill('input[name="password"]', 'admin12345')
await page.click('button[type="submit"]')
await page.waitForTimeout(2500)

await page.goto(BASE + '/admin/files', { waitUntil: 'networkidle' })
await page.waitForTimeout(1500)

console.log('body text length:', (await page.locator('body').innerText()).length)
console.log('--- file manager main text (first 600 chars) ---')
console.log((await page.locator('body').innerText()).slice(0, 600).replace(/\n{2,}/g, '\n'))

console.log('--- x-cloak elements still hidden? ---')
console.log('x-cloak present count:', await page.locator('[x-cloak]').count())

console.log('--- error count:', errors.length)
console.log(errors.slice(0, 6).join('\n'))
await page.screenshot({ path: '/tmp/opencode/fm-current.png', fullPage: false })
await browser.close()