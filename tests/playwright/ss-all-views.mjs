import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function shot(page, path) {
  await page.screenshot({ path, fullPage: true });
  console.log('Saved:', path);
}

async function run() {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1400, height: 900 } });
  const page = await context.newPage();

  await page.goto(`${BASE}/login`, { timeout: 15000, waitUntil: 'networkidle' });
  await page.fill('#data\\.email', 'arafat.122260@gmail.com');
  await page.fill('#data\\.password', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(3000);

  // Kanban
  await page.goto(`${BASE}/tasks`, { timeout: 15000, waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  await shot(page, '/home/hermes/board-view.png');

  // Table
  await page.click('button.view-toggle');
  await page.waitForTimeout(1000);
  await shot(page, '/home/hermes/table-view.png');

  // Card view
  await page.click('button.view-toggle');
  await page.waitForTimeout(1000);
  await shot(page, '/home/hermes/card-view.png');

  await browser.close();
}
run().catch(e => { console.error('Error:', e.message); process.exit(1); });
