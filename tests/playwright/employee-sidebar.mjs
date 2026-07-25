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

  // Screenshot
  await page.screenshot({ path: './screenshots/employee-dashboard.png', fullPage: false });
  
  // Get ALL sidebar text
  const sidebarText = await page.evaluate(() => {
    const all = document.querySelectorAll('.fi-sidebar-item-label, .fi-sidebar-group-label, nav a, .fi-sidebar-item a, .fi-sidebar-item-button');
    return Array.from(all).map(el => el.textContent?.trim().replace(/\s+/g, ' ')).filter(t => t && t.length > 0);
  });
  
  console.log("=== EMPLOYEE SIDEBAR ===");
  sidebarText.forEach(t => console.log(`  "${t}"`));

  // Also check for leave-related items more broadly
  const leaveItems = await page.evaluate(() => {
    const all = document.querySelectorAll('nav a');
    return Array.from(all)
      .filter(a => a.textContent?.toLowerCase().includes('leave'))
      .map(a => ({ text: a.textContent?.trim().replace(/\s+/g, ' '), href: a.getAttribute('href') }));
  });
  
  console.log("\n=== LEAVE-RELATED ITEMS ===");
  leaveItems.forEach(i => console.log(`  "${i.text}" -> ${i.href}`));

  await page.screenshot({ path: './screenshots/employee-sidebar.png', fullPage: true });
  console.log('\nScreenshots saved');
  await browser.close();
}

run().catch(console.error);
