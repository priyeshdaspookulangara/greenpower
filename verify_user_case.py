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

        # Simulate user's exact case from screenshot
        print("--- TESTING EXACT USER CREDENTIALS ---")
        cursor.execute("DROP TABLE IF EXISTS customer")
        cursor.execute("CREATE TABLE customer (Memberid TEXT, MemberPass TEXT)")
        cursor.execute("INSERT INTO customer VALUES ('719298', 'root000')")
        conn.commit()

        await page.goto('http://localhost:8000/burfee_login.php')
        await page.locator('.glass-card input[name="member_id"]').fill('719298')
        await page.locator('.glass-card input[name="password"]').fill('root000')
        await page.locator('.glass-card button[type="submit"]').click()

        await page.wait_for_url('**/index.php*')
        print("LOGIN WITH 719298/root000: SUCCESS")

        # Verify migration
        await page.goto('http://localhost:8000/dashboard.php')
        id_display = await page.locator('.glass-card h3:has-text("Profile Info") + p').inner_text()
        print(f"Migrated ID: {id_display}")

        conn.close()
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
