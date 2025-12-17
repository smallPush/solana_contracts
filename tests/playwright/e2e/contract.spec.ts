import { test, expect, Page } from '@playwright/test';

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

    const userExists = async (page: Page, username: string) => {
        await page.goto('/admin/people');
        await page.fill('input[name="user"]', username);
        await page.getByRole('button', { name: 'Filter' }).click();

        const userLink = page.getByRole('link', { name: username, exact: true });
        return await userLink.count() > 0;
    };

    const createUser = async (page: Page, username: string) => {
        if (await userExists(page, username)) {
            console.log(`User ${username} already exists. Skipping creation.`);
            return;
        }
        await page.goto('/admin/people/create');
        await page.fill('input[name="mail"]', `${username}@example.com`);
        await page.fill('input[name="name"]', username);
        await page.fill('input[name="pass[pass1]"]', 'password');
        await page.fill('input[name="pass[pass2]"]', 'password');
        await page.getByRole('button', { name: 'Create new account' }).click();
        await expect(page.locator('.messages--status')).toContainText('Created a new user account');
    };

    test('should create a new contract with parties and expiration', async ({ page }) => {
        // Create test users
        await createUser(page, 'party_a');
        await createUser(page, 'party_b');


        await page.goto('/contract/add');

        // Fill in the title
        await page.fill('input[name="title[0][value]"]', 'Test Contract with Parties');

        // Fill in the description
        // content is in a CKEditor 5 instance, so we target the editable div
        await page.locator('.ck-editor__editable').fill('Contract between Party A and Party B.');

        // Fill Expires
        // We target the wrapper to be specific
        const expiresWrapper = page.locator('#edit-expires-wrapper');
        // Date is today adding one month
        const today = new Date();
        const expiresDate = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate());
        await expiresWrapper.locator('input[type="date"]').fill(expiresDate.toISOString().split('T')[0]);
        await expiresWrapper.locator('input[type="time"]').fill('12:00');

        // Fill Party A
        await page.fill('input[name="party_a[0][target_id]"]', 'party_a');
        // Wait for autocomplete and select
        await page.click('ul.ui-autocomplete li a:has-text("party_a")');

        // Fill Party B
        await page.fill('input[name="party_b[0][target_id]"]', 'party_b');
        await page.click('ul.ui-autocomplete li a:has-text("party_b")');

        // Save the contract
        await page.getByRole('button', { name: 'Save' }).click();

        // Verify successful creation
        await expect(page.locator('.messages--status')).toContainText('Created the Test Contract with Parties Contract.');
    });
});

