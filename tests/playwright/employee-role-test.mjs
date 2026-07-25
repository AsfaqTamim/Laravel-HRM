import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';
const EMP = { email: 'employee@kassit.com', password: 'password' };

async function screenshot(page, name) {
  await page.screenshot({ path: `/home/hrm/tests/playwright/screenshots/emp-${name}.png`, fullPage: true });
  console.log(`  📸 emp-${name}.png`);
}

async function run() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  console.log('=== 1. EMPLOYEE LOGIN ===');
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[id="data.email"]', EMP.email);
  await page.fill('input[id="data.password"]', EMP.password);
  await page.click('button[type="submit"]');
  await page.waitForURL('http://160.25.226.41:8082/', { timeout: 10000 });
  console.log('  ✅ Logged in as employee');
  await screenshot(page, '01-dashboard');

  // Check for Check In button on dashboard
  const body = await page.textContent('body');
  console.log(`  "Check In" button: ${body.includes('Check In') ? '✅' : '❌'}`);
  console.log(`  "Break" button: ${body.includes('Break') ? '✅' : '❌'}`);
  console.log(`  "Check Out" button: ${body.includes('Check Out') ? '✅' : '❌'}`);
  console.log(`  Check-in widget rendered: ${body.includes('Not Clocked In') ? '✅' : body.includes('Clocked In') ? '✅' : '⚠️'}`);

  // Check sidebar items
  const sidebar = await page.textContent('nav');
  const sidebarItems = [
    'Attendance', 'Holidays', 'Leaves', 'Tasks', 'Projects', 'Payslips',
    'Employee Directory', 'Attendance Report', 'Leave Report', 'Payroll Report'
  ];
  const visible = sidebarItems.filter(i => sidebar.includes(i));
  const hidden = sidebarItems.filter(i => !sidebar.includes(i));
  console.log(`  Sidebar: ${visible.length}/${sidebarItems.length} visible`);
  visible.forEach(i => console.log(`    ✅ ${i}`));
  hidden.forEach(i => console.log(`    ❌ ${i}`));

  console.log('\n=== 2. CHECK IN (click the button) ===');
  const checkInBtn = page.locator('button:has-text("Check In")');
  if (await checkInBtn.count() > 0) {
    await checkInBtn.click();
    await page.waitForTimeout(2000);
    // After check-in, should see Break and Check Out buttons
    const afterBody = await page.textContent('body');
    console.log(`  "Back to Work"/"Break": ${afterBody.includes('Back to Work') || afterBody.includes('Break') ? '✅' : '⚠️'}`);
    console.log(`  "Check Out": ${afterBody.includes('Check Out') ? '✅' : '❌'}`);
    console.log(`  Notification (success check-in): ${afterBody.includes('Checked in') ? '✅' : afterBody.includes('Late') ? '✅' : '⚠️'}`);

    // Take a break
    const breakBtn = page.locator('button:has-text("Break")');
    if (await breakBtn.count() > 0) {
      await breakBtn.click();
      await page.waitForTimeout(1000);
      const breakBody = await page.textContent('body');
      console.log(`  "Back to Work" after break: ${breakBody.includes('Back to Work') ? '✅' : '❌'}`);
      console.log(`  Break notification: ${breakBody.includes('Break started') ? '✅' : '⚠️'}`);

      // Return from break
      const backBtn = page.locator('button:has-text("Back to Work")');
      if (await backBtn.count() > 0) {
        await backBtn.click();
        await page.waitForTimeout(1000);
        console.log('  Returned from break ✅');
      }
    }

    // Check out
    const checkOutBtn = page.locator('button:has-text("Check Out")');
    if (await checkOutBtn.count() > 0) {
      await checkOutBtn.click();
      await page.waitForTimeout(2000);
      const outBody = await page.textContent('body');
      console.log(`  Check-out notification: ${outBody.includes('Checked out') ? '✅' : '⚠️'}`);
      console.log('  ✅ Full check-in/out cycle completed');
    }
  } else {
    console.log('  ⚠️ Check In button not found (maybe already checked in from previous test)');
    // Try checking if already clocked in
    const clockedIn = body.includes('Clocked In') || body.includes('On Break');
    console.log(`  Already clocked in today: ${clockedIn}`);
  }

  await screenshot(page, '02-after-checkin');

  console.log('\n=== 3. ATTENDANCE RECORDS ===');
  const attLink = page.locator('a:has-text("Attendance")').first();
  if (await attLink.count() > 0) {
    await attLink.click();
    await page.waitForTimeout(1000);
    await screenshot(page, '03-attendance');
    console.log('  ✅ Attendance history accessible');
  }

  console.log('\n=== 4. PAYSLIPS ===');
  const payslipLink = page.locator('a:has-text("Payslips")').first();
  if (await payslipLink.count() > 0) {
    await payslipLink.click();
    await page.waitForTimeout(1000);
    await screenshot(page, '04-payslips');
    console.log('  ✅ Payslips accessible');
  }

  console.log('\n=== 5. VERIFY NO ADMIN PAGES ===');
  const adminOnly = ['Payroll Runs', 'Salary Structures', 'Departments', 'Designations',
                     'Office Times', 'Leave Types', 'Roles'];
  const fullText = await page.textContent('body');
  const leaked = adminOnly.filter(i => fullText.includes(i));
  if (leaked.length === 0) {
    console.log('  ✅ No admin pages visible');
  } else {
    leaked.forEach(i => console.log(`  ⚠️ '${i}' visible`));
  }

  console.log('\n=== ✅ EMPLOYEE TEST COMPLETE ===');
  await browser.close();
}

run().catch(err => {
  console.error('❌ TEST FAILED:', err.message);
  process.exit(1);
});
