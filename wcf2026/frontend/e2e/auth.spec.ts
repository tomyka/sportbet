import { test, expect } from '@playwright/test';

const testEmail = `e2e-${Date.now()}@example.com`;
const testPassword = 'e2e-secret-pass-123';

test.describe('Auth flow', () => {
  test('register → see verification page', async ({ page }) => {
    await page.goto('/register');

    await page.getByLabel(/name/i).fill('E2E User');
    await page.getByLabel(/email/i).fill(testEmail);
    await page.getByLabel(/^password$/i).fill(testPassword);
    await page.getByLabel(/confirm password/i).fill(testPassword);
    await page.getByRole('button', { name: /create account/i }).click();

    await expect(page.getByText(/check your email/i)).toBeVisible();
  });

  test('login with known credentials', async ({ page }) => {
    // Requires a pre-seeded user — see docs/superpowers/plans/2026-05-28-wcf2026-phase-1-auth-users.md Task 9.1
    await page.goto('/login');

    await page.getByLabel(/email/i).fill('smoke@example.com');
    await page.getByLabel(/password/i).fill('secret-pass');
    await page.getByRole('button', { name: /sign in/i }).click();

    await expect(page).toHaveURL('/');
    await expect(page.getByText(/smoke@example.com|Smoke/i).first()).toBeVisible();
  });

  test('unauthenticated redirect to login', async ({ page }) => {
    await page.goto('/profile');
    await expect(page).toHaveURL(/login/);
  });

  test('forgot password form shows confirmation', async ({ page }) => {
    await page.goto('/forgot-password');
    await page.getByLabel(/email/i).fill('anyone@example.com');
    await page.getByRole('button', { name: /send reset/i }).click();
    await expect(page.getByText(/check your email/i)).toBeVisible();
  });
});
