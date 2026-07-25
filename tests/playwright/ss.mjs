import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function run() {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1400, height: 900 } });
  const page = await context.newPage();

  // Login
  await page.goto(`${BASE}/login`, { timeout: 15000, waitUntil: 'networkidle' });

  // Filament uses id="data.email" and id="data.password"
  await page.fill('#data\\.email', 'arafat.122260@gmail.com');
  await page.fill('#data\\.password', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(3000);

  // Go to tasks
  await page.goto(`${BASE}/tasks`, { timeout: 15000, waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);

  await page.screenshot({ path: '/home/hermes/tasks-kanban.png', fullPage: true });
  console.log('Screenshot saved!');

  await browser.close();
}
run().catch(e => {
  console.error('Error:', e.message);
  process.exit(1);
});