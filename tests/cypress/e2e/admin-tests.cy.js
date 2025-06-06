describe('Admin Area Tests', () => {
  beforeEach(() => {
    // Login to WordPress admin
    cy.visit('/wp-login.php');
    cy.get('#user_login').type(Cypress.env('adminUsername'));
    cy.get('#user_pass').type(Cypress.env('adminPassword'));
    cy.get('#wp-submit').click();
  });

  it('should display plugin settings page', () => {
    cy.visit('/wp-admin/options-general.php?page=guest-post-settings');
    cy.get('h1').should('contain', 'Guest Post Submission Settings');
    cy.get('select[name="gps_settings[default_category]"]').should('exist');
    cy.get('input[name="gps_settings[ip_submission_limit]"]').should('exist');
    cy.get('input[name="gps_settings[notification_email]"]').should('exist');
  });

  it('should save settings changes', () => {
    cy.visit('/wp-admin/options-general.php?page=guest-post-settings');
    
    // Change IP limit
    cy.get('input[name="gps_settings[ip_submission_limit]"]').clear().type('5');
    
    // Save changes
    cy.get('input[name="submit"]').click();
    
    // Verify success message
    cy.get('.notice-success').should('be.visible');
    
    // Verify value was saved
    cy.get('input[name="gps_settings[ip_submission_limit]"]').should('have.value', '5');
  });

  it('should show guest post meta box for submissions', () => {
    // Assuming we have a guest post with ID 1
    cy.visit('/wp-admin/post.php?post=1&action=edit');
    
    // Check if meta box exists
    cy.get('#gps_guest_post_info').should('exist');
    cy.get('#gps_author_name').should('exist');
    cy.get('#gps_author_email').should('exist');
    cy.get('#gps_author_bio').should('exist');
  });

  it('should approve a pending guest post', () => {
    // Go to pending posts
    cy.visit('/wp-admin/edit.php?post_status=pending');
    
    // Find a guest post and click to edit
    cy.contains('tr', 'E2E Test Post').find('a.row-title').click();
    
    // Change status to publish
    cy.get('#publish').click();
    
    // Verify success message
    cy.get('#message').should('contain', 'Post updated');
    
    // Verify status is now published
    cy.get('#post-status-display').should('contain', 'Published');
  });
});
