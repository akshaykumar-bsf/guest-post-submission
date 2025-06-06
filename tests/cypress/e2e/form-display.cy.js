describe('Form Display Tests', () => {
  beforeEach(() => {
    cy.visit(Cypress.env('testPage'));
  });

  it('should display the form with all fields', () => {
    cy.get('#gps-submission-form').should('be.visible');
    cy.get('#post_title').should('be.visible');
    cy.get('#post_content_wrapper').should('be.visible');
    cy.get('#author_name').should('be.visible');
    cy.get('#author_email').should('be.visible');
    cy.get('#author_bio').should('be.visible');
    cy.get('#featured_image').should('be.visible');
    cy.get('button[type="submit"]').should('be.visible');
  });

  it('should be responsive on mobile viewport', () => {
    cy.viewport('iphone-x');
    cy.get('#gps-submission-form').should('be.visible');
    cy.get('.card').should('have.css', 'width').and('match', /100%|auto/);
  });

  it('should have Bootstrap styles applied', () => {
    cy.get('.card').should('have.class', 'shadow-sm');
    cy.get('#post_title').should('have.class', 'form-control');
    cy.get('button[type="submit"]').should('have.class', 'btn-primary');
  });
});
