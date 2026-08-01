import { chromium } from 'playwright';

const BASE = 'http://160.25.226.41:8082';

async function run() {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  await page.goto(`${BASE}/login`);
  await page.waitForTimeout(2000);
  await page.fill('#data\\.email', 'employee@example.com');
  await page.fill('#data\\.password', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });
  await page.waitForTimeout(3000);

  await page.setViewportSize({ width: 1440, height: 900 });
  await page.waitForTimeout(1000);

  const info = await page.evaluate(() => {
    const results = [];

    // Find all fi-wi elements (widget columns)
    const widgets = document.querySelectorAll('.fi-wi');
    widgets.forEach((w, i) => {
      const rect = w.getBoundingClientRect();
      results.push({
        type: 'fi-wi (grid)',
        index: i,
        width: Math.round(rect.width),
        left: Math.round(rect.left),
        right: Math.round(rect.right),
        classes: (w.className || '').substring(0, 250),
      });
    });

    // Find all widget entries (fi-wi-entry)
    const entries = document.querySelectorAll('.fi-wi-entry');
    entries.forEach((e, i) => {
      const rect = e.getBoundingClientRect();
      results.push({
        type: 'fi-wi-entry (col)',
        index: i,
        width: Math.round(rect.width),
        left: Math.round(rect.left),
        right: Math.round(rect.right),
        colSpan: e.style.getPropertyValue('--col-span'),
        classes: (e.className || '').substring(0, 200),
      });
    });

    // Find the actual attendance card
    const cards = document.querySelectorAll('.rounded-xl.shadow-sm');
    cards.forEach((c, i) => {
      if (c.textContent.includes('Check In') || c.textContent.includes('Check Out')) {
        const rect = c.getBoundingClientRect();
        results.push({
          type: 'attendance-card',
          index: i,
          width: Math.round(rect.width),
          left: Math.round(rect.left),
          right: Math.round(rect.right),
          classes: (c.className || '').substring(0, 200),
        });
      }
    });

    // Check the page content wrapper
    const main = document.querySelector('main');
    if (main) {
      const rect = main.getBoundingClientRect();
      results.push({
        type: 'main element',
        width: Math.round(rect.width),
        left: Math.round(rect.left),
        right: Math.round(rect.right),
        classes: (main.className || '').substring(0, 200),
      });
    }

    // Check what --cols-* css vars are set
    const style = getComputedStyle(document.documentElement);

    return { results, cols: results };
  });

  // Also check CSS vars on the grid container
  const cssVars = await page.evaluate(() => {
    const grid = document.querySelector('.fi-wi');
    if (!grid) return 'no grid found';
    const style = getComputedStyle(grid);
    return {
      colsDefault: style.getPropertyValue('--cols-default'),
      colsLg: style.getPropertyValue('--cols-lg'),
      colsXl: style.getPropertyValue('--cols-xl'),
      gridTemplate: style.gridTemplateColumns,
    };
  });

  console.log('=== WIDGET GRID INFO ===');
  console.log('CSS vars:', JSON.stringify(cssVars, null, 2));
  console.log('\n=== DOM BOXES ===');
  for (const r of info.results) {
    console.log(`${r.type}#${r.index} w=${r.width} left=${r.left} right=${r.right} colSpan=${r.colSpan || '-'} classes=${(r.classes||'').substring(0,150)}`);
  }

  await browser.close();
}

run().catch(err => { console.error(err); process.exit(1); });
