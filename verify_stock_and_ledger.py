import asyncio
from playwright.async_api import async_playwright
import sqlite3
import random

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        context = await browser.new_context()
        page = await context.new_page()

        # 1. Check current stock
        db_path = 'config/solar_shop.sqlite'
        conn = sqlite3.connect(db_path)
        cursor = conn.cursor()
        cursor.execute("SELECT stock FROM products WHERE id = 1")
        initial_stock = cursor.fetchone()[0]
        print(f"Initial Stock: {initial_stock}")

        # 2. Register fresh user
        email = f"stock_test_{random.randint(1000, 9999)}@example.com"
        await page.goto('http://localhost:8000/register.php')
        await page.locator('.container .glass-card input[name="name"]').fill('Stock Tester')
        await page.locator('.container .glass-card input[name="email"]').fill(email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        # 3. Login
        await page.goto('http://localhost:8000/login.php')
        await page.locator('.container .glass-card input[name="email"]').fill(email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        # 4. Purchase
        await page.goto('http://localhost:8000/product.php?id=1')
        await page.click('button:has-text("Add to Shopping Cart")')
        await page.goto('http://localhost:8000/cart.php')
        await page.click('button:has-text("PROCEED TO CHECKOUT")')
        await page.wait_for_url('**/checkout.php*')
        await page.click('button:has-text("Confirm and Pay")')
        await page.wait_for_url('**/dashboard.php*')
        print("Purchase completed.")

        # 5. Verify stock decreased
        cursor.execute("SELECT stock FROM products WHERE id = 1")
        final_stock = cursor.fetchone()[0]
        print(f"Final Stock: {final_stock}")

        if final_stock == initial_stock - 1:
            print("SUCCESS: Stock correctly decremented.")
        else:
            print(f"FAILURE: Stock mismatch. Expected {initial_stock-1}, got {final_stock}")

        conn.close()
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
