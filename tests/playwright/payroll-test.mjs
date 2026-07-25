import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';
const ADMIN = { email: 'arafat.122260@gmail.com', password: 'password' };

async function screenshot(page, name) {
  await page.screenshot({ path: `/home/hrm/tests/playwright/screenshots/${name}.png`, fullPage: true });
  console.log(`  📸 ${name}.png`);
}

async function run() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  console.log('=== 1. LOGIN ===');
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.waitForSelector('input[id="data.email"]', { timeout: 10000 });
  await page.fill('input[id="data.email"]', ADMIN.email);
  await page.fill('input[id="data.password"]', ADMIN.password);
  await page.click('button[type="submit"]');
  await page.waitForURL('http://160.25.226.41:8082/', { timeout: 10000 });
  console.log('  ✅ Logged in as admin');

  await screenshot(page, '01-dashboard');

  console.log('\n=== 2. PAYROLL RUNS ===');
  // Navigate via sidebar — click Payroll Runs
  await page.click('a:has-text("Payroll Runs")');
  await page.waitForSelector('table', { timeout: 10000 });
  await page.waitForTimeout(500);
  console.log('  ✅ Payroll Runs page loaded');
  await screenshot(page, '02-payroll-runs');

  // Check the July 2026 run is visible
  const periodText = await page.textContent('table');
  console.log(`  Table contains: July 2026 = ${periodText.includes('July 2026')}`);
  console.log(`  Status: paid = ${periodText.includes('paid')}`);

  console.log('\n=== 3. VIEW PAYSLIPS ===');
  // Click "View Payslips" action button for the July 2026 run
  const viewBtn = page.locator('tr').filter({ hasText: 'July 2026' }).locator('a:has-text("View Payslips")');
  if (await viewBtn.count() > 0) {
    await viewBtn.click();
    await page.waitForSelector('table', { timeout: 10000 });
    await page.waitForTimeout(500);
    console.log('  ✅ Payslips page loaded with filter');
  } else {
    // Fallback: go to Payslips directly
    await page.click('a:has-text("Payslips")');
    await page.waitForSelector('table', { timeout: 10000 });
    await page.waitForTimeout(500);
    console.log('  ✅ Payslips page loaded (direct nav)');
  }
  await screenshot(page, '03-payslips');

  const payslipText = await page.textContent('table');
  console.log(`  Employee found: ${payslipText.includes('Employee')}`);
  console.log(`  Gross 41,500: ${payslipText.includes('41,500') || payslipText.includes('41500')}`);

  console.log('\n=== 4. OPEN PAYSLIP DETAIL ===');
  // Click "View" button on the payslip row
  const viewPayslipBtn = page.locator('tr').filter({ hasText: 'Employee' }).locator('a:has-text("View")');
  if (await viewPayslipBtn.count() > 0) {
    await viewPayslipBtn.click();
    await page.waitForTimeout(1000);
    console.log('  ✅ Payslip detail page loaded');
  } else {
    // Try clicking the first row link
    const firstLink = page.locator('table tbody tr:first-child a').first();
    if (await firstLink.count() > 0) {
      await firstLink.click();
      await page.waitForTimeout(1000);
      console.log('  ✅ Payslip detail via first row');
    }
  }
  await screenshot(page, '04-payslip-detail');

  // Verify earnings and deductions on the form
  const bodyText = await page.textContent('body');
  console.log(`  Basic Salary visible: ${bodyText.includes('Basic')}`);
  console.log(`  Gross Pay visible: ${bodyText.includes('Gross')}`);
  console.log(`  Provident Fund visible: ${bodyText.includes('Provident')}`);
  console.log(`  Net Pay visible: ${bodyText.includes('Net')}`);

  console.log('\n=== 5. PDF DOWNLOAD ===');
  // Check if Print/PDF button exists
  const pdfBtn = page.locator('button:has-text("PDF"), a:has-text("PDF"), button:has-text("Print")');
  const pdfBtnCount = await pdfBtn.count();
  console.log(`  PDF/Print button found: ${pdfBtnCount > 0 ? '✅' : '❌'} (${pdfBtnCount})`);
  if (pdfBtnCount > 0) {
    await pdfBtn.first().click();
    await page.waitForTimeout(1000);
    console.log('  PDF button clicked');
  }
  await screenshot(page, '05-pdf-action');

  console.log('\n=== 6. SALARY STRUCTURES ===');
  await page.click('a:has-text("Salary Structures")');
  await page.waitForSelector('table', { timeout: 10000 });
  await page.waitForTimeout(500);
  await screenshot(page, '06-salary-structures');
  const salaryText = await page.textContent('body');
  console.log(`  Employee salary structure: ${salaryText.includes('Employee') ? '✅' : '❌'}`);
  console.log(`  Gross 41,500: ${salaryText.includes('41,500') || salaryText.includes('41500') ? '✅' : '❌'}`);

  console.log('\n=== ✅ PAYROLL TEST COMPLETE ===');
  await browser.close();
}

run().catch(err => {
  console.error('❌ TEST FAILED:', err.message);
  process.exit(1);
});
