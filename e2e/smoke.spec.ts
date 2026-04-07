import { test, expect } from '@playwright/test';

/**
 * Browser smoke: mirrors docs/POST-DEPLOY-SMOKE.md table (HTTP 200, no hard fail).
 * Local: `php artisan serve` then `npm run test:e2e`
 * Production: `PLAYWRIGHT_BASE_URL=https://your.domain npm run test:e2e`
 */
const smokePaths = [
    '/',
    '/volunteer/opportunities',
    '/about',
    '/programs',
    '/events',
    '/legal/terms',
    '/sitemap.xml',
    '/robots.txt',
    '/login',
    '/admin/login',
];

for (const path of smokePaths) {
    test(`GET ${path} is OK`, async ({ page }) => {
        const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
        expect(response, `no response for ${path}`).not.toBeNull();
        expect(response!.ok(), `${path} returned ${response!.status()}`).toBeTruthy();
    });
}
