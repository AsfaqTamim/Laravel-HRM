import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';
const EMP = { email: 'employee@kassit.com', password: 'password' };

async function run() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  console.log('=== LOGIN ===');
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[id="data.email"]', EMP.email);
  await page.fill('input[id="data.password"]', EMP.password);
  await page.click('button[type="submit"]');
  await page.waitForURL('http://160.25.226.41:8082/', { timeout: 10000 });

  // Get ALL text visible on the dashboard
  const text = await page.textContent('body');
  console.log('ALL body text (first 2000 chars):');
  console.log(text.substring(0, 2000));

  // Check for the widget by HTML element
  const widgetEl = await page.locator('.fi-wi-stats-overview-widget, .fi-wi-widget').count();
  console.log(`\nWidget elements found: ${widgetEl}`);

  // Get page URL
  console.log(`\nCurrent URL: ${page.url()}`);

  // Check for buttons
  const buttons = await page.locator('button').allTextContents();
  console.log('\nAll button texts:', JSON.stringify(buttons));

  // Check for headings
  const headings = await page.locator('h1, h2, h3, h4, h5, h6').allTextContents();
  console.log('\nAll headings:', JSON.stringify(headings));

  // Check sidebar structure
  const aside = await page.locator('aside').first().textContent().catch(() => 'NO ASIDE');
  console.log(`\nAside text: ${aside.substring(0, 1500)}`);

  await page.screenshot({ path: `/home/hrm/tests/playwright/screenshots/debug-dashboard.png`, fullPage: true });
  console.log('\n📸 debug-dashboard.png saved');

  await browser.close();
}

run().catch(err => {
  console.error('❌ FAILED:', err.message);
  process.exit(1);
});
