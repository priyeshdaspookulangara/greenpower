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

        # Test Case: Different Casing/Names (e.g. member_id / member_pass)
        print("--- TESTING ROBUST LEGACY LOGIN ---")
        cursor.execute("DROP TABLE IF EXISTS customer")
        cursor.execute("CREATE TABLE customer (member_id TEXT, member_pass TEXT, full_name TEXT)")
        cursor.execute("INSERT INTO customer VALUES ('TEST007', 'password123', 'Robust User')")
        conn.commit()

        await page.goto('http://localhost:8000/burfee_login.php')
        # Use specific locators to avoid modal interference
        await page.locator('.glass-card input[name="member_id"]').fill('TEST007')
        await page.locator('.glass-card input[name="password"]').fill('password123')
        await page.locator('.glass-card button[type="submit"]').click()

        await page.wait_for_url('**/index.php*')
        print("LOGIN WITH DYNAMIC COLUMNS: SUCCESS")

        await page.goto('http://localhost:8000/dashboard.php')
        name = await page.locator('.glass-card h3:has-text("Profile Info") + p').inner_text()
        if "Robust User" in name:
            print("USER DATA MIGRATION: SUCCESS")
        else:
            print(f"USER DATA MIGRATION: FAILED (Got {name})")

        conn.close()
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
