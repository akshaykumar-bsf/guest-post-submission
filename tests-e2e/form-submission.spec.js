const { test, expect } = require('@playwright/test');

/**
 * Test suite for the Guest Post Submission form
 */
test.describe('Guest Post Submission Form', () => {
  // Page with the form
  let formPage = '/guest-post-form/';

  test.beforeEach(async ({ page }) => {
    // Navigate to the page with the form
    await page.goto(formPage);
    
    // Wait for the form to be fully loaded
    await page.waitForSelector('#gps-submission-form form', { state: 'visible' });
  });

  test('should display the form correctly', async ({ page }) => {
    // Check if the form title is visible
    await expect(page.locator('h1:has-text("Submit a Guest Post")')).toBeVisible();
    
    // Check if all required form fields are present
    await expect(page.locator('#post_title')).toBeVisible();
    await expect(page.locator('#gps-tinymce-editor')).toBeVisible();
    await expect(page.locator('#author_name')).toBeVisible();
    await expect(page.locator('#author_email')).toBeVisible();
    await expect(page.locator('#author_bio')).toBeVisible();
    await expect(page.locator('#featured_image')).toBeVisible();
    
    // Check if the submit button is present
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('should show validation errors for empty required fields', async ({ page }) => {
    // Click the submit button without filling any fields
    await page.locator('button[type="submit"]').click();
    
    // Check if validation errors are displayed
    await expect(page.locator('text=Title is required')).toBeVisible();
    await expect(page.locator('text=Content is required')).toBeVisible();
    await expect(page.locator('text=Name is required')).toBeVisible();
    await expect(page.locator('text=Email is required')).toBeVisible();
  });

  test('should show validation error for invalid email', async ({ page }) => {
    // Fill in all required fields except email
    await page.locator('#post_title').fill('Test Post Title');
    
    // Fill TinyMCE content (this is tricky as it's in an iframe)
    // First check if TinyMCE is initialized
    const isTinyMCEInitialized = await page.evaluate(() => {
      return window.tinymce && window.tinymce.get('gps-tinymce-editor');
    });
    
    if (isTinyMCEInitialized) {
      // If TinyMCE is initialized, set content through TinyMCE API
      await page.evaluate(() => {
        window.tinymce.get('gps-tinymce-editor').setContent('<p>This is a test post content.</p>');
      });
    } else {
      // Fallback to the textarea
      await page.locator('#gps-tinymce-editor').fill('This is a test post content.');
    }
    
    await page.locator('#author_name').fill('Test Author');
    await page.locator('#author_email').fill('invalid-email'); // Invalid email
    await page.locator('#author_bio').fill('This is a test author bio.');
    
    // Submit the form
    await page.locator('button[type="submit"]').click();
    
    // Check if email validation error is displayed
    await expect(page.locator('text=Email is invalid')).toBeVisible();
  });

  test('should successfully submit the form with valid data', async ({ page }) => {
    // Fill in all required fields with valid data
    await page.locator('#post_title').fill('Test Post Title');
    
    // Fill TinyMCE content
    const isTinyMCEInitialized = await page.evaluate(() => {
      return window.tinymce && window.tinymce.get('gps-tinymce-editor');
    });
    
    if (isTinyMCEInitialized) {
      await page.evaluate(() => {
        window.tinymce.get('gps-tinymce-editor').setContent('<p>This is a test post content.</p>');
      });
    } else {
      await page.locator('#gps-tinymce-editor').fill('This is a test post content.');
    }
    
    await page.locator('#author_name').fill('Test Author');
    await page.locator('#author_email').fill('test@example.com');
    await page.locator('#author_bio').fill('This is a test author bio.');
    
    // Mock the AJAX response for form submission
    await page.route('**/wp-admin/admin-ajax.php', async route => {
      const postData = route.request().postData();
      if (postData && postData.includes('action=gps_submit_post')) {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({
            success: true,
            data: {
              message: 'Your post has been submitted successfully and is awaiting review.',
              post_id: 123
            }
          })
        });
      } else {
        await route.continue();
      }
    });
    
    // Submit the form
    await page.locator('button[type="submit"]').click();
    
    // Check if success message is displayed
    await expect(page.locator('text=Your post has been submitted successfully')).toBeVisible();
    
    // Check if form fields are reset
    await expect(page.locator('#post_title')).toHaveValue('');
    await expect(page.locator('#author_name')).toHaveValue('');
    await expect(page.locator('#author_email')).toHaveValue('');
    await expect(page.locator('#author_bio')).toHaveValue('');
  });

  test('should handle form submission errors gracefully', async ({ page }) => {
    // Fill in all required fields with valid data
    await page.locator('#post_title').fill('Test Post Title');
    
    // Fill TinyMCE content
    const isTinyMCEInitialized = await page.evaluate(() => {
      return window.tinymce && window.tinymce.get('gps-tinymce-editor');
    });
    
    if (isTinyMCEInitialized) {
      await page.evaluate(() => {
        window.tinymce.get('gps-tinymce-editor').setContent('<p>This is a test post content.</p>');
      });
    } else {
      await page.locator('#gps-tinymce-editor').fill('This is a test post content.');
    }
    
    await page.locator('#author_name').fill('Test Author');
    await page.locator('#author_email').fill('test@example.com');
    await page.locator('#author_bio').fill('This is a test author bio.');
    
    // Mock the AJAX response for form submission with an error
    await page.route('**/wp-admin/admin-ajax.php', async route => {
      const postData = route.request().postData();
      if (postData && postData.includes('action=gps_submit_post')) {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({
            success: false,
            data: {
              message: 'An error occurred while processing your submission.'
            }
          })
        });
      } else {
        await route.continue();
      }
    });
    
    // Submit the form
    await page.locator('button[type="submit"]').click();
    
    // Check if error message is displayed
    await expect(page.locator('text=An error occurred while processing your submission')).toBeVisible();
  });

  test('should be accessible', async ({ page }) => {
    // Check for basic accessibility attributes
    
    // Form should have an accessible name
    await expect(page.locator('form[aria-label="Guest post submission form"]')).toBeVisible();
    
    // Required fields should have aria-required="true"
    await expect(page.locator('#post_title')).toHaveAttribute('aria-required', 'true');
    await expect(page.locator('#author_name')).toHaveAttribute('aria-required', 'true');
    await expect(page.locator('#author_email')).toHaveAttribute('aria-required', 'true');
    await expect(page.locator('#author_bio')).toHaveAttribute('aria-required', 'true');
    
    // Submit button should be accessible
    const submitButton = page.locator('button[type="submit"]');
    await expect(submitButton).toBeVisible();
    await expect(submitButton).not.toHaveAttribute('aria-hidden', 'true');
    
    // Test keyboard navigation
    await page.keyboard.press('Tab');
    await expect(page.locator('#post_title')).toBeFocused();
    
    await page.keyboard.press('Tab');
    // Focus might go to TinyMCE editor or the next field depending on the setup
    
    // Continue tabbing through the form
    for (let i = 0; i < 5; i++) {
      await page.keyboard.press('Tab');
    }
    
    // Eventually, the submit button should get focus
    await expect(submitButton).toBeFocused();
  });
});
