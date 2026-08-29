const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({ args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1920, height: 1080 });
  await page.goto('http://localhost:8000', { waitUntil: 'networkidle0' });
  await page.screenshot({ path: 'navbar_screenshot.png' });
  await browser.close();
})();
