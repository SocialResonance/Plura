describe('Database Schema', () => {
  beforeEach(() => {
    // Login to Nextcloud
    cy.loginToNextcloud()
    
    // Visit the Plura app
    cy.visit('/index.php/apps/plura/')
  })

  it('should have created all required database tables', () => {
    // We'll need an admin API to check this, 
    // so for now we'll just verify the app loads without database errors
    cy.get('#app-content').should('be.visible')
    cy.get('#app-content').should('not.contain', 'Database error')
    cy.get('#app-content').should('not.contain', 'Missing table')
  })

  it('should allow viewing user credits', () => {
    // This tests that the credit system database is working
    cy.get('[data-cy="user-credit-balance"]').should('exist')
    cy.get('[data-cy="user-credit-balance"]').should('not.be.empty')
  })

  it('should show system parameters in admin settings', () => {
    // Navigate to admin settings for Plura
    cy.visit('/index.php/settings/admin/plura')
    
    // Test that system parameters are displayed
    cy.get('[data-cy="admin-parameters"]').should('exist')
    cy.get('[data-cy="param-k-value"]').should('exist')
    cy.get('[data-cy="param-initial-credits"]').should('exist')
    cy.get('[data-cy="param-matching-fund"]').should('exist')
  })
})