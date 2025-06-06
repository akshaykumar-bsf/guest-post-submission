describe('Form Submission Tests', () => {
  beforeEach(() => {
    cy.visit(Cypress.env('testPage'));
  });

  it('should submit form with valid data', () => {
    // Fill out the form
    cy.get('#post_title').type('E2E Test Post');
    
    // Type content in TinyMCE
    cy.get('iframe.tox-edit-area__iframe').then($iframe => {
      const iframe = $iframe.contents();
      iframe.find('body').type('This is a test post created during E2E testing. It needs to be long enough to pass the validation requirements for the guest post submission form. This should be more than 100 characters.');
    });
    
    cy.get('#author_name').type('E2E Test Author');
    cy.get('#author_email').type('e2e-test@example.com');
    cy.get('#author_bio').type('This is an automated test submission.');
    
    // Submit the form
    cy.get('button[type="submit"]').click();
    
    // Check for success message
    cy.get('#gps-form-success').should('be.visible');
    cy.get('#gps-form-success').should('contain', 'Thanks for your submission');
    
    // Form should be reset
    cy.get('#post_title').should('have.value', '');
  });

  it('should show loading indicator during submission', () => {
    // Intercept AJAX request to delay it
    cy.intercept('POST', '**/admin-ajax.php', (req) => {
      req.reply((res) => {
        res.delay = 1000;
      });
    }).as('formSubmission');
    
    // Fill out the form
    cy.get('#post_title').type('Loading Test Post');
    
    // Type content in TinyMCE
    cy.get('iframe.tox-edit-area__iframe').then($iframe => {
      const iframe = $iframe.contents();
      iframe.find('body').type('This is a test post created during E2E testing. It needs to be long enough to pass the validation requirements for the guest post submission form. This should be more than 100 characters.');
    });
    
    cy.get('#author_name').type('Loading Test Author');
    cy.get('#author_email').type('loading-test@example.com');
    cy.get('#author_bio').type('This is an automated test submission.');
    
    // Submit the form
    cy.get('button[type="submit"]').click();
    
    // Loading indicator should be visible
    cy.get('#gps-form-loading').should('be.visible');
    
    // Wait for submission to complete
    cy.wait('@formSubmission');
    
    // Loading indicator should be hidden
    cy.get('#gps-form-loading').should('not.be.visible');
  });
});
