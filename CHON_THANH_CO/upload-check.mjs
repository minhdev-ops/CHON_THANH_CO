// @ts-check
import { chromium } from 'playwright'
import { writeFileSync } from 'node:fs'

const BASE = 'http://127.0.0.1:8000'
const browser = await chromium.launch()

const page = await browser.newPage()
const logs = []
page.on('console', (m) => { if (m.type() === 'error') logs.push('CONSOLE: ' + m.text()) })
page.on('pageerror', (e) => logs.push('PAGEERROR: ' + e.message + '\n   ' + (e.stack || '').split('\n').slice(0, 4).join('\n   ')))
page.on('requestfailed', (r) => logs.push('REQFAIL: ' + r.url() + ' ' + r.failure()?.errorText))
page.on('response', (r) => { if (r.status() >= 400 && !r.url().includes('favicon')) logs.push(`HTTP ${r.status()} ${r.request().method()} ${r.url()}`) })

// 1. login
await page.goto(BASE + '/admin/login', { waitUntil: 'networkidle' })
await page.fill('input[name="username"]', 'admin')
await page.fill('input[name="password"]', 'admin12345')
await page.click('button[type="submit"]')
await page.waitForTimeout(2500)
console.log('login ok ->', page.url())

// 2. open file manager
await page.goto(BASE + '/admin/files', { waitUntil: 'networkidle' })
await page.waitForTimeout(1200)
console.log('tab type =', await page.locator('button').filter({ hasText: 'Tài liệu' }).getAttribute('class'))

// 3. upload a file via UI
await page.setInputFiles('input[type="file"]', { name: 'auto-test.txt', mimeType: 'text/plain', buffer: Buffer.from('hello autotest') })
await page.waitForTimeout(2500)

// 4. check notice + list
const body = await page.locator('body').innerText()
const noticeLine = body.split('\n').find((l) => l.includes('tải') || l.includes('thành công') || l.includes('lỗi'))
console.log('NOTICE:', noticeLine?.trim())
console.log('FILE IN LIST:', body.includes('auto-test.txt'))
console.log('FILES SECTION HAS ITEM:', await page.locator('xpath=//*[contains(text(),"auto-test.txt")]').count())

// screenshot
await page.screenshot({ path: '/tmp/opencode/fm-after-upload.png', fullPage: true })
console.log('--- diagnostics ---')
console.log(logs.join('\n') || '(không có lỗi)')
await browser.close()