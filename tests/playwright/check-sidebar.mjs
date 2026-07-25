import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function sleep(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function run() {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

  // Login as admin first to check sidebar
  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[wire\\:model="data.email"]', 'arafat.122260@gmail.com');
  await page.fill('input[wire\\:model="data.password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(url => !url.pathname.includes('login'), { timeout: 10000 });
  await sleep(2000);

  const adminLinks = await page.evaluate(() => {
    const items = document.querySelectorAll('nav a, .fi-sidebar-item a, .fi-sidebar-item-button');
    return Array.from(items).map(a => ({
      text: a.textContent?.trim().substring(0, 60).replace(/\s+/g, ' '),
      href: a.getAttribute('href')
    })).filter(l => l.text && l.text.length > 0);
  });

  console.log("=== ADMIN SIDEBAR ===");
  adminLinks.forEach(l => console.log(`"${l.text}" -> ${l.href || '-'}`));

  await browser.close();
}

run().catch(console.error);
