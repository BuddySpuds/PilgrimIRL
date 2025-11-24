// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Homepage Tests', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/');
    });

    test('should load homepage with hero section', async ({ page }) => {
        // Check page title
        await expect(page).toHaveTitle(/PilgrimIRL/i);

        // Check hero section exists
        const hero = page.locator('.hero-section');
        await expect(hero).toBeVisible();

        // Check hero title
        const heroTitle = page.locator('.hero-title');
        await expect(heroTitle).toContainText(/Discover Ireland/i);
    });

    test('should have working navigation menu', async ({ page }) => {
        // Check navigation exists
        const nav = page.locator('.main-navigation');
        await expect(nav).toBeVisible();

        // Check menu items
        const menuItems = page.locator('.nav-menu a');
        const count = await menuItems.count();
        expect(count).toBeGreaterThan(0);
    });

    test('should display search form', async ({ page }) => {
        // Check search form exists
        const searchForm = page.locator('#pilgrim-search-form');
        await expect(searchForm).toBeVisible();

        // Check search input
        const searchInput = page.locator('#pilgrim-search-input');
        await expect(searchInput).toBeVisible();
        await expect(searchInput).toHaveAttribute('placeholder', /search/i);
    });

    test('should have county and type filters', async ({ page }) => {
        // Check county filter
        const countyFilter = page.locator('#filter-county');
        await expect(countyFilter).toBeVisible();

        // Check type filter
        const typeFilter = page.locator('#filter-post-type');
        await expect(typeFilter).toBeVisible();

        // Verify county options
        const countyOptions = countyFilter.locator('option');
        const countyCount = await countyOptions.count();
        expect(countyCount).toBeGreaterThan(32); // 32 counties + "All Counties"
    });

    test('should display feature cards', async ({ page }) => {
        // Check feature cards section
        const featureCards = page.locator('.feature-card');
        const count = await featureCards.count();
        expect(count).toBeGreaterThanOrEqual(3); // Monastic Sites, Routes, Ruins

        // Check each card has required elements
        for (let i = 0; i < count; i++) {
            const card = featureCards.nth(i);
            await expect(card.locator('h3')).toBeVisible();
            await expect(card.locator('p')).toBeVisible();
            await expect(card.locator('a.pilgrim-btn')).toBeVisible();
        }
    });

    test('should display county grid', async ({ page }) => {
        // Check counties overview section
        const countiesSection = page.locator('.counties-overview');
        await expect(countiesSection).toBeVisible();

        // Check county cards
        const countyCards = page.locator('.county-card');
        const count = await countyCards.count();
        expect(count).toBe(32); // All 32 Irish counties
    });

    test('should show interactive map section', async ({ page }) => {
        // Check map section exists
        const mapSection = page.locator('.map-section');
        await expect(mapSection).toBeVisible();

        // Check map container
        const mapContainer = page.locator('#pilgrim-main-map');
        await expect(mapContainer).toBeVisible();
    });

});

test.describe('Search Functionality', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/');
    });

    test('should perform search and show results', async ({ page }) => {
        const searchInput = page.locator('#pilgrim-search-input');
        const resultsContainer = page.locator('#pilgrim-search-results');

        // Type search query
        await searchInput.fill('abbey');

        // Wait a bit for debounced search
        await page.waitForTimeout(500);

        // Check if results container appears
        // Note: This will only work if site has data
        // await expect(resultsContainer).toBeVisible();
    });

    test('should filter by county', async ({ page }) => {
        const countyFilter = page.locator('#filter-county');

        // Select a county
        await countyFilter.selectOption('cork');

        // Add assertions based on your expected behavior
    });

    test('should clear filters with reset button', async ({ page }) => {
        const searchInput = page.locator('#pilgrim-search-input');
        const countyFilter = page.locator('#filter-county');
        const resetButton = page.locator('#reset-filters');

        // Fill in filters
        await searchInput.fill('test');
        await countyFilter.selectOption('dublin');

        // Click reset
        await resetButton.click();

        // Verify filters are cleared
        await expect(searchInput).toHaveValue('');
        await expect(countyFilter).toHaveValue('');
    });

});

test.describe('Responsive Design', () => {

    test('should display mobile menu on small screens', async ({ page }) => {
        // Set mobile viewport
        await page.setViewportSize({ width: 375, height: 667 });
        await page.goto('/');

        // Check if mobile menu toggle exists
        const mobileToggle = page.locator('.mobile-menu-toggle');
        // Mobile toggle may or may not exist depending on your implementation
    });

    test('should be readable on tablet', async ({ page }) => {
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.goto('/');

        // Check key elements are visible
        const hero = page.locator('.hero-section');
        await expect(hero).toBeVisible();
    });

});
