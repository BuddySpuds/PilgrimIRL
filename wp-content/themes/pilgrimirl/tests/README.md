# PilgrimIRL E2E Testing Suite

Automated end-to-end and accessibility testing using Playwright.

## Setup

```bash
cd tests
npm install
npx playwright install
```

## Running Tests

### All Tests
```bash
npm test
```

### With UI Mode (Recommended for Development)
```bash
npm run test:ui
```

### Headed Mode (See Browser)
```bash
npm run test:headed
```

### Specific Browser
```bash
npm run test:chrome
npm run test:firefox
npm run test:safari
```

### Mobile Tests
```bash
npm run test:mobile
```

### Debug Mode
```bash
npm run test:debug
```

## Viewing Reports

```bash
npm run report
```

## Writing New Tests

Tests are located in `e2e/` directory. Create new files with `.spec.js` extension.

### Example Test

```javascript
const { test, expect } = require('@playwright/test');

test('my test', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('h1')).toContainText('Expected Text');
});
```

## Code Generation

Generate tests by recording browser interactions:

```bash
npm run codegen
```

## Test Coverage

Current test files:
- `e2e/homepage.spec.js` - Homepage functionality
- `e2e/accessibility.spec.js` - WCAG 2.1 AA compliance

## Requirements

- WordPress site running at http://localhost:10028
- Node.js 18+
- Playwright browsers installed

## CI/CD Integration

Tests can be run in GitHub Actions or other CI platforms. See `playwright.config.js` for CI-specific settings.
