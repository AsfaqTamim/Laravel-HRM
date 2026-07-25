import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function run() {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const page = await browser.newPage({ viewport: { width: 1400, height: 900 } });

  await page.goto(`${BASE}/login`, { timeout: 10000 });
  await page.waitForLoadState('networkidle');

  await page.fill('#data\\.email', 'arafat.122260@gmail.com');
  await page.fill('#data\\.password', 'password');
  await page.click('button[type="submit"]');
  
  await page.waitForURL('**/dashboard', { timeout: 10000 });
  
  await page.goto(`${BASE}/tasks`);
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(1000);
  await page.screenshot({ path: '/home/hermes/tasks-redesign.png', fullPage: true });
  console.log('Screenshot saved');

  await browser.close();
}
run();