import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function sleep(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function run() {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

  // Login as admin — Filament uses wire:model="data.email" / data.password
  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[wire\\:model="data.email"]', 'arafat.122260@gmail.com');
  await page.fill('input[wire\\:model="data.password"]', 'password');
  await page.click('button[type="submit"]');
  // Dashboard is at root (''), not /dashboard
  await page.waitForURL(url => !url.pathname.includes('login'), { timeout: 10000 });
  await sleep(2000);

  // Go to Employees
  await page.goto(BASE + '/employees', { waitUntil: 'networkidle' });
  await sleep(2000);

  // Check all buttons
  const buttons = await page.evaluate(() => {
    const btns = document.querySelectorAll('button, a');
    return Array.from(btns).map(b => ({
      tag: b.tagName,
      text: b.textContent?.trim().substring(0, 60),
      href: b.getAttribute('href'),
      class: b.className?.substring(0, 80),
      visible: b.offsetParent !== null
    }));
  });

  console.log("=== ALL BUTTONS/LINKS ===");
  buttons.forEach(b => {
    if (b.text) console.log(`[${b.tag}] "${b.text}" visible=${b.visible} href=${b.href || '-'}`);
  });

  // Also check for the create button specifically
  const createBtn = await page.evaluate(() => {
    // Check common Filament create button selectors
    const selectors = [
      'a[href*="create"]',
      '.filament-tables-header-toolbar a[href*="create"]',
      'button:has-text("New Employee")',
      'button:has-text("Create")',
      'a:has-text("New Employee")',
      'a:has-text("Create")',
      'a[href*="employees/create"]',
    ];
    for (const sel of selectors) {
      const el = document.querySelector(sel);
      if (el) return `FOUND via: ${sel} | text="${el.textContent?.trim()}" | visible=${el.offsetParent !== null}`;
    }
    return 'NOT FOUND with any selector';
  });
  console.log("\n=== CREATE BUTTON SEARCH ===");
  console.log(createBtn);

  // Print HTML around where create button should be
  const htmlSnippet = await page.evaluate(() => {
    const toolbar = document.querySelector('.fi-ta-header-toolbar') || document.querySelector('.fi-ta-header');
    if (toolbar) return toolbar.innerHTML.substring(0, 2000);
    return 'No toolbar found';
  });
  console.log("\n=== TOOLBAR HTML ===");
  console.log(htmlSnippet);

  await browser.close();
}

run().catch(console.error);
