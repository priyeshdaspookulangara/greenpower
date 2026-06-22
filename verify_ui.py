import asyncio
from playwright.async_api import async_playwright
import os

async def capture_ui():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page()

        # Base URL - assuming it's serving from the current dir
        # We need a server. Let's use php built-in server.

        os.system("php -S localhost:8001 > php_server.log 2>&1 &")
        await asyncio.sleep(2) # Wait for server

        # Test Index
        await page.goto("http://localhost:8001/index.php")
        await page.screenshot(path="verification/screenshots/index_glass.png", full_page=True)

        # Test Login
        await page.goto("http://localhost:8001/login.php")
        await page.screenshot(path="verification/screenshots/login_glass.png")

        # Test Admin (if possible without login bypass, otherwise we'll just check public)
        # We can bypass auth for verification by setting a session or mocking.
        # But even just index and login will confirm the theme.

        print("Screenshots captured.")

        os.system("kill $(lsof -t -i :8001)")
        await browser.close()

if __name__ == "__main__":
    asyncio.run(capture_ui())
