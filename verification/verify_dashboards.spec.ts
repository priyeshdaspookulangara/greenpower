import { test, expect } from '@playwright/test';

test('verify admin dashboard', async ({ page }) => {
  await page.goto('http://localhost:8000/login.php');
  await page.fill('input[name="email"]', 'admin@empress.com');
  await page.fill('input[name="password"]', 'admin123');
  await page.click('button[type="submit"]');
  await page.waitForURL('http://localhost:8000/admin.php');
  await page.screenshot({ path: 'verification/screenshots/admin_empress.png', fullPage: true });
});

test('verify user dashboard', async ({ page }) => {
  await page.goto('http://localhost:8000/login.php');
  await page.fill('input[name="email"]', 'john@example.com');
  await page.fill('input[name="password"]', 'password123');
  await page.click('button[type="submit"]');
  await page.waitForURL('http://localhost:8000/dashboard.php');
  await page.screenshot({ path: 'verification/screenshots/dashboard_empress.png', fullPage: true });
});
