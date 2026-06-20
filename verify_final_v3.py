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

        print("\n--- VERIFYING ADMIN EDIT ---")
        await page.goto('http://localhost:8000/login.php')
        # Standard admin password in seed is 'password123'
        await page.locator('.container .glass-card input[name="email"]').fill('admin@example.com')
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        await page.wait_for_url('**/index.php*')
        await page.goto('http://localhost:8000/admin.php')

        # Click edit on first product
        await page.locator('.fa-edit').first.click()
        await page.wait_for_selector('#productModal.active')

        await page.locator('#productModal input[name="price"]').fill('35000.00')
        await page.click('button[name="edit_product_submit"]')
        await page.wait_for_url('**/admin.php*')

        cursor.execute("SELECT price FROM products WHERE id = (SELECT MIN(id) FROM products)")
        price = cursor.fetchone()[0]
        print(f"Updated Price: {price}")
        if price == 35000.00:
            print("ADMIN EDIT: SUCCESS")
        else:
            print("ADMIN EDIT: FAILED")

        conn.close()
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
