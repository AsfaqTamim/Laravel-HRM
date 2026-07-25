import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function sleep(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function run() {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

  // Login as employee
  await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[wire\\:model="data.email"]', 'employee@test.com');
  await page.fill('input[wire\\:model="data.password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(url => !url.pathname.includes('login'), { timeout: 10000 });
  await sleep(2000);

  // Go to leave requests
  await page.goto(BASE + '/leaves', { waitUntil: 'networkidle' });
  await sleep(2000);

  // Screenshot
  await page.screenshot({ path: './screenshots/employee-leaves.png', fullPage: false });

  // Check for create/new button
  const buttons = await page.evaluate(() => {
    const all = document.querySelectorAll('a, button, .fi-btn');
    return Array.from(all)
      .filter(el => el.textContent?.toLowerCase().includes('new') || el.textContent?.toLowerCase().includes('create'))
      .map(el => ({ text: el.textContent?.trim().replace(/\s+/g, ' '), href: el.getAttribute('href'), tag: el.tagName }));
  });

  console.log("=== CREATE/NEW BUTTONS ===");
  buttons.forEach(b => console.log(`  "${b.text}" href=${b.href || '-'} tag=${b.tag}`));

  if (buttons.length === 0) {
    console.log("No create/new buttons found!");
    
    // Dump all links
    const allLinks = await page.evaluate(() => {
      return Array.from(document.querySelectorAll('a')).map(a => ({
        text: a.textContent?.trim().replace(/\s+/g, ' ').substring(0, 60),
        href: a.getAttribute('href')
      })).filter(l => l.text);
    });
    console.log("\n=== ALL LINKS ON PAGE ===");
    allLinks.forEach(l => console.log(`  "${l.text}" -> ${l.href}`));
  }

  await browser.close();
}

run().catch(console.error);
