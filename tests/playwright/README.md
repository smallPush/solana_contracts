# Playwright Tests for Solana Contracts

This directory contains End-to-End (E2E) tests using Playwright.

## Setup

1. Install dependencies:
   ```bash
   npm install
   npx playwright install
   ```

2. Configure Environment:
   By default, tests run against `http://localhost`. If your Drupal site is running on a different URL or port, set the `BASE_URL` environment variable.

   ```bash
   export BASE_URL=http://localhost:8080
   ```

## Running Tests

Run all tests:
```bash
npx playwright test
```

Run in UI mode:
```bash
npx playwright test --ui
```

## Troubleshooting

- **Login Failures**: Ensure the admin credentials in `e2e/contract.spec.ts` are correct or update them to match your environment.
- **Connection Refused/404**: Verify `BASE_URL` matches your running Drupal instance.
