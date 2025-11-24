// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Playwright configuration for PilgrimIRL WordPress theme testing
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
    testDir: './e2e',

    // Maximum time one test can run
    timeout: 30 * 1000,

    expect: {
        timeout: 5000
    },

    // Run tests in files in parallel
    fullyParallel: true,

    // Fail the build on CI if you accidentally left test.only in the source code
    forbidOnly: !!process.env.CI,

    // Retry on CI only
    retries: process.env.CI ? 2 : 0,

    // Opt out of parallel tests on CI
    workers: process.env.CI ? 1 : undefined,

    // Reporter to use
    reporter: [
        ['html'],
        ['list'],
        ['json', { outputFile: 'test-results/results.json' }]
    ],

    // Shared settings for all the projects below
    use: {
        // Base URL for the WordPress site
        baseURL: 'http://localhost:10028',

        // Collect trace when retrying the failed test
        trace: 'on-first-retry',

        // Screenshot on failure
        screenshot: 'only-on-failure',

        // Video on first retry
        video: 'retain-on-failure',
    },

    // Configure projects for major browsers
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },

        {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'] },
        },

        {
            name: 'webkit',
            use: { ...devices['Desktop Safari'] },
        },

        // Mobile viewports
        {
            name: 'mobile',
            use: { ...devices['iPhone 12'] },
        },

        {
            name: 'mobile-landscape',
            use: {
                ...devices['iPhone 12'],
                viewport: { width: 844, height: 390 }
            },
        },

        {
            name: 'tablet',
            use: { ...devices['iPad Pro'] },
        },
    ],

    // Run your local dev server before starting the tests
    webServer: {
        command: 'echo "WordPress should be running on Local by Flywheel"',
        url: 'http://localhost:10028',
        reuseExistingServer: !process.env.CI,
        timeout: 120 * 1000,
    },
});
