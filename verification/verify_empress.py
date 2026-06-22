from playwright.sync_api import sync_playwright
import os

def run_cuj(page):
    # Navigate to Home Page
    page.goto("http://localhost:8000/index.php")
    page.wait_for_timeout(1000)
    page.screenshot(path="/app/verification/screenshots/index_empress.png", full_page=True)
    print("Home Page screenshot captured.")

    # Navigate to Login Page
    page.goto("http://localhost:8000/login.php")
    page.wait_for_timeout(500)
    page.screenshot(path="/app/verification/screenshots/login_empress.png")
    print("Login Page screenshot captured.")

if __name__ == "__main__":
    os.makedirs("/app/verification/screenshots", exist_ok=True)
    os.makedirs("/app/verification/videos", exist_ok=True)
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            record_video_dir="/app/verification/videos"
        )
        page = context.new_page()
        try:
            run_cuj(page)
        finally:
            context.close()
            browser.close()
