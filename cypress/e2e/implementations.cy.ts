describe('Plura Implementations', () => {
  beforeEach(() => {
    // Visit a proposal page that has implementations
    cy.visit('/index.php/apps/plura/');
    // Navigate to a proposal with implementations
    cy.get('.proposal-item').first().click();
  });

  it('should display implementations for a proposal', () => {
    // Check that implementations section exists
    cy.get('.implementations-section').should('be.visible');
    cy.get('.implementation-item').should('exist');
  });

  it('should allow submitting a new implementation', () => {
    // Click on the new implementation button
    cy.get('[data-cy="new-implementation-button"]').should('be.visible').click();
    
    // Fill in the implementation content
    cy.get('[data-cy="implementation-content-input"]').should('be.visible')
      .type('This is a test implementation content created by Cypress');
    
    // Submit the implementation
    cy.get('[data-cy="implementation-submit-button"]').click();
    
    // Verify the implementation was created
    cy.contains('.implementation-item', 'This is a test implementation').should('exist');
  });

  it('should allow voting on implementations', () => {
    // Find an implementation
    cy.get('.implementation-item').first().within(() => {
      // Check voting controls are visible
      cy.get('[data-cy="vote-controls"]').should('be.visible');
      
      // Cast a vote
      cy.get('[data-cy="upvote-button"]').click();
      
      // Verify vote was registered
      cy.get('[data-cy="vote-count"]').should('not.contain', '0');
    });
  });

  it('should allow predicting vote outcomes', () => {
    // Find an implementation
    cy.get('.implementation-item').first().within(() => {
      // Enable prediction mode
      cy.get('[data-cy="prediction-toggle"]').should('be.visible').click();
      
      // Set prediction amount
      cy.get('[data-cy="prediction-amount-input"]').should('be.visible').clear().type('5');
      
      // Submit prediction
      cy.get('[data-cy="submit-prediction-button"]').click();
      
      // Verify prediction was made
      cy.get('[data-cy="prediction-status"]').should('contain', 'Predicted');
    });
  });
});