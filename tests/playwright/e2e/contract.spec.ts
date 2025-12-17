import { test, expect } from '@playwright/test';

test.describe('Contract Management', () => {
    test.beforeEach(async ({ page }) => {
        // Login phase - assuming standard Drupal login path
        await page.goto('/user/login');
        await page.fill('input[name="name"]', 'admin');
        await page.fill('input[name="pass"]', 'admin');
        await page.getByRole('button', { name: 'Log in' }).click();
        // Verify login success
        await expect(page.locator('h1')).not.toContainText('Log in');
    });

    test('should create a new contract', async ({ page }) => {
        await page.goto('/contract/add');

        // Fill in the title
        await page.fill('input[name="title[0][value]"]', 'Test Contract via Playwright');

        // Fill in the description
        await page.fill('textarea[name="description[0][value]"]', 'This is a test contract created by Playwright automation.');

        // Save the contract
        await page.getByRole('button', { name: 'Save' }).click();

        // Verify successful creation
        await expect(page.locator('.messages--status')).toContainText('Created the Test Contract via Playwright Contract.');
    });
});
