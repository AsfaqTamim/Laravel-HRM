import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function run() {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1400, height: 900 } });
  const page = await context.newPage();

  page.on('console', msg => console.log('CONSOLE:', msg.type(), msg.text()));
  page.on('pageerror', err => console.log('PAGE_ERROR:', err.message));

  await page.goto(`${BASE}/login`, { timeout: 15000, waitUntil: 'networkidle' });
  await page.fill('#data\\.email', 'arafat.122260@gmail.com');
  await page.fill('#data\\.password', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(3000);

  await page.goto(`${BASE}/profile`, { timeout: 15000, waitUntil: 'networkidle' });
  await page.waitForTimeout(3000);

  // Get the page HTML for debugging
  const html = await page.evaluate(() => document.body.innerText.substring(0, 3000));
  console.log('--- PAGE TEXT ---');
  console.log(html);

  await browser.close();
}
run().catch(e => { console.error('Error:', e.message); process.exit(1); });
