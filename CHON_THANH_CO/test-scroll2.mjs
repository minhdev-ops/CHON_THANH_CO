import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

  await page.goto('http://127.0.0.1:8000/products', { waitUntil: 'networkidle', timeout: 20000 });
  await page.waitForTimeout(2000);

  // Take screenshots at different scroll positions
  for (const pos of [0, 600, 1500, 2500, 3500]) {
    await page.evaluate((y) => window.scrollTo(0, y), pos);
    await page.waitForTimeout(500);
    await page.screenshot({ path: `/tmp/products-scroll-${pos}.png` });
    console.log(`Saved /tmp/products-scroll-${pos}.png`);
  }

  // Check actual HTML at products grid
  const productSection = await page.locator('text=Tất cả').count();
  const productCount = await page.locator('a[href*="/products/"]').count();
  console.log('Product links count:', productCount);

  // Get text around products grid
  await page.evaluate(() => window.scrollTo(0, 1800));
  await page.waitForTimeout(500);
  const allText = await page.locator('body').textContent();
  const hasProducts = allText.includes('ART 12') || allText.includes('Vải địa kỹ thuật không dệt');
  console.log('Has product text after scroll:', hasProducts);

  await browser.close();
})();
