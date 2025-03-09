describe('Plura Proposals', () => {
  beforeEach(() => {
    // Visit the Plura app page before each test
    cy.visit('/index.php/apps/plura/')
    
    // Add any authentication handling if needed
  })

  it('should display the proposals list when available', () => {
    // Test that the proposals list is visible
    cy.get('.proposals-list').should('exist')
  })

  it('should allow creating a new proposal', () => {
    // Click on the new proposal button
    cy.get('[data-cy="new-proposal-button"]').should('be.visible').click()
    
    // Fill in the proposal form
    cy.get('[data-cy="proposal-title-input"]').should('be.visible').type('Test Proposal')
    cy.get('[data-cy="proposal-description-input"]').should('be.visible')
      .type('This is a test proposal created by Cypress')
    
    // Submit the form
    cy.get('[data-cy="proposal-submit-button"]').click()
    
    // Verify the proposal was created
    cy.contains('.proposal-item', 'Test Proposal').should('exist')
  })

  it('should show proposal details when clicked', () => {
    // Find a proposal and click on it
    cy.get('.proposal-item').first().click()
    
    // Verify proposal details are shown
    cy.get('.proposal-details').should('be.visible')
    cy.get('.proposal-title').should('be.visible')
    cy.get('.proposal-description').should('be.visible')
    cy.get('.proposal-credits').should('be.visible')
  })

  it('should allow allocating credits to a proposal', () => {
    // Open a proposal
    cy.get('.proposal-item').first().click()
    
    // Find the credit allocation input
    cy.get('[data-cy="credit-allocation-input"]').should('be.visible').clear().type('10')
    
    // Submit allocation
    cy.get('[data-cy="allocate-credits-button"]').click()
    
    // Verify credits were allocated
    cy.get('.proposal-credits').should('contain', '10')
  })
})