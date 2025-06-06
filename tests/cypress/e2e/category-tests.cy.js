describe('Category Tests', () => {
  beforeEach(() => {
    // Login to WordPress admin
    cy.visit('/wp-login.php');
    cy.get('#user_login').type(Cypress.env('adminUsername'));
    cy.get('#user_pass').type(Cypress.env('adminPassword'));
    cy.get('#wp-submit').click();
  });

  it('should have created Submissions category on activation', () => {
    cy.visit('/wp-admin/edit-tags.php?taxonomy=category');
    cy.contains('tr', 'Submissions').should('exist');
  });

  it('should assign guest posts to Submissions category', () => {
    // Go to posts list
    cy.visit('/wp-admin/edit.php');
    
    // Find a guest post and click to edit
    cy.contains('tr', 'E2E Test Post').find('a.row-title').click();
    
    // Check if Submissions category is checked
    cy.get('#categorychecklist input[type="checkbox"]').then($checkboxes => {
      // Find the checkbox for Submissions category
      const submissionsCheckbox = $checkboxes.filter((i, el) => {
        return Cypress.$(el).next('label').text().includes('Submissions');
      });
      
      expect(submissionsCheckbox).to.be.checked;
    });
  });

  it('should allow changing default category in settings', () => {
    cy.visit('/wp-admin/options-general.php?page=guest-post-settings');
    
    // Select a different category
    cy.get('select[name="gps_settings[default_category]"]').select('Uncategorized');
    
    // Save changes
    cy.get('input[name="submit"]').click();
    
    // Verify success message
    cy.get('.notice-success').should('be.visible');
    
    // Verify value was saved
    cy.get('select[name="gps_settings[default_category]"]').should('have.value', '1'); // Assuming Uncategorized has ID 1
  });
});
