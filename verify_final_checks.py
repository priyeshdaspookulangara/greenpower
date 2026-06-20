import asyncio
from playwright.async_api import async_playwright
import sqlite3
import random

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        context = await browser.new_context()
        page = await context.new_page()

        # 1. Verify Stock Logic
        print("--- VERIFYING STOCK LOGIC ---")
        db_path = 'config/solar_shop.sqlite'
        conn = sqlite3.connect(db_path)
        cursor = conn.cursor()
        cursor.execute("SELECT stock FROM products WHERE id = 1")
        initial_stock = cursor.fetchone()[0]
        print(f"Initial Stock for Product 1: {initial_stock}")

        # Register and buy
        email = f"stock_final_{random.randint(1000, 9999)}@example.com"
        await page.goto('http://localhost:8000/register.php')
        await page.locator('.container .glass-card input[name="name"]').fill('Stock Final')
        await page.locator('.container .glass-card input[name="email"]').fill(email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()
        await page.goto('http://localhost:8000/login.php')
        await page.locator('.container .glass-card input[name="email"]').fill(email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        await page.goto('http://localhost:8000/product.php?id=1')
        await page.click('button:has-text("Add to Shopping Cart")')
        await page.goto('http://localhost:8000/cart.php')
        await page.click('a:has-text("Proceed to Checkout")')
        await page.wait_for_url('**/checkout.php*')
        await page.click('button:has-text("Confirm and Pay")')
        await page.wait_for_url('**/dashboard.php*')

        cursor.execute("SELECT stock FROM products WHERE id = 1")
        final_stock = cursor.fetchone()[0]
        print(f"Final Stock: {final_stock}")
        if final_stock == initial_stock - 1:
            print("STOCK LOGIC: OK")
        else:
            print(f"STOCK LOGIC: FAILED (Expected {initial_stock-1})")

        # 2. Verify Burfee Member Login
        print("\n--- VERIFYING BURFEE MEMBER LOGIN ---")
        # Ensure we have a member in the customer table
        cursor.execute("INSERT OR IGNORE INTO customer (MemberId, MemberPass, FullName, CibilStatus) VALUES ('B123', 'pass123', 'Burfee Test', 'good')")
        conn.commit()

        await page.goto('http://localhost:8000/logout.php')
        await page.goto('http://localhost:8000/burfee_login.php')
        # Use specific locator for the Burfee login page
        await page.locator('.glass-card input[name="member_id"]').fill('B123')
        await page.locator('.glass-card input[name="password"]').fill('pass123')
        await page.locator('.glass-card button[type="submit"]').click()
        await page.wait_for_url('**/index.php*')

        # Check if they can access dashboard
        await page.goto('http://localhost:8000/dashboard.php')
        dashboard_name = await page.locator('.glass-card h3:has-text("Profile Info") + p').inner_text()
        print(f"Logged in as: {dashboard_name}")
        if "Burfee Test" in dashboard_name:
            print("BURFEE LOGIN: OK")
        else:
            print("BURFEE LOGIN: FAILED")

        conn.close()
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
