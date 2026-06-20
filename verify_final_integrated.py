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
        ref_email = f"referrer_{suffix}@example.com"
        cust_email = f"customer_{suffix}@example.com"

        # 1. Create Referrer
        print(f"Creating Referrer: {ref_email}")
        await page.goto('http://localhost:8000/register.php')
        await page.locator('.container .glass-card input[name="name"]').fill('Referrer User')
        await page.locator('.container .glass-card input[name="email"]').fill(ref_email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        # 2. Register Customer with Referrer
        print(f"Registering Customer with Referrer: {cust_email}")
        await page.goto(f'http://localhost:8000/register.php?ref={ref_email}')
        await page.locator('.container .glass-card input[name="name"]').fill('Referral Customer')
        await page.locator('.container .glass-card input[name="email"]').fill(cust_email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card select[name="cibil_status"]').select_option('good')
        await page.locator('.container .glass-card button[type="submit"]').click()

        # 3. Customer Purchase
        print("Customer login and purchase...")
        await page.goto('http://localhost:8000/login.php')
        await page.locator('.container .glass-card input[name="email"]').fill(cust_email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        await page.goto('http://localhost:8000/product.php?id=1')
        await page.click('button:has-text("Add to Shopping Cart")')
        await page.goto('http://localhost:8000/checkout.php')
        await page.click('button:has-text("Confirm and Pay")')
        await page.wait_for_url('**/dashboard.php*')
        print("Purchase complete.")

        # 4. Check Referrer Earnings
        print("Checking Referrer Dashboard...")
        await page.goto('http://localhost:8000/logout.php')
        await page.goto('http://localhost:8000/login.php')
        await page.locator('.container .glass-card input[name="email"]').fill(ref_email)
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        await page.goto('http://localhost:8000/dashboard.php')

        # Use more specific locators
        level_income = await page.locator('.earnings-card:has-text("Level Income") .neon-text').inner_text()
        referral_bonus = await page.locator('.earnings-card:has-text("Referral Bonus") .neon-text').inner_text()

        print(f"Referrer Level Income: {level_income}")
        print(f"Referrer Referral Bonus: {referral_bonus}")

        if "5,000" in level_income and "4,500" in referral_bonus:
            print("SUCCESS: Referrer earned correct amounts (5000/4500 logic).")
        else:
            print("FAILURE: Earnings mismatch.")

        # 5. Admin Verification
        print("Checking Admin Ledger...")
        await page.goto('http://localhost:8000/logout.php')
        await page.goto('http://localhost:8000/login.php')
        await page.locator('.container .glass-card input[name="email"]').fill('admin@example.com')
        await page.locator('.container .glass-card input[name="password"]').fill('password123')
        await page.locator('.container .glass-card button[type="submit"]').click()

        await page.goto('http://localhost:8000/admin.php')
        ledger_text = await page.inner_text('table')
        if "referral_commission" in ledger_text and "subsidy_payout" in ledger_text:
            print("Admin ledger correctly reflects system distribution types.")
        else:
            print("Admin ledger might be missing entries.")

        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
