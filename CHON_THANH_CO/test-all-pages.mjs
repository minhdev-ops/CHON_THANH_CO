import { chromium } from 'playwright';

const PAGES = [
  { url: '/about', name: 'about' },
  { url: '/products', name: 'products' },
  { url: '/products/vai-kt-khong-det-art-12', name: 'product-detail' },
  { url: '/projects', name: 'projects' },
  { url: '/projects/cao-toc-bac-bac-quang-nam', name: 'project-detail' },
  { url: '/certificates', name: 'certificates' },
  { url: '/contact', name: 'contact' },
  { url: '/news', name: 'news' },
  { url: '/news/chon-thanh-dat-doi-tac-hock', name: 'news-detail' },
  { url: '/faq', name: 'faq' },
  { url: '/about/capability', name: 'capability' },
  { url: '/about/certification', name: 'certification' },
  { url: '/non-existent-page', name: 'not-found' },
];

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();

const errors = [];
page.on('pageerror', e => errors.push(`PAGEERROR: ${e.message}`));
page.on('console', m => { if (m.type() === 'error') errors.push(`CONSOLE: ${m.text()}`); });

for (const p of PAGES) {
  errors.length = 0;
  await page.goto(`http://127.0.0.1:8000${p.url}`, { waitUntil: 'networkidle', timeout: 15000 }).catch(e => errors.push(`NAV: ${e.message}`));
  await page.waitForTimeout(1500);
  const title = await page.title();
  const h1 = await page.locator('h1').first().textContent().catch(() => '(no h1)');
  const bodyText = (await page.locator('body').textContent() || '').slice(0, 200).replace(/\s+/g, ' ').trim();
  console.log(`\n=== ${p.name.toUpperCase()} (${p.url}) ===`);
  console.log(`Title: ${title}`);
  console.log(`H1: ${h1?.slice(0, 80)}`);
  console.log(`Body[0-200]: ${bodyText}`);
  if (errors.length) {
    console.log(`ERRORS (${errors.length}):`);
    errors.slice(0, 3).forEach(e => console.log(`  - ${e.slice(0, 200)}`));
  }
  await page.screenshot({ path: `/tmp/test-${p.name}.png`, fullPage: false });
}

await browser.close();
