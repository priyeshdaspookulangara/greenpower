import asyncio
from playwright.async_api import async_playwright
import os
import random

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        context = await browser.new_context(viewport={'width': 1280, 'height': 800})
        page = await context.new_page()

        email = f"tester_{random.randint(1000, 9999)}@example.com"
        password = "password123"

        # 1. Register a new user
        print(f"Registering new user: {email}...")
        await page.goto('http://localhost:8000/register.php')
        await page.locator('.container .glass-card input[name="name"]').fill('Visual Tester')
        await page.locator('.container .glass-card input[name="email"]').fill(email)
        await page.locator('.container .glass-card input[name="password"]').fill(password)
        await page.locator('.container .glass-card select[name="cibil_status"]').select_option('good')
        await page.locator('.container .glass-card button[type="submit"]').click()
        await page.wait_for_url('**/login.php*')
        print("Registration success, redirected to login.")

        # 2. Login
        print("Logging in...")
        await page.locator('.container .glass-card input[name="email"]').fill(email)
        await page.locator('.container .glass-card input[name="password"]').fill(password)
        await page.locator('.container .glass-card button[type="submit"]').click()
        await page.wait_for_url('**/index.php*')
        print("Login success.")

        # 3. Dashboard
        print("Visiting Dashboard...")
        await page.goto('http://localhost:8000/dashboard.php')
        await asyncio.sleep(1)
        await page.screenshot(path='/home/jules/verification/screenshots/final_dashboard.png')

        # 4. Product Details
        print("Visiting Product Details...")
        await page.goto('http://localhost:8000/product.php?id=1')
        await asyncio.sleep(1)
        await page.screenshot(path='/home/jules/verification/screenshots/final_product_details.png')

        # 5. Add to cart
        print("Adding to cart...")
        await page.click('button:has-text("Add to Shopping Cart")')
        await page.wait_for_url('**/cart.php*')
        await page.screenshot(path='/home/jules/verification/screenshots/final_cart.png')

        # 6. Checkout page
        print("Visiting Checkout...")
        await page.goto('http://localhost:8000/checkout.php')
        await asyncio.sleep(1)
        await page.screenshot(path='/home/jules/verification/screenshots/final_checkout.png')

        print("All screenshots captured.")
        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
