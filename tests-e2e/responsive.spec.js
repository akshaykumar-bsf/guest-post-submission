const { test, expect } = require('@playwright/test');

/**
 * Test suite for responsive behavior of the Guest Post Submission form
 */
test.describe('Guest Post Submission Form Responsive Design', () => {
  // Page with the form
  let formPage = '/guest-post-form/';

  test('should display correctly on desktop', async ({ page }) => {
    // Set viewport to desktop size
    await page.setViewportSize({ width: 1280, height: 800 });
    
    // Navigate to the page with the form
    await page.goto(formPage);
    
    // Wait for the form to be fully loaded
    await page.waitForSelector('#gps-submission-form form', { state: 'visible' });
    
    // Check if the form is displayed correctly
    await expect(page.locator('#gps-submission-form')).toBeVisible();
    
    // Check if the form has appropriate width for desktop
    const formWidth = await page.locator('#gps-submission-form').evaluate(el => {
      return window.getComputedStyle(el).width;
    });
    
    // Convert to number for comparison
    const numericWidth = parseInt(formWidth);
    expect(numericWidth).toBeGreaterThan(500); // Desktop form should be wider than 500px
    
    // Check if name and email fields are side by side (grid layout)
    const nameField = page.locator('#author_name');
    const emailField = page.locator('#author_email');
    
    const nameRect = await nameField.boundingBox();
    const emailRect = await emailField.boundingBox();
    
    // On desktop, email field should be to the right of name field
    expect(emailRect.x).toBeGreaterThan(nameRect.x + nameRect.width / 2);
  });

  test('should display correctly on tablet', async ({ page }) => {
    // Set viewport to tablet size
    await page.setViewportSize({ width: 768, height: 1024 });
    
    // Navigate to the page with the form
    await page.goto(formPage);
    
    // Wait for the form to be fully loaded
    await page.waitForSelector('#gps-submission-form form', { state: 'visible' });
    
    // Check if the form is displayed correctly
    await expect(page.locator('#gps-submission-form')).toBeVisible();
    
    // Check if the form has appropriate width for tablet
    const formWidth = await page.locator('#gps-submission-form').evaluate(el => {
      return window.getComputedStyle(el).width;
    });
    
    // Convert to number for comparison
    const numericWidth = parseInt(formWidth);
    expect(numericWidth).toBeLessThan(1000); // Tablet form should be narrower than 1000px
    
    // Check if name and email fields are still side by side on tablet
    const nameField = page.locator('#author_name');
    const emailField = page.locator('#author_email');
    
    const nameRect = await nameField.boundingBox();
    const emailRect = await emailField.boundingBox();
    
    // On tablet, email field should still be to the right of name field
    expect(emailRect.x).toBeGreaterThan(nameRect.x + nameRect.width / 2);
  });

  test('should display correctly on mobile', async ({ page }) => {
    // Set viewport to mobile size
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Navigate to the page with the form
    await page.goto(formPage);
    
    // Wait for the form to be fully loaded
    await page.waitForSelector('#gps-submission-form form', { state: 'visible' });
    
    // Check if the form is displayed correctly
    await expect(page.locator('#gps-submission-form')).toBeVisible();
    
    // Check if the form has appropriate width for mobile
    const formWidth = await page.locator('#gps-submission-form').evaluate(el => {
      return window.getComputedStyle(el).width;
    });
    
    // Convert to number for comparison
    const numericWidth = parseInt(formWidth);
    expect(numericWidth).toBeLessThan(500); // Mobile form should be narrower than 500px
    
    // Check if name and email fields are stacked vertically on mobile
    const nameField = page.locator('#author_name');
    const emailField = page.locator('#author_email');
    
    const nameRect = await nameField.boundingBox();
    const emailRect = await emailField.boundingBox();
    
    // On mobile, email field should be below name field
    expect(emailRect.y).toBeGreaterThan(nameRect.y + nameRect.height / 2);
  });

  test('should have touch-friendly input sizes on mobile', async ({ page }) => {
    // Set viewport to mobile size
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Navigate to the page with the form
    await page.goto(formPage);
    
    // Wait for the form to be fully loaded
    await page.waitForSelector('#gps-submission-form form', { state: 'visible' });
    
    // Check input field heights to ensure they're touch-friendly
    const inputFields = ['#post_title', '#author_name', '#author_email', '#author_bio', '#featured_image'];
    
    for (const fieldSelector of inputFields) {
      const fieldHeight = await page.locator(fieldSelector).evaluate(el => {
        return window.getComputedStyle(el).height;
      });
      
      // Convert to number for comparison
      const numericHeight = parseInt(fieldHeight);
      
      // Touch-friendly inputs should be at least 40px tall
      expect(numericHeight).toBeGreaterThanOrEqual(40);
    }
    
    // Check submit button size
    const buttonHeight = await page.locator('button[type="submit"]').evaluate(el => {
      return window.getComputedStyle(el).height;
    });
    
    // Convert to number for comparison
    const numericButtonHeight = parseInt(buttonHeight);
    
    // Touch-friendly button should be at least 44px tall
    expect(numericButtonHeight).toBeGreaterThanOrEqual(44);
  });

  test('should have full-width submit button on mobile', async ({ page }) => {
    // Set viewport to mobile size
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Navigate to the page with the form
    await page.goto(formPage);
    
    // Wait for the form to be fully loaded
    await page.waitForSelector('#gps-submission-form form', { state: 'visible' });
    
    // Get the form width
    const formWidth = await page.locator('#gps-submission-form').evaluate(el => {
      return el.clientWidth;
    });
    
    // Get the button width
    const buttonWidth = await page.locator('button[type="submit"]').evaluate(el => {
      return el.clientWidth;
    });
    
    // On mobile, the button should take up most of the form width
    expect(buttonWidth).toBeGreaterThan(formWidth * 0.8);
  });
});
