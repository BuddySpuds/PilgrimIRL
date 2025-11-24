// @ts-check
const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;

test.describe('Accessibility Tests', () => {

    test('homepage should not have accessibility violations', async ({ page }) => {
        await page.goto('/');

        const accessibilityScanResults = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
            .analyze();

        expect(accessibilityScanResults.violations).toEqual([]);
    });

    test('search form should be keyboard accessible', async ({ page }) => {
        await page.goto('/');

        // Tab to search input
        await page.keyboard.press('Tab');
        await page.keyboard.press('Tab');

        // Check if search input is focused
        const searchInput = page.locator('#pilgrim-search-input');
        await expect(searchInput).toBeFocused();

        // Type using keyboard
        await page.keyboard.type('test search');
        await expect(searchInput).toHaveValue('test search');
    });

    test('should have proper heading hierarchy', async ({ page }) => {
        await page.goto('/');

        // Check for H1
        const h1 = page.locator('h1');
        const h1Count = await h1.count();
        expect(h1Count).toBe(1); // Should have exactly one H1

        // Check heading order
        const headings = await page.locator('h1, h2, h3, h4, h5, h6').all();
        expect(headings.length).toBeGreaterThan(0);
    });

    test('images should have alt text', async ({ page }) => {
        await page.goto('/');

        const images = page.locator('img');
        const count = await images.count();

        // Check each image has alt attribute
        for (let i = 0; i < count; i++) {
            const img = images.nth(i);
            const alt = await img.getAttribute('alt');
            // Alt can be empty for decorative images, but attribute should exist
            expect(alt).not.toBeNull();
        }
    });

    test('links should have accessible names', async ({ page }) => {
        await page.goto('/');

        const links = page.locator('a');
        const count = await links.count();

        for (let i = 0; i < count; i++) {
            const link = links.nth(i);
            const text = await link.textContent();
            const ariaLabel = await link.getAttribute('aria-label');

            // Link should have either text content or aria-label
            expect(text || ariaLabel).toBeTruthy();
        }
    });

    test('form inputs should have labels', async ({ page }) => {
        await page.goto('/');

        // Check search input has label (or aria-label)
        const searchInput = page.locator('#pilgrim-search-input');
        const placeholder = await searchInput.getAttribute('placeholder');
        const ariaLabel = await searchInput.getAttribute('aria-label');

        // Should have either placeholder or aria-label for accessibility
        expect(placeholder || ariaLabel).toBeTruthy();
    });

    test('should have sufficient color contrast', async ({ page }) => {
        await page.goto('/');

        const accessibilityScanResults = await new AxeBuilder({ page })
            .withTags(['wcag2aa'])
            .include('body')
            .analyze();

        const contrastViolations = accessibilityScanResults.violations.filter(
            violation => violation.id === 'color-contrast'
        );

        expect(contrastViolations).toEqual([]);
    });

});

test.describe('ARIA Attributes', () => {

    test('interactive elements should have proper ARIA attributes', async ({ page }) => {
        await page.goto('/');

        // Check buttons have aria-label or text
        const buttons = page.locator('button');
        const count = await buttons.count();

        for (let i = 0; i < count; i++) {
            const button = buttons.nth(i);
            const text = await button.textContent();
            const ariaLabel = await button.getAttribute('aria-label');

            expect(text || ariaLabel).toBeTruthy();
        }
    });

    test('navigation should have proper landmarks', async ({ page }) => {
        await page.goto('/');

        // Check for main navigation
        const nav = page.locator('nav');
        await expect(nav).toBeVisible();

        // Check for main content area
        const main = page.locator('main');
        await expect(main).toBeVisible();
    });

});
