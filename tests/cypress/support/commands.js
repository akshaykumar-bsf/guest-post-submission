// Custom commands for WordPress testing

// Command to login to WordPress admin
Cypress.Commands.add('wpLogin', (username, password) => {
  cy.visit('/wp-login.php');
  cy.get('#user_login').type(username);
  cy.get('#user_pass').type(password);
  cy.get('#wp-submit').click();
});

// Command to create a test page with shortcode
Cypress.Commands.add('createTestPage', () => {
  cy.wpLogin(Cypress.env('adminUsername'), Cypress.env('adminPassword'));
  cy.visit('/wp-admin/post-new.php?post_type=page');
  cy.get('#title').type('Guest Post Form');
  
  // Add shortcode to content
  cy.get('.wp-editor-tabs .switch-tmce').click();
  cy.get('#content_ifr').then($iframe => {
    const iframe = $iframe.contents();
    iframe.find('body').type('[guest_post_form]');
  });
  
  // Publish the page
  cy.get('#publish').click();
  
  // Get the permalink
  cy.get('#sample-permalink a').invoke('attr', 'href').then(href => {
    Cypress.env('testPage', href);
  });
});
