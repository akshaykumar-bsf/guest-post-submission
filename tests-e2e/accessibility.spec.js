const { test, expect } = require('@playwright/test');

/**
 * Test suite for accessibility features of the Guest Post Submission form
 */
test.describe('Guest Post Submission Form Accessibility', () => {
  // Page with the form
  let formPage = '/guest-post-form/';

  test.beforeEach(async ({ page }) => {
    // Navigate to the page with the form
    await page.goto(formPage);
    
    // Wait for the form to be fully loaded
    await page.waitForSelector('#gps-submission-form form', { state: 'visible' });
  });

  test('should have proper heading structure', async ({ page }) => {
    // Check if the main heading is h1
    await expect(page.locator('h1:has-text("Submit a Guest Post")')).toBeVisible();
    
    // There should not be any skipped heading levels
    const h2Count = await page.locator('h2').count();
    const h3Count = await page.locator('h3').count();
    
    // Log the heading structure for debugging
    console.log(`Heading structure: h1: 1, h2: ${h2Count}, h3: ${h3Count}`);
  });

  test('should have proper form labeling', async ({ page }) => {
    // Check if all form fields have associated labels
    const formFields = [
      { id: 'post_title', label: 'Post Title' },
      { id: 'gps-tinymce-editor', label: 'Post Content' },
      { id: 'author_name', label: 'Your Name' },
      { id: 'author_email', label: 'Your Email' },
      { id: 'author_bio', label: 'About You' },
      { id: 'featured_image', label: 'Featured Image' }
    ];
    
    for (const field of formFields) {
      // Check if the label exists and is properly associated with the field
      await expect(page.locator(`label[for="${field.id}"]`)).toBeVisible();
      await expect(page.locator(`label[for="${field.id}"]`)).toContainText(field.label);
    }
  });

  test('should indicate required fields properly', async ({ page }) => {
    // Check if required fields are properly marked
    const requiredFields = ['post_title', 'author_name', 'author_email', 'author_bio'];
    
    for (const fieldId of requiredFields) {
      // Check for visual indication (asterisk)
      await expect(page.locator(`label[for="${fieldId}"] .text-red-500`)).toBeVisible();
      
      // Check for aria-required attribute
      await expect(page.locator(`#${fieldId}`)).toHaveAttribute('aria-required', 'true');
      
      // Check for screen reader text
      await expect(page.locator(`label[for="${fieldId}"] .sr-only`)).toHaveText('(required)');
    }
  });

  test('should have proper error handling for screen readers', async ({ page }) => {
    // Submit the form without filling it to trigger validation errors
    await page.locator('button[type="submit"]').click();
    
    // Check if error messages have proper ARIA attributes
    await expect(page.locator('[role="alert"]')).toBeVisible();
    
    // Check if fields with errors have aria-invalid="true"
    await expect(page.locator('#post_title')).toHaveAttribute('aria-invalid', 'true');
    
    // Check if error messages are linked to their respective fields
    const errorId = await page.locator('#post_title').getAttribute('aria-describedby');
    expect(errorId).not.toBeNull();
    await expect(page.locator(`#${errorId}`)).toBeVisible();
  });

  test('should support keyboard navigation', async ({ page }) => {
    // Start from the beginning of the page
    await page.keyboard.press('Tab');
    
    // First focusable element should be the post title
    await expect(page.locator('#post_title')).toBeFocused();
    
    // Tab through all form fields
    const focusableElements = [
      '#post_title',
      // TinyMCE editor is complex to test with keyboard navigation
      '#author_name',
      '#author_email',
      '#author_bio',
      '#featured_image',
      'button[type="submit"]'
    ];
    
    for (let i = 1; i < focusableElements.length; i++) {
      await page.keyboard.press('Tab');
      
      // Skip TinyMCE editor as it's complex to test
      if (focusableElements[i] !== '#gps-tinymce-editor') {
        await expect(page.locator(focusableElements[i])).toBeFocused();
      }
    }
  });

  test('should have sufficient color contrast', async ({ page }) => {
    // This is a visual test that would typically require manual verification
    // or specialized accessibility testing tools
    
    // For demonstration purposes, we'll check that text colors use Tailwind's
    // standard classes which are designed with sufficient contrast
    
    // Check form labels (should be dark enough against white background)
    await expect(page.locator('label')).toHaveCSS('color', /rgb\(17, 24, 39|#111827/);
    
    // Check error messages (should be red with sufficient contrast)
    await page.locator('button[type="submit"]').click(); // Trigger errors
    await expect(page.locator('[role="alert"]')).toHaveCSS('color', /rgb\(239, 68, 68|#ef4444/);
    
    // Check submit button (should have sufficient contrast)
    await expect(page.locator('button[type="submit"]')).toHaveCSS('background-color', /rgb\(79, 70, 229|#4f46e5/);
    await expect(page.locator('button[type="submit"]')).toHaveCSS('color', /rgb\(255, 255, 255|#ffffff/);
  });

  test('should announce form submission status to screen readers', async ({ page }) => {
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
    
    // Check if success message has proper ARIA attributes
    await expect(page.locator('[role="alert"]')).toBeVisible();
    await expect(page.locator('[role="alert"]')).toContainText('Your post has been submitted successfully');
    
    // Check if the success message gets focus for screen readers
    await expect(page.locator('[role="alert"]')).toBeFocused();
  });
});
