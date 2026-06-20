import asyncio
from playwright.async_api import async_playwright
import os

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        context = await browser.new_context(viewport={'width': 1280, 'height': 800})
        page = await context.new_page()

        await page.goto('http://localhost:8000/index.php')
        # Verify Burfee Login link is prominent in header
        burfee_link = page.locator('text=BURFEE LOGIN')
        await asyncio.sleep(1)
        await page.screenshot(path='/home/jules/verification/screenshots/header_with_burfee.png')

        if await burfee_link.is_visible():
            print("BURFEE LOGIN LINK: VISIBLE")
            await burfee_link.click()
            await page.wait_for_url('**/burfee_login.php*')
            print("BURFEE NAVIGATION: SUCCESS")
        else:
            print("BURFEE LOGIN LINK: NOT FOUND")

        await browser.close()

if __name__ == '__main__':
    asyncio.run(run())
