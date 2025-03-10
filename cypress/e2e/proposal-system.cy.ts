describe('Plura Proposals', () => {
  before(() => {
    // Login to Nextcloud once before all tests
    cy.loginToNextcloud('admin', 'admin');
  });

  beforeEach(() => {
    // Ensure we're logged in before each test
    cy.loginToNextcloud('admin', 'admin');

    // Visit the Plura app's proposals page directly
    cy.visit('/index.php/apps/plura/#/proposals');

    // Ensure the page has loaded
    cy.contains('h1', 'Proposals', { timeout: 10000 }).should('be.visible');
  });

  it('should display the proposals list or empty state', () => {
    // Check if either the proposals list or empty state is displayed
    cy.get('body').then($body => {
      if ($body.find('.proposals-empty').length > 0) {
        // If empty state is shown, verify it has the expected content
        cy.get('.proposals-empty').should('be.visible');
        cy.contains('No proposals yet').should('be.visible');
      } else {
        // If proposals exist, verify the list is shown
        cy.get('.proposals-list').should('be.visible');
      }
    });
  });

  it('should allow creating a new proposal', () => {
    // Click on the new proposal button
    cy.get('[data-cy="new-proposal-button"]').should('be.visible').click();

    // Fill in the proposal form
    cy.get('[data-cy="proposal-title-input"]').should('be.visible').type('Test Proposal');
    cy.get('[data-cy="proposal-description-input"]').should('be.visible')
      .type('This is a test proposal created by Cypress');

    // Select a document from the dropdown
    cy.get('[data-cy="document-selector"]').should('be.visible').select('1');

    // Set a deadline (2 weeks from today)
    const date = new Date();
    date.setDate(date.getDate() + 14);
    const formattedDate = date.toISOString().split('T')[0];
    cy.get('[data-cy="proposal-deadline"]').should('be.visible').type(formattedDate);

    // Submit the form
    cy.get('[data-cy="proposal-submit-button"]').click();

    // Verify the proposal was created
    cy.contains('.proposal-item', 'Test Proposal').should('exist');
    cy.contains('.proposal-item', 'This is a test proposal').should('exist');
  });

  // This test creates a proposal and then tests viewing details and allocating credits
  it('should create, view details, and allocate credits to a proposal', () => {
    // Start by creating a proposal
    cy.get('[data-cy="new-proposal-button"]').should('be.visible').click();

    // Fill in the proposal form
    cy.get('[data-cy="proposal-title-input"]').should('be.visible').type('Test Details Proposal');
    cy.get('[data-cy="proposal-description-input"]').should('be.visible')
      .type('This is a test proposal for viewing details and allocating credits');

    // Select a document from the dropdown
    cy.get('[data-cy="document-selector"]').should('be.visible').select('1');

    // Set a deadline (2 weeks from today)
    const date = new Date();
    date.setDate(date.getDate() + 14);
    const formattedDate = date.toISOString().split('T')[0];
    cy.get('[data-cy="proposal-deadline"]').should('be.visible').type(formattedDate);

    // Submit the form
    cy.get('[data-cy="proposal-submit-button"]').click();

    // Verify the proposal was created and find it in the list
    cy.contains('.proposal-item', 'Test Details Proposal').should('exist').click();

    // TEST PART 2: Viewing proposal details
    // Verify proposal details are shown
    cy.get('.proposal-details').should('be.visible');
    cy.contains('h2', 'Test Details Proposal').should('be.visible');
    cy.get('.proposal-description-text').should('be.visible');
    cy.get('.proposal-meta').should('be.visible');
    cy.get('.proposal-deadline').should('be.visible');
    cy.get('.proposal-document').should('be.visible');
    cy.get('.proposal-creator').should('be.visible');

    // TEST PART 3: Allocating credits
    // Get user's initial credit balance
    let initialBalance: number;
    cy.get('[data-cy="user-credit-balance"]')
      .invoke('text')
      .then((text) => {
        initialBalance = parseFloat(text.replace(/[^0-9.-]+/g, ''));
      });

    // Find the credit allocation input
    cy.get('[data-cy="credit-allocation-input"]').should('be.visible').clear().type('5');

    // Submit allocation
    cy.get('[data-cy="allocate-credits-button"]').click();

    // Verify priority calculation display becomes visible
    cy.get('.proposal-priority').should('be.visible');
    cy.get('.raw-credits').should('be.visible');
    cy.get('.quadratic-value').should('be.visible');
    cy.get('.matching-fund-bonus').should('be.visible');
    cy.get('.total-priority-score').should('be.visible');

    // Check that user's credit balance was reduced
    cy.get('[data-cy="user-credit-balance"]')
      .invoke('text')
      .then((text) => {
        const newBalance = parseFloat(text.replace(/[^0-9.-]+/g, ''));
        expect(newBalance).to.be.lessThan(initialBalance);
      });
  });

  // This test only runs if we have at least 2 proposals to test sorting
  it('should allow sorting proposals if multiple proposals exist', () => {
    // Create another proposal to ensure we have at least 2 for sorting
    cy.get('[data-cy="new-proposal-button"]').should('be.visible').click();

    // Fill in the proposal form
    cy.get('[data-cy="proposal-title-input"]').should('be.visible').type('Second Sort Test Proposal');
    cy.get('[data-cy="proposal-description-input"]').should('be.visible')
      .type('This is a second test proposal for testing sorting functionality');

    // Select a document
    cy.get('[data-cy="document-selector"]').should('be.visible').select('1');

    // Set a deadline (1 week from today - different from the other proposal)
    const date = new Date();
    date.setDate(date.getDate() + 7); // 1 week instead of 2
    const formattedDate = date.toISOString().split('T')[0];
    cy.get('[data-cy="proposal-deadline"]').should('be.visible').type(formattedDate);

    // Submit the form
    cy.get('[data-cy="proposal-submit-button"]').click();

    // Verify we now have at least 2 proposals
    cy.get('.proposal-item').should('have.length.at.least', 2).then(() => {
      // Now test sorting functions

      // Test sorting by newest
      cy.get('[data-cy="sort-select"]').select('created_at');

      // Don't test exact timestamp values, just verify the component structure
      cy.get('.proposal-created-at').should('have.length.at.least', 2);

      // Test sorting by deadline
      cy.get('[data-cy="sort-select"]').select('deadline');
      cy.get('.proposal-deadline').should('have.length.at.least', 2);

      // Test sorting by priority
      cy.get('[data-cy="sort-select"]').select('priority');
      cy.get('.proposal-priority-score').should('have.length.at.least', 2);
    });
  });
});