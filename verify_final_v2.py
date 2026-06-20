import asyncio
from playwright.async_api import async_playwright
import sqlite3

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        context = await browser.new_context()
        page = await context.new_page()

        db_path = 'config/solar_shop.sqlite'
        conn = sqlite3.connect(db_path)
        cursor = conn.cursor()

        # 2. Verify Admin Edit
        print("\n--- VERIFYING ADMIN EDIT ---")
        await page.goto('http://localhost:8000/login.php')
        await page.locator('.container .glass-card input[name="email"]').fill('admin@example.com')
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        await asyncio.sleep(1)
        await page.goto('http://localhost:8000/admin.php')

        # Take a screenshot to see why it fails
        await page.screenshot(path='/home/jules/verification/screenshots/admin_panel.png')

        # Click edit icon
        await page.locator('.fa-edit').first.click()
        await page.wait_for_selector('#productModal.active')

        await page.locator('#productModal input[name="price"]').fill('29999.00')
        await page.click('button[name="edit_product_submit"]')

        await asyncio.sleep(1)

        # Get ID of the edited product from URL or check all
        cursor.execute("SELECT MAX(price) FROM products")
        max_price = cursor.fetchone()[0]
        print(f"Max Price in DB: {max_price}")
        if max_price == 29999.00:
            print("ADMIN EDIT: SUCCESS")
        else:
            print("ADMIN EDIT: FAILED")

        conn.close()
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
