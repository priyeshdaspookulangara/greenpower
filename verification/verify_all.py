from playwright.sync_api import sync_playwright
import os

def run_verification(page, url, screenshot_name, user=None, password=None):
    print(f"Navigating to {url} for {screenshot_name}...")
    page.goto(url)
    page.wait_for_timeout(2000)

    if user and password:
        print(f"Logging in as {user}...")
        page.fill('input[name="email"]', user)
        page.fill('input[name="password"]', password)
        page.click('button[type="submit"]')
        page.wait_for_timeout(2000)

    path = f"verification/screenshots/{screenshot_name}.png"
    page.screenshot(path=path, full_page=True)
    print(f"Screenshot saved to {path}")

if __name__ == "__main__":
    os.makedirs("verification/screenshots", exist_ok=True)
    os.makedirs("verification/videos", exist_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)

        # Home Page
        context = browser.new_context()
        page = context.new_page()
        run_verification(page, "http://localhost:8000/index.php", "index_final")
        context.close()

        # Admin Dashboard
        context = browser.new_context()
        page = context.new_page()
        run_verification(page, "http://localhost:8000/login.php", "admin_final", "admin@example.com", "password")
        context.close()

        # User Dashboard
        context = browser.new_context()
        page = context.new_page()
        run_verification(page, "http://localhost:8000/login.php", "dashboard_final", "customer@example.com", "password")
        context.close()

        browser.close()
