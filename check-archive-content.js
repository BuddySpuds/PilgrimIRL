const { chromium } = require('playwright');

async function checkArchive() {
  console.log('🔍 Checking Archive Page Content\n');

  const browser = await chromium.launch({ headless: false }); // visible for debugging
  const page = await browser.newPage();

  await page.goto('https://pilgrimirl.com/monastic-sites/', { waitUntil: 'networkidle' });

  console.log('Page loaded. Checking for content...\n');

  // Check various selectors for posts
  const selectors = [
    { name: 'article elements', selector: 'article' },
    { name: '.pilgrim-card', selector: '.pilgrim-card' },
    { name: '.site-card', selector: '.site-card' },
    { name: '.post', selector: '.post' },
    { name: '.entry', selector: '.entry' },
    { name: 'h2 headings', selector: 'h2' },
    { name: 'main content', selector: 'main' }
  ];

  for (const { name, selector } of selectors) {
    const count = await page.locator(selector).count();
    console.log(`${name}: ${count} found`);
  }

  // Get the main content HTML
  console.log('\n━━━ Main Content HTML (first 500 chars) ━━━');
  const mainContent = await page.locator('main').innerHTML();
  console.log(mainContent.substring(0, 500));

  // Check if there's a "no posts" message
  const bodyText = await page.locator('body').innerText();
  if (bodyText.includes('No posts') || bodyText.includes('Nothing found')) {
    console.log('\n⚠️  "No posts" message detected');
  }

  // Wait a bit for user to see the browser
  await page.waitForTimeout(5000);

  await browser.close();
}

checkArchive().catch(console.error);
