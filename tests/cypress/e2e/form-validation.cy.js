describe('Form Validation Tests', () => {
  beforeEach(() => {
    cy.visit(Cypress.env('testPage'));
  });

  it('should show validation errors for empty fields', () => {
    cy.get('button[type="submit"]').click();
    
    // Check for validation errors
    cy.get('#post_title').should('have.class', 'is-invalid');
    cy.get('#author_name').should('have.class', 'is-invalid');
    cy.get('#author_email').should('have.class', 'is-invalid');
    cy.get('#author_bio').should('have.class', 'is-invalid');
  });

  it('should validate email format', () => {
    cy.get('#post_title').type('Test Post');
    cy.get('#author_name').type('Test Author');
    cy.get('#author_email').type('invalid-email');
    cy.get('#author_bio').type('Test Bio');
    
    // Try to submit
    cy.get('button[type="submit"]').click();
    
    // Check for email validation error
    cy.get('#author_email').should('have.class', 'is-invalid');
  });

  it('should validate content length', () => {
    cy.get('#post_title').type('Test Post');
    
    // Type short content in TinyMCE
    cy.get('iframe.tox-edit-area__iframe').then($iframe => {
      const iframe = $iframe.contents();
      iframe.find('body').type('Too short');
    });
    
    cy.get('#author_name').type('Test Author');
    cy.get('#author_email').type('test@example.com');
    cy.get('#author_bio').type('Test Bio');
    
    // Try to submit
    cy.get('button[type="submit"]').click();
    
    // Check for content length error
    cy.get('#post_content_wrapper').should('have.class', 'border-danger');
  });

  it('should show real-time validation on blur', () => {
    cy.get('#post_title').focus().blur();
    cy.get('#post_title').should('have.class', 'is-invalid');
    
    cy.get('#author_email').type('invalid-email').blur();
    cy.get('#author_email').should('have.class', 'is-invalid');
    
    // Fix the email and check validation passes
    cy.get('#author_email').clear().type('valid@example.com').blur();
    cy.get('#author_email').should('not.have.class', 'is-invalid');
  });
});
