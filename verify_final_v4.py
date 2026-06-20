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

        print("Logging in as Admin...")
        await page.goto('http://localhost:8000/login.php')
        await page.locator('.container .glass-card input[name="email"]').fill('admin@example.com')
        await page.locator('.container .glass-card input[name="password"]').fill('password')
        await page.locator('.container .glass-card button[type="submit"]').click()

        await page.wait_for_url('**/index.php*')
        print("Logged in. Navigating to Admin Panel...")
        await page.goto('http://localhost:8000/admin.php')

        # Open edit modal for first product
        await page.locator('.fa-edit').first.click()
        await page.wait_for_selector('#productModal.active')
        print("Edit Modal Opened.")

        # Change price
        await page.locator('#productModal input[name="price"]').fill('42000.00')
        await page.locator('button[name="edit_product_submit"]').click()
        await page.wait_for_url('**/admin.php*')
        print("Product Updated.")

        cursor.execute("SELECT price FROM products ORDER BY id ASC LIMIT 1")
        price = cursor.fetchone()[0]
        print(f"Verified Price in DB: {price}")
        if price == 42000.00:
            print("ADMIN EDIT: SUCCESS")
        else:
            print("ADMIN EDIT: FAILED")

        conn.close()
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
