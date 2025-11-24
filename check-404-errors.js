const { chromium } = require('playwright');

async function check404Errors() {
  console.log('🔍 Checking for 404 errors on PilgrimIRL\n');

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  const failedRequests = [];

  // Listen to all requests
  page.on('response', response => {
    if (response.status() === 404) {
      failedRequests.push({
        url: response.url(),
        status: response.status(),
        type: response.request().resourceType()
      });
    }
  });

  console.log('Loading homepage...');
  await page.goto('https://pilgrimirl.com', { waitUntil: 'networkidle', timeout: 30000 });
  await page.waitForTimeout(3000);

  console.log('\nLoading archive page...');
  await page.goto('https://pilgrimirl.com/monastic-sites/', { waitUntil: 'networkidle', timeout: 30000 });
  await page.waitForTimeout(3000);

  console.log('\nLoading individual site...');
  await page.goto('https://pilgrimirl.com/monastic-sites/glendalough/', { waitUntil: 'networkidle', timeout: 30000 });
  await page.waitForTimeout(3000);

  await browser.close();

  console.log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log('404 ERRORS FOUND:');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');

  if (failedRequests.length === 0) {
    console.log('✅ No 404 errors found!');
  } else {
    failedRequests.forEach((req, i) => {
      console.log(`${i + 1}. ${req.type}: ${req.url}`);
    });
  }
}

check404Errors().catch(console.error);
