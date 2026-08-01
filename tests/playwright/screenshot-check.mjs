import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function run() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  await page.goto(`${BASE}/login`);
  await page.waitForTimeout(2000);

  await page.fill('#data\\.email', 'employee@example.com');
  await page.fill('#data\\.password', 'password');
  await page.click('button[type="submit"]');

  // Panel is at root path '' — after login we end up at /
  await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });
  await page.waitForTimeout(3000);

  // Screenshot at 1440px
  await page.screenshot({ path: 'screenshots/emp-dash-1440.png', fullPage: true });
  console.log('1440px saved');

  // At 1280px — still lg+
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.waitForTimeout(1000);
  await page.screenshot({ path: 'screenshots/emp-dash-1280.png', fullPage: true });
  console.log('1280px saved');

  // At 1024px — edge of lg
  await page.setViewportSize({ width: 1024, height: 900 });
  await page.waitForTimeout(1000);
  await page.screenshot({ path: 'screenshots/emp-dash-1024.png', fullPage: true });
  console.log('1024px saved');

  await browser.close();
}

run().catch(err => { console.error(err); process.exit(1); });
