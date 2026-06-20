import asyncio
from playwright.async_api import async_playwright
import sqlite3
import random

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        context = await browser.new_context()
        page = await context.new_page()

        # 1. Profile B Flow (Low CIBIL)
        email_b = f"low_cibil_{random.randint(1000, 9999)}@example.com"
        print(f"Testing Profile B with {email_b}...")

        await page.goto('http://localhost:8000/register.php')
        await page.locator('.container .glass-card input[name="name"]').fill('Low Cibil User')
        await page.locator('.container .glass-card input[name="email"]').fill(email_b)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card select[name="cibil_status"]').select_option('low')
        await page.locator('.container .glass-card button[type="submit"]').click()

        # Login
        await page.goto('http://localhost:8000/login.php')
        await page.locator('.container .glass-card input[name="email"]').fill(email_b)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        # Buy a Solar Panel (ID 1)
        await page.goto('http://localhost:8000/product.php?id=1')
        await page.click('button:has-text("Add to Shopping Cart")')
        await page.goto('http://localhost:8000/checkout.php')

        # Verify Profile B logic is displayed
        logic_section = await page.locator('text=Processing Logic:').locator('..').inner_text()
        print(f"Checkout Logic Displayed:\n{logic_section}")

        if "Profile B" in logic_section:
            print("UI correctly identifies Profile B.")
        else:
            print("UI failed to identify Profile B.")

        await page.click('button:has-text("Confirm and Pay")')
        await page.wait_for_url('**/dashboard.php*')
        print("Profile B purchase completed.")

        # 2. Database Verification
        db_path = 'config/solar_shop.sqlite'
        conn = sqlite3.connect(db_path)
        cursor = conn.cursor()

        # Check Transactions for this order
        cursor.execute("""
            SELECT t.type, t.amount, t.description
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            WHERE u.email = ?
            ORDER BY t.id DESC
        """, (email_b,))
        transactions = cursor.fetchall()

        print("\nLedger for Profile B Purchase:")
        for t in transactions:
            print(f"- Type: {t[0]}, Amount: {t[1]}, Desc: {t[2]}")

        # Ensure "third_party_subsidy" is present for Profile B
        found_tp = any("third_party_subsidy" == t[0] for t in transactions)
        if found_tp:
            print("\nSUCCESS: Third-party routing verified in ledger.")
        else:
            print("\nFAILURE: Third-party routing NOT found in ledger.")

        conn.close()
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
