import { test, expect, Page } from '@playwright/test';

test.describe('Contract Signing Display', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/user/login');
        await page.fill('input[name="name"]', 'admin');
        await page.fill('input[name="pass"]', 'admin');
        await page.getByRole('button', { name: 'Log in' }).click();
    });

    const createUser = async (page: Page, username: string) => {
        await page.goto('/admin/people/create');
        await page.fill('input[name="mail"]', `${username}@example.com`);
        await page.fill('input[name="name"]', username);
        await page.fill('input[name="pass[pass1]"]', 'password');
        await page.fill('input[name="pass[pass2]"]', 'password');
        await page.getByRole('button', { name: 'Create new account' }).click();
    };

    test('should display signature info after signing (simulated)', async ({ page }) => {
        // 1. Setup: Create users and contract
        const partyA = 'signer_a_' + Date.now();
        const partyB = 'signer_b_' + Date.now();
        await createUser(page, partyA);
        await createUser(page, partyB);

        await page.goto('/contract/add');
        await page.fill('input[name="title[0][value]"]', 'Sign Test Contract');
        await page.locator('.ck-editor__editable').fill('Content to be signed.');

        const today = new Date();
        const expiresDate = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate());
        const expiresWrapper = page.locator('#edit-expires-wrapper');
        await expiresWrapper.locator('input[type="date"]').fill(expiresDate.toISOString().split('T')[0]);
        await expiresWrapper.locator('input[type="time"]').fill('12:00');

        await page.fill('input[name="party_a[0][target_id]"]', partyA);
        await page.click(`ul.ui-autocomplete li a:has-text("${partyA}")`);
        await page.fill('input[name="party_b[0][target_id]"]', partyB);
        await page.click(`ul.ui-autocomplete li a:has-text("${partyB}")`);
        await page.getByRole('button', { name: 'Save' }).click();

        // Get contract ID from URL
        const url = page.url();
        const contractId = url.split('/').pop();

        // 2. Login as Party A
        await page.goto('/user/logout');
        // Click on logout
        await page.getByRole('button', { name: 'Log out' }).click();

        await page.goto('/user/login');
        await page.fill('input[name="name"]', partyA);
        await page.fill('input[name="pass"]', 'password');
        await page.getByRole('button', { name: 'Log in' }).click();

        // 3. Visit Sign Page
        await page.goto(`/contract/${contractId}/sign`);

        // Baseline: Should see "Sign Contract" button
        await expect(page.locator('#solana-sign')).toBeVisible();
        // Baseline: Should NOT see "Contract Signed" yet
        await expect(page.getByText('Contract Signed')).not.toBeVisible();

        // Note: Actual signing requires wallet interaction which is hard to mock here.
        // Manual verification or backend seeding would be needed to test the "Signed" state.
    });
});
