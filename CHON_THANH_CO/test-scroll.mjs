import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

  for (const [name, url] of [
    ['products', 'http://127.0.0.1:8000/products'],
    ['projects', 'http://127.0.0.1:8000/projects'],
    ['news', 'http://127.0.0.1:8000/news'],
  ]) {
    await page.goto(url, { waitUntil: 'networkidle', timeout: 20000 });
    await page.waitForTimeout(2000);
    // Scroll to mid-page
    await page.evaluate(() => window.scrollTo(0, 800));
    await page.waitForTimeout(1000);
    await page.screenshot({ path: `/tmp/scrolled-${name}.png` });
    console.log(`Saved /tmp/scrolled-${name}.png`);
  }

  await browser.close();
})();
