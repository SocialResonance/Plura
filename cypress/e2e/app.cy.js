describe('Plura App', () => {
  beforeEach(() => {
    // Visit the Plura app page before each test
    cy.visit('/index.php/apps/plura/')
    
    // Add any authentication handling if needed
    // This might be necessary depending on your Nextcloud setup
  })

  it('should load the app interface correctly', () => {
    // Test that the app loads properly
    cy.get('#app-content').should('be.visible')
    cy.get('#app-navigation').should('be.visible')
    cy.contains('h1', 'Plura').should('be.visible')
  })
})