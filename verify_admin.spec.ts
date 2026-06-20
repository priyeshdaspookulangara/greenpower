import { test, expect } from '@playwright/test';

test('Verify Admin UI', async ({ page }) => {
  // Login as admin
  await page.goto('http://localhost:8000/login.php');
  await page.fill('input[name="email"]', 'admin@example.com');
  await page.fill('input[name="password"]', 'admin123');
  await page.click('button[type="submit"]');

  // Go to admin page
  await page.goto('http://localhost:8000/admin.php');
  await page.waitForSelector('.sidebar');
  await page.screenshot({ path: 'admin_dashboard.png', fullPage: true });

  // Open Add Product Modal
  await page.click('button[data-target="#productModal"]');
  await page.waitForSelector('#productModal', { state: 'visible' });
  await page.screenshot({ path: 'admin_add_product_modal.png' });
});
