describe('Plura App', () => {
  beforeEach(() => {
    // Login before each test using our enhanced login command
    cy.loginToNextcloud();

    // Navigate to the Plura app
    cy.goToPluraApp();
  });

  it('should load the app interface correctly', () => {
    // Test that the app loads properly
    cy.get('#app-content').should('be.visible');
    cy.get('#app-navigation').should('be.visible');
    cy.contains('h1', 'Plura').should('be.visible');
  });
});