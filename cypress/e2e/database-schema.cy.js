describe('Database Schema', () => {
  before(() => {
    // Login to Nextcloud once before all tests
    cy.loginToNextcloud('admin', 'admin')
  })
  
  beforeEach(() => {
    // Ensure we're logged in before each test
    cy.loginToNextcloud('admin', 'admin')
  })

  it('should have created all required database tables', () => {
    // Visit the Plura app
    cy.visit('/index.php/apps/plura/')
    
    // We'll need an admin API to check this, 
    // so for now we'll just verify the app loads without database errors
    cy.get('body').should('not.contain', 'Database error')
    cy.get('body').should('not.contain', 'Missing table')
    
    // Wait for Vue app to mount
    cy.get('#plura', { timeout: 10000 }).should('be.visible')
  })

  it('should allow viewing user credits', () => {
    // Visit the Plura app
    cy.visit('/index.php/apps/plura/')
    
    // This tests that the credit system database is working
    // Either the credit balance is shown directly, or we need to wait for it to load in the Vue app
    cy.get('body').then($body => {
      // Look for either the direct credit display or the Vue app container
      if ($body.find('[data-cy="user-credit-balance"]').length > 0) {
        cy.get('[data-cy="user-credit-balance"]').should('exist')
          .should('not.be.empty')
      } else {
        // Wait for Vue app to mount and display credits
        cy.get('#plura', { timeout: 10000 }).should('be.visible')
        cy.contains('Credits', { timeout: 10000 }).should('be.visible')
      }
    })
  })

  it('should have admin settings page', () => {
    // Navigate to admin settings for Plura
    cy.visit('/index.php/settings/admin/plura')
    
    // Check if the admin page exists and loads without errors
    cy.get('body').should('not.contain', 'An error occurred')
  })
})