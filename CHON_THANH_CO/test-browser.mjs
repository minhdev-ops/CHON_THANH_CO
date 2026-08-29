import { chromium } from 'playwright';

const pages = [
  { url: 'http://127.0.0.1:8000/', name: 'Home' },
  { url: 'http://127.0.0.1:8000/products', name: 'Products' },
  { url: 'http://127.0.0.1:8000/projects', name: 'Projects' },
  { url: 'http://127.0.0.1:8000/news', name: 'News' },
  { url: 'http://127.0.0.1:8000/about', name: 'About' },
  { url: 'http://127.0.0.1:8000/faq', name: 'FAQ' },
  { url: 'http://127.0.0.1:8000/certificates', name: 'Certificates' },
  { url: 'http://127.0.0.1:8000/contact', name: 'Contact' },
];

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await context.newPage();

  const errors = [];
  const consoleErrors = [];
  page.on('console', msg => { if (msg.type() === 'error') consoleErrors.push(msg.text()); });
  page.on('pageerror', err => errors.push('PAGE: ' + err.message));
  page.on('response', resp => {
    if (resp.status() >= 400) errors.push(`HTTP ${resp.status()}: ${resp.url()}`);
  });

  for (const p of pages) {
    console.log(`\n═══ ${p.name} (${p.url}) ═══`);
    try {
      await page.goto(p.url, { waitUntil: 'networkidle', timeout: 20000 });
      await page.waitForTimeout(1500);

      // Get all text content
      const text = await page.locator('body').textContent();

      // Count common elements
      const productCards = await page.locator('a[href*="/products/"]').count();
      const projectCards = await page.locator('a[href*="/projects/"]').count();
      const newsCards = await page.locator('a[href*="/news/"]').count();
      const certCards = await page.locator('a[href*="/certificates"]').count();
      const faqItems = await page.locator('button').count();

      console.log(`  Text length: ${text.length} chars`);
      console.log(`  Product links: ${productCards}`);
      console.log(`  Project links: ${projectCards}`);
      console.log(`  News links: ${newsCards}`);
      console.log(`  Certificate links: ${certCards}`);
      console.log(`  Buttons: ${faqItems}`);

      // Check for specific keywords
      const checks = {
        'Vải địa kỹ thuật': text.includes('Vải địa kỹ thuật'),
        'CHƠN THÀNH': text.includes('CHƠN THÀNH'),
        'Cao tốc': text.includes('Cao tốc'),
        'Hồ chứa': text.includes('Hồ chứa'),
        'ISO 9001': text.includes('ISO 9001'),
        'Không tìm thấy': text.includes('Không tìm thấy'),
        'Đang tải': text.includes('Đang tải'),
        'error': text.toLowerCase().includes('error'),
      };
      for (const [k, v] of Object.entries(checks)) {
        console.log(`  "${k}": ${v ? '✓' : '✗'}`);
      }

      // Take screenshot
      await page.screenshot({ path: `/tmp/screenshot-${p.name.toLowerCase()}.png`, fullPage: false });
    } catch (e) {
      console.log(`  ERROR: ${e.message}`);
    }
  }

  console.log('\n═══ ERRORS ═══');
  if (errors.length === 0) console.log('  No HTTP/page errors');
  else errors.slice(0, 10).forEach(e => console.log('  ' + e));

  console.log('\n═══ CONSOLE ERRORS ═══');
  if (consoleErrors.length === 0) console.log('  No console errors');
  else consoleErrors.slice(0, 10).forEach(e => console.log('  ' + e));

  await browser.close();
})();
