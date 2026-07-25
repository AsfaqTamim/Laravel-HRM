import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';
const EMP = { email: 'employee@kassit.com', password: 'password' };

async function run() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  // Track pass/fail
  let pass = 0, fail = 0;
  const check = (name, ok) => { console.log(`  ${ok ? '✅' : '❌'} ${name}`); ok ? pass++ : fail++; };

  console.log('1. LOGIN');
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[id="data.email"]', EMP.email);
  await page.fill('input[id="data.password"]', EMP.password);
  await page.click('button[type="submit"]');
  await page.waitForURL('http://160.25.226.41:8082/', { timeout: 10000 });
  check('Logged in', true);

  console.log('\n2. DASHBOARD — check-in widget');
  const body = await page.textContent('body');
  check('Dashboard title visible', body.includes('Dashboard'));
  check('Check In button on dashboard', body.includes('Check In'));
  check('Not Clocked In status visible', body.includes('Not Clocked In'));

  // Get the check-in button and click it
  const checkInBtn = page.locator('button:has-text("Check In")').first();
  const btnCount = await checkInBtn.count();
  check('Check In button exists in DOM', btnCount > 0);
  
  if (btnCount > 0) {
    await checkInBtn.click();
    await page.waitForTimeout(2000);
    const after = await page.textContent('body');
    
    check('Break button appeared after check-in', after.includes('Break'));
    check('Check Out button appeared after check-in', after.includes('Check Out'));
    
    // Click Break
    const breakBtn = page.locator('button:has-text("Break")').first();
    const breakCount = await breakBtn.count();
    check('Break button exists', breakCount > 0);
    
    if (breakCount > 0) {
      await breakBtn.click();
      await page.waitForTimeout(1000);
      const breakAfter = await page.textContent('body');
      check('Back to Work appeared after break start', breakAfter.includes('Back to Work'));
      
      // Return from break
      const backBtn = page.locator('button:has-text("Back to Work")').first();
      if (await backBtn.count() > 0) {
        await backBtn.click();
        await page.waitForTimeout(1000);
        check('Break ended, Break button available again', (await page.textContent('body')).includes('Break'));
      }
    }
    
    // Check Out
    const checkOutBtn = page.locator('button:has-text("Check Out")').first();
    if (await checkOutBtn.count() > 0) {
      await checkOutBtn.click();
      await page.waitForTimeout(2000);
      const outAfter = await page.textContent('body');
      check('Checked out successfully', outAfter.includes('Check In'));
    }
  }

  console.log('\n3. SIDEBAR — employee-accessible links');
  const sidebar = await page.locator('aside').first().textContent().catch(() => '');
  const expectedSidebar = [
    'Dashboard', 'Holidays', 'Employee Directory', 'Attendance',
    'Leaves', 'Leave Report', 'Payslips', 'Payroll Report',
    'Tasks', 'Projects', 'Attendance Report'
  ];
  for (const item of expectedSidebar) {
    check(`Sidebar: ${item}`, sidebar.includes(item));
  }

  console.log('\n4. NO ADMIN PAGES');
  const adminPages = ['Payroll Runs', 'Salary Structures', 'Departments', 'Designations',
                      'Office Times', 'Leave Types', 'Roles'];
  for (const item of adminPages) {
    if (sidebar.includes(item)) check(`⚠️ Admin: ${item} visible`, false);
  }

  // Cleanup: if we created a today record, delete it
  console.log('\n5. CLEANUP');
  const today = await page.textContent('body');
  if (today.includes('Checked in') || today.includes('Checked out')) {
    // Record was created, we can clean via tinker
    console.log('  Attendance record was created during test');
  }

  console.log(`\n${'='.repeat(40)}`);
  console.log(`RESULT: ${pass} passed, ${fail} failed`);
  console.log(`${'='.repeat(40)}`);

  await browser.close();
}

run().catch(err => {
  console.error('❌ TEST FAILED:', err.message);
  process.exit(1);
});
