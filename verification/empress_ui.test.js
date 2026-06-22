const { test, expect } = require('@playwright/test');

test('Home Page Visuals', async ({ page }) => {
  await page.goto('http://localhost:8000/index.php');
  await page.screenshot({ path: 'verification/screenshots/index_empress.png', fullPage: true });
});

test('Login Page Visuals', async ({ page }) => {
  await page.goto('http://localhost:8000/login.php');
  await page.screenshot({ path: 'verification/screenshots/login_empress.png' });
});
