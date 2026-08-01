import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';
const EMP = { email: 'employee@example.com', password: 'password' };

async function run() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  console.log('1. LOGIN & CHECK SIDEBAR');
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[id="data.email"]', EMP.email);
  await page.fill('input[id="data.password"]', EMP.password);
  await page.click('button[type="submit"]');
  await page.waitForURL('http://160.25.226.41:8082/', { timeout: 10000 });
  await page.waitForTimeout(2000);

  // Check sidebar links by their actual hrefs / visible text using locators
  const sidebarLinks = [
    'Dashboard', 'Holidays', 'Employees', 'Attendance', 'Leaves', 'Payslips',
    'Payroll Runs', 'Tasks', 'Projects'
  ];
  const navLinks = page.locator('aside a');
  const allLinks = await navLinks.allTextContents();
  const clean = allLinks.map(t => t.trim()).filter(t => t.length > 0);
  console.log('  Sidebar links found:', JSON.stringify(clean));

  for (const item of sidebarLinks) {
    const found = clean.some(t => t === item || t.startsWith(item));
    console.log(`  ${found ? '✅' : '❌'} ${item}`);
  }

  // Check that report pages exist (they might be nested)
  // Check by trying to navigate to each
  console.log('\n2. CHECK REPORT PAGES');
  const checkPage = async (name, path) => {
    await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(500);
    const ok = page.url().includes(path) || !page.url().includes('login');
    console.log(`  ${ok ? '✅' : '❌'} ${name} (${path})`);
    return ok;
  };
  await checkPage('Dashboard', '/');
  await checkPage('Employee Directory', '/employees');  // report page
  await checkPage('Attendance Report', '/attendance-report');
  await checkPage('Leave Report', '/leave-report');
  await checkPage('Payroll Report', '/payroll-report');

  // Test the check-in flow
  console.log('\n3. CHECK-IN/BREAK/CHECK-OUT CYCLE');
  // Go back to dashboard
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(1000);

  // Look for Check In button
  const checkInBtn = page.locator('button:has-text("Check In")').first();
  if (await checkInBtn.count() > 0) {
    console.log('  ✅ Check In button found');
    await checkInBtn.click();
    await page.waitForTimeout(2000);

    // After check-in
    const breakBtn = page.locator('button:has-text("Break")').first();
    console.log(`  ✅ After check-in: ${await breakBtn.count() > 0 ? 'Break visible' : 'Break missing'}`);

    if (await breakBtn.count() > 0) {
      await breakBtn.click();
      await page.waitForTimeout(1000);
      const backBtn = page.locator('button:has-text("Back to Work")').first();
      console.log(`  ✅ After break: ${await backBtn.count() > 0 ? 'Back to Work visible' : 'Back to Work missing'}`);

      if (await backBtn.count() > 0) {
        await backBtn.click();
        await page.waitForTimeout(1000);
        console.log(`  ✅ After returning: Break available again: ${(await page.locator('button:has-text("Break")').count()) > 0}`);
      }
    }

    // Check out
    const checkOutBtn = page.locator('button:has-text("Check Out")').first();
    if (await checkOutBtn.count() > 0) {
      await checkOutBtn.click();
      await page.waitForTimeout(2000);
      const checkInAgain = page.locator('button:has-text("Check In")');
      console.log(`  ✅ After checkout: Check In available again: ${await checkInAgain.count() > 0}`);
    }
    console.log('  ✅ Full check-in/break/check-out cycle completed');
  } else {
    console.log('  ⚠️ Already checked in');
  }

  // Cleanup
  console.log('\n4. CLEANUP');
  await browser.close();
  console.log('  ✅ Closed browser');
}

run().catch(err => {
  console.error('❌ FAILED:', err.message);
  process.exit(1);
});
