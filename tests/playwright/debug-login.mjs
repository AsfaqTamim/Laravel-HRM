import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function run() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  // Debug: see what the login page looks like
  await page.goto(`${BASE}/login`);
  await page.waitForTimeout(3000);

  // Dump page HTML
  const html = await page.content();
  console.log('=== PAGE HTML (first 3000 chars) ===');
  console.log(html.substring(0, 3000));
  
  // Try to find inputs
  const inputs = await page.locator('input').all();
  console.log(`\n=== Found ${inputs.length} inputs ===`);
  for (const input of inputs) {
    const name = await input.getAttribute('name');
    const type = await input.getAttribute('type');
    const id = await input.getAttribute('id');
    const placeholder = await input.getAttribute('placeholder');
    console.log(`  name="${name}" type="${type}" id="${id}" placeholder="${placeholder}"`);
  }

  await browser.close();
}

run().catch(err => { console.error(err); process.exit(1); });
