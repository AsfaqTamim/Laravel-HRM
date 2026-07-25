import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function sleep(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function run() {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[wire\\:model="data.email"]', 'arafat.122260@gmail.com');
  await page.fill('input[wire\\:model="data.password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(url => !url.pathname.includes('login'), { timeout: 10000 });
  await sleep(2000);

  // Take a full sidebar + main area screenshot
  await page.screenshot({ path: './screenshots/dashboard-full.png', fullPage: false });
  console.log('Dashboard screenshot saved');

  // Go to employees URL directly
  await page.goto(BASE + '/employees', { waitUntil: 'networkidle' });
  await sleep(2000);
  await page.screenshot({ path: './screenshots/employees-full.png', fullPage: false });
  console.log('Employees screenshot saved');

  await browser.close();
}

run().catch(console.error);
