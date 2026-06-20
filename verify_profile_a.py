import asyncio
from playwright.async_api import async_playwright
import sqlite3
import random

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        context = await browser.new_context()
        page = await context.new_page()

        suffix = random.randint(1000, 9999)
        ref_email = f"ref_{suffix}@example.com"
        cust_email = f"cust_{suffix}@example.com"

        # 1. Register Referrer
        await page.goto('http://localhost:8000/register.php')
        await page.locator('.container .glass-card input[name="name"]').fill('Referrer')
        await page.locator('.container .glass-card input[name="email"]').fill(ref_email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()
        await page.wait_for_url('**/login.php*')

        # 2. Register Customer with Referrer
        await page.goto(f'http://localhost:8000/register.php?ref={ref_email}')
        await page.locator('.container .glass-card input[name="name"]').fill('Customer A')
        await page.locator('.container .glass-card input[name="email"]').fill(cust_email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card select[name="cibil_status"]').select_option('good')
        await page.locator('.container .glass-card button[type="submit"]').click()
        await page.wait_for_url('**/login.php*')

        # 3. Customer Purchase
        await page.goto('http://localhost:8000/login.php')
        await page.locator('.container .glass-card input[name="email"]').fill(cust_email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        await page.goto('http://localhost:8000/product.php?id=1')
        await page.click('button:has-text("Add to Shopping Cart")')
        await page.goto('http://localhost:8000/checkout.php')

        logic_text = await page.locator('text=Processing Logic:').locator('..').inner_text()
        print(f"Checkout Logic Displayed:\n{logic_text}")

        await page.click('button:has-text("Confirm and Pay")')
        await page.wait_for_url('**/dashboard.php*')

        # 4. Database Check
        db_path = 'config/solar_shop.sqlite'
        conn = sqlite3.connect(db_path)
        cursor = conn.cursor()

        # Check Referrer Earnings
        cursor.execute("SELECT id FROM users WHERE email = ?", (ref_email,))
        ref_id = cursor.fetchone()[0]

        cursor.execute("SELECT type, amount FROM transactions WHERE user_id = ?", (ref_id,))
        txs = cursor.fetchall()

        print("\nReferrer Transactions:")
        found_5000 = False
        found_4500 = False
        for tx in txs:
            print(f"- {tx[0]}: {tx[1]}")
            if tx[0] == 'subsidy_payout' and tx[1] == 5000: found_5000 = True
            if tx[0] == 'referral_commission' and tx[1] == 4500: found_4500 = True

        if found_5000 and found_4500:
            print("\nSUCCESS: Profile A 5000/4500 distribution verified in database.")
        else:
            print("\nFAILURE: Distribution missing.")

        conn.close()
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
