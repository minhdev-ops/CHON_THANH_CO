// @ts-check
import { chromium } from 'playwright'
const BASE = 'http://127.0.0.1:8000'
const browser = await chromium.launch()
const page = await browser.newPage()
const logs = []
page.on('pageerror', (e) => logs.push('PAGEERROR: ' + e.message.split('\n')[0]))
page.on('response', async (r) => {
  if (r.url().includes('/files/browse') || r.url().includes('/files/upload')) {
    let body = ''
    try { body = await r.text() } catch {}
    console.log(`[NET ${r.status()}] ${r.request().method()} ${r.url().split('/admin/')[1]}`)
    if (r.url().includes('upload')) console.log('   upload body:', body.slice(0, 300))
    if (r.url().includes('browse') && r.status() === 200) {
      try { const j = JSON.parse(body); console.log('   folders:', j.folders?.map(f => f.name)); console.log('   files:', j.files?.map(f => f.name)) } catch {}
    }
  }
})

await page.goto(BASE + '/admin/login', { waitUntil: 'networkidle' })
await page.fill('input[name="username"]', 'admin')
await page.fill('input[name="password"]', 'admin12345')
await page.click('button[type="submit"]')
await page.waitForTimeout(2500)

await page.goto(BASE + '/admin/files', { waitUntil: 'networkidle' })
await page.waitForTimeout(1200)

// dump initial files
console.log('=== INITIAL browse ===')

// upload
console.log('=== uploading auto-test.txt ===')
await page.setInputFiles('input[type="file"]', { name: 'auto-test.txt', mimeType: 'text/plain', buffer: Buffer.from('hello autotest') })
await page.waitForTimeout(3000)

// dump body text around file area
const body = await page.locator('body').innerText()
console.log('=== body contains auto-test:', body.includes('auto-test'))
console.log('=== files section rendered (last 400 chars) ===')
console.log(body.slice(-400).replace(/\n{2,}/g, '\n'))
console.log('=== errors ===')
console.log(logs.join('\n') || 'none')
await browser.close()