import { chromium } from 'playwright';

async function run() {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });
  const page = await (await browser.newContext({ viewport: { width: 1200, height: 800 } })).newPage();

  await page.goto('http://160.25.226.41:8082/login', { timeout: 15000, waitUntil: 'networkidle' });
  await page.fill('#data\\.email', 'arafat.122260@gmail.com');
  await page.fill('#data\\.password', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(3000);

  await page.goto('http://160.25.226.41:8082/profile', { timeout: 15000, waitUntil: 'networkidle' });
  await page.waitForTimeout(1500);
  await page.screenshot({ path: '/home/hermes/profile-card-view.png', fullPage: true });
  console.log('Done');

  await browser.close();
}
run().catch(e => { console.error('Error:', e.message); process.exit(1); });
