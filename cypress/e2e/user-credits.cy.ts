describe('Plura User Credits', () => {
  beforeEach(() => {
    // Visit the Plura app page before each test
    cy.visit('/index.php/apps/plura/');
  });

  it('should display user credit balance', () => {
    // Credit balance should be visible in the UI
    cy.get('[data-cy="user-credit-balance"]').should('be.visible');
    cy.get('[data-cy="user-credit-balance"]').should('not.be.empty');
  });

  it('should show credit transaction history', () => {
    // Navigate to credit history page
    cy.get('[data-cy="credit-history-link"]').click();
    
    // Verify transaction history is displayed
    cy.get('.transaction-history').should('be.visible');
    cy.get('.transaction-item').should('exist');
  });

  it('should display proper transaction details', () => {
    // Navigate to credit history page
    cy.get('[data-cy="credit-history-link"]').click();
    
    // Open a transaction detail
    cy.get('.transaction-item').first().click();
    
    // Verify transaction details are shown
    cy.get('.transaction-details').should('be.visible');
    cy.get('.transaction-amount').should('be.visible');
    cy.get('.transaction-date').should('be.visible');
    cy.get('.transaction-type').should('be.visible');
    cy.get('.transaction-related-entity').should('exist');
  });

  it('should update credit balance after allocation', () => {
    // Get initial credit balance
    let initialBalance: number;
    cy.get('[data-cy="user-credit-balance"]')
      .invoke('text')
      .then((text) => {
        initialBalance = parseFloat(text.replace(/[^0-9.-]+/g, ''));
      });
    
    // Navigate to a proposal
    cy.get('.proposal-item').first().click();
    
    // Allocate credits (using a small amount to avoid test failures)
    cy.get('[data-cy="credit-allocation-input"]').clear().type('1');
    cy.get('[data-cy="allocate-credits-button"]').click();
    
    // Navigate back to main page
    cy.visit('/index.php/apps/plura/');
    
    // Check that balance has been reduced
    cy.get('[data-cy="user-credit-balance"]')
      .invoke('text')
      .then((text) => {
        const newBalance = parseFloat(text.replace(/[^0-9.-]+/g, ''));
        expect(newBalance).to.be.lessThan(initialBalance);
      });
  });
});