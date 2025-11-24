const { chromium } = require('playwright');

async function auditSite() {
  console.log('🔍 Starting PilgrimIRL Site Audit with Playwright\n');

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
  });
  const page = await context.newPage();

  const issues = [];
  const results = {
    passed: [],
    warnings: [],
    failed: []
  };

  // Collect console errors
  page.on('console', msg => {
    if (msg.type() === 'error') {
      issues.push({ type: 'console_error', message: msg.text(), location: page.url() });
    }
  });

  // Collect page errors
  page.on('pageerror', error => {
    issues.push({ type: 'page_error', message: error.message, location: page.url() });
  });

  // Test 1: Homepage Load
  console.log('📄 Test 1: Homepage Load');
  try {
    const response = await page.goto('https://pilgrimirl.com', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    if (response.status() === 200) {
      results.passed.push('Homepage loads successfully (HTTP 200)');
      console.log('   ✅ Homepage loads (HTTP 200)');
    } else {
      results.failed.push(`Homepage returned HTTP ${response.status()}`);
      console.log(`   ❌ Homepage returned HTTP ${response.status()}`);
    }

    // Check page title
    const title = await page.title();
    if (title && title.includes('PilgrimIRL')) {
      results.passed.push(`Page title correct: "${title}"`);
      console.log(`   ✅ Page title: "${title}"`);
    } else {
      results.warnings.push(`Page title may be incorrect: "${title}"`);
      console.log(`   ⚠️  Page title: "${title}"`);
    }
  } catch (error) {
    results.failed.push(`Homepage failed to load: ${error.message}`);
    console.log(`   ❌ Failed to load: ${error.message}`);
  }

  // Test 2: Check for critical elements
  console.log('\n🔎 Test 2: Critical Elements');
  const criticalSelectors = {
    'Navigation': 'nav',
    'Header': 'header',
    'Main content': 'main',
    'Footer': 'footer'
  };

  for (const [name, selector] of Object.entries(criticalSelectors)) {
    try {
      const element = await page.locator(selector).first();
      if (await element.count() > 0) {
        results.passed.push(`${name} element found`);
        console.log(`   ✅ ${name} element found`);
      } else {
        results.warnings.push(`${name} element not found`);
        console.log(`   ⚠️  ${name} element not found`);
      }
    } catch (error) {
      results.warnings.push(`${name} check failed: ${error.message}`);
      console.log(`   ⚠️  ${name} check failed`);
    }
  }

  // Test 3: Check for JavaScript errors
  console.log('\n⚡ Test 3: JavaScript Errors');
  await page.waitForTimeout(2000); // Wait for JS to execute

  if (issues.length === 0) {
    results.passed.push('No JavaScript errors detected');
    console.log('   ✅ No JavaScript errors');
  } else {
    console.log(`   ❌ Found ${issues.length} JavaScript errors:`);
    issues.forEach((issue, i) => {
      results.failed.push(`JS Error ${i + 1}: ${issue.message}`);
      console.log(`      ${i + 1}. ${issue.type}: ${issue.message}`);
    });
  }

  // Test 4: Monastic Sites Archive
  console.log('\n📚 Test 4: Monastic Sites Archive');
  try {
    const response = await page.goto('https://pilgrimirl.com/monastic-sites/', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    if (response.status() === 200) {
      results.passed.push('Monastic sites archive loads');
      console.log('   ✅ Archive page loads (HTTP 200)');

      // Check for site cards
      const siteCards = await page.locator('.pilgrim-card, article').count();
      if (siteCards > 0) {
        results.passed.push(`Found ${siteCards} site cards`);
        console.log(`   ✅ Found ${siteCards} site cards`);
      } else {
        results.warnings.push('No site cards found on archive');
        console.log('   ⚠️  No site cards found');
      }
    } else {
      results.failed.push(`Archive page returned HTTP ${response.status()}`);
      console.log(`   ❌ HTTP ${response.status()}`);
    }
  } catch (error) {
    results.failed.push(`Archive page failed: ${error.message}`);
    console.log(`   ❌ Failed: ${error.message}`);
  }

  // Test 5: Check specific site page
  console.log('\n⛪ Test 5: Individual Site Page');
  try {
    const response = await page.goto('https://pilgrimirl.com/monastic-sites/glendalough/', {
      waitUntil: 'networkidle',
      timeout: 30000
    });

    if (response.status() === 200) {
      results.passed.push('Individual site page loads');
      console.log('   ✅ Site page loads (HTTP 200)');

      // Check for map element
      const mapElement = await page.locator('#single-site-map, .pilgrim-map-container').count();
      if (mapElement > 0) {
        results.passed.push('Map container element found');
        console.log('   ✅ Map container found');
      } else {
        results.warnings.push('Map container not found');
        console.log('   ⚠️  Map container not found');
      }

      // Check for Google Maps script
      const mapsScript = await page.locator('script[src*="maps.googleapis.com"]').count();
      if (mapsScript > 0) {
        results.passed.push('Google Maps script loaded');
        console.log('   ✅ Google Maps script loaded');
      } else {
        results.warnings.push('Google Maps script not found');
        console.log('   ⚠️  Google Maps script not found');
      }
    } else {
      results.failed.push(`Site page returned HTTP ${response.status()}`);
      console.log(`   ❌ HTTP ${response.status()}`);
    }
  } catch (error) {
    results.failed.push(`Site page failed: ${error.message}`);
    console.log(`   ❌ Failed: ${error.message}`);
  }

  // Test 6: Mobile Responsiveness
  console.log('\n📱 Test 6: Mobile Responsiveness');
  try {
    await page.setViewportSize({ width: 375, height: 667 }); // iPhone size
    await page.goto('https://pilgrimirl.com', { waitUntil: 'networkidle' });

    const hamburgerMenu = await page.locator('.menu-toggle, .hamburger, [aria-label*="menu"]').count();
    if (hamburgerMenu > 0) {
      results.passed.push('Mobile menu toggle found');
      console.log('   ✅ Mobile menu found');
    } else {
      results.warnings.push('Mobile menu toggle not found');
      console.log('   ⚠️  Mobile menu not found');
    }
  } catch (error) {
    results.warnings.push(`Mobile test failed: ${error.message}`);
    console.log(`   ⚠️  Mobile test failed`);
  }

  // Test 7: Performance Check
  console.log('\n⚡ Test 7: Performance Check');
  try {
    const startTime = Date.now();
    await page.goto('https://pilgrimirl.com', { waitUntil: 'load' });
    const loadTime = Date.now() - startTime;

    if (loadTime < 3000) {
      results.passed.push(`Page load time: ${loadTime}ms (excellent)`);
      console.log(`   ✅ Load time: ${loadTime}ms (excellent)`);
    } else if (loadTime < 5000) {
      results.warnings.push(`Page load time: ${loadTime}ms (acceptable)`);
      console.log(`   ⚠️  Load time: ${loadTime}ms (acceptable)`);
    } else {
      results.failed.push(`Page load time: ${loadTime}ms (slow)`);
      console.log(`   ❌ Load time: ${loadTime}ms (slow)`);
    }
  } catch (error) {
    results.warnings.push(`Performance test failed: ${error.message}`);
    console.log(`   ⚠️  Performance test failed`);
  }

  // Test 8: SEO Meta Tags
  console.log('\n🔍 Test 8: SEO Meta Tags');
  try {
    await page.goto('https://pilgrimirl.com', { waitUntil: 'networkidle' });

    const metaDescription = await page.locator('meta[name="description"]').getAttribute('content');
    if (metaDescription && metaDescription.length > 0) {
      results.passed.push(`Meta description: "${metaDescription.substring(0, 50)}..."`);
      console.log('   ✅ Meta description present');
    } else {
      results.warnings.push('Meta description missing');
      console.log('   ⚠️  Meta description missing');
    }

    const ogTitle = await page.locator('meta[property="og:title"]').getAttribute('content');
    if (ogTitle) {
      results.passed.push('Open Graph title present');
      console.log('   ✅ Open Graph tags present');
    } else {
      results.warnings.push('Open Graph tags missing');
      console.log('   ⚠️  Open Graph tags missing');
    }
  } catch (error) {
    results.warnings.push(`SEO check failed: ${error.message}`);
    console.log(`   ⚠️  SEO check failed`);
  }

  await browser.close();

  // Print Summary
  console.log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log('📊 AUDIT SUMMARY');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log(`✅ Passed: ${results.passed.length}`);
  console.log(`⚠️  Warnings: ${results.warnings.length}`);
  console.log(`❌ Failed: ${results.failed.length}`);
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');

  if (results.failed.length > 0) {
    console.log('❌ CRITICAL ISSUES:');
    results.failed.forEach((issue, i) => console.log(`   ${i + 1}. ${issue}`));
    console.log('');
  }

  if (results.warnings.length > 0) {
    console.log('⚠️  WARNINGS:');
    results.warnings.forEach((issue, i) => console.log(`   ${i + 1}. ${issue}`));
    console.log('');
  }

  // Overall Status
  if (results.failed.length === 0 && results.warnings.length === 0) {
    console.log('🎉 All tests passed! Site is fully operational.');
    process.exit(0);
  } else if (results.failed.length === 0) {
    console.log('✅ Site is operational with minor warnings.');
    process.exit(0);
  } else {
    console.log('❌ Site has critical issues that need attention.');
    process.exit(1);
  }
}

auditSite().catch(error => {
  console.error('Fatal error during audit:', error);
  process.exit(1);
});
