const { chromium } = require('playwright');

(async () => {
  let browser;
  try {
    browser = await chromium.launch();
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1280, height: 1080 });

    // Verify Home Page
    await page.goto('http://localhost:8000/index.php');
    await page.screenshot({ path: 'verification/screenshots/index_empress.png', fullPage: true });
    console.log('Home Page screenshot captured.');

    // Verify Login Page
    await page.goto('http://localhost:8000/login.php');
    await page.screenshot({ path: 'verification/screenshots/login_empress.png' });
    console.log('Login Page screenshot captured.');

  } catch (err) {
    console.error('Error during screenshot capture:', err);
  } finally {
    if (browser) await browser.close();
  }
})();
