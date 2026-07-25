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

  // Check sidebar links
  const links = await page.evaluate(() => {
    const items = document.querySelectorAll('.fi-sidebar-item a, .fi-sidebar-group a, .fi-sidebar-item-button');
    return Array.from(items).map(a => ({
      text: a.textContent?.trim().substring(0, 60),
      href: a.getAttribute('href'),
      visible: a.offsetParent !== null
    }));
  });

  console.log("=== SIDEBAR LINKS ===");
  links.forEach(l => {
    if (l.text) console.log(`"${l.text}" -> ${l.href || '-'} (visible=${l.visible})`);
  });

  // Visit employees page
  await page.goto(BASE + '/employees', { waitUntil: 'networkidle' });
  await sleep(2000);
  await page.screenshot({ path: './screenshots/employees-after-fix.png', fullPage: false });
  console.log('\nEmployees page screenshot saved');

  await browser.close();
}

run().catch(console.error);
