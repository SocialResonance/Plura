describe('Plura Proposals', () => {
  beforeEach(() => {
    // Login to Nextcloud
    cy.loginToNextcloud()
    
    // Visit the Plura app page and navigate to proposals
    cy.visit('/index.php/apps/plura/')
    cy.get('[data-to*="proposals"]').click()
  })

  it('should display the proposals list when available', () => {
    // Test that the proposals list container is visible
    cy.get('.proposals-list').should('be.visible')
  })

  it('should allow creating a new proposal', () => {
    // Click on the new proposal button
    cy.get('[data-cy="new-proposal-button"]').should('be.visible').click()
    
    // Fill in the proposal form
    cy.get('[data-cy="proposal-title-input"]').should('be.visible').type('Test Proposal')
    cy.get('[data-cy="proposal-description-input"]').should('be.visible')
      .type('This is a test proposal created by Cypress')
    
    // Select a document from the dropdown
    cy.get('[data-cy="document-selector"]').should('be.visible').click()
    cy.get('.document-option').first().click()
    
    // Set a deadline (2 weeks from today)
    const date = new Date()
    date.setDate(date.getDate() + 14)
    const formattedDate = date.toISOString().split('T')[0]
    cy.get('[data-cy="proposal-deadline"]').should('be.visible').type(formattedDate)
    
    // Submit the form
    cy.get('[data-cy="proposal-submit-button"]').click()
    
    // Verify the proposal was created
    cy.contains('.proposal-item', 'Test Proposal').should('exist')
    cy.contains('.proposal-item', 'This is a test proposal').should('exist')
  })

  it('should show proposal details when clicked', () => {
    // Find a proposal and click on it
    cy.get('.proposal-item').first().click()
    
    // Verify proposal details are shown
    cy.get('.proposal-details').should('be.visible')
    cy.get('.proposal-title').should('be.visible')
    cy.get('.proposal-description').should('be.visible')
    cy.get('.proposal-credits').should('be.visible')
    cy.get('.proposal-deadline').should('be.visible')
    cy.get('.proposal-document').should('be.visible')
    cy.get('.proposal-creator').should('be.visible')
  })

  it('should allow allocating credits to a proposal', () => {
    // Open a proposal
    cy.get('.proposal-item').first().click()
    
    // Get user's initial credit balance
    let initialBalance
    cy.get('[data-cy="user-credit-balance"]')
      .invoke('text')
      .then((text) => {
        initialBalance = parseFloat(text.replace(/[^0-9.-]+/g, ''))
      })
    
    // Find the credit allocation input
    cy.get('[data-cy="credit-allocation-input"]').should('be.visible').clear().type('10')
    
    // Submit allocation
    cy.get('[data-cy="allocate-credits-button"]').click()
    
    // Verify credits were allocated to the proposal
    cy.get('.proposal-credits').should('contain', '10')
    
    // Check that user's credit balance was reduced
    cy.get('[data-cy="user-credit-balance"]')
      .invoke('text')
      .then((text) => {
        const newBalance = parseFloat(text.replace(/[^0-9.-]+/g, ''))
        expect(newBalance).to.be.lessThan(initialBalance)
      })
    
    // Verify that a transaction was created
    cy.visit('/index.php/apps/plura/credits')
    cy.contains('.transaction-item', 'Proposal Funding').should('exist')
  })

  it('should display proposal priority based on quadratic funding formula', () => {
    // Check the proposal listing shows priority scores
    cy.get('.proposal-priority-score').should('exist')
    
    // Open a proposal
    cy.get('.proposal-item').first().click()
    
    // Verify priority calculation display
    cy.get('.proposal-priority').should('be.visible')
    cy.get('.raw-credits').should('be.visible')
    cy.get('.quadratic-value').should('be.visible')
    cy.get('.matching-fund-bonus').should('be.visible')
    cy.get('.total-priority-score').should('be.visible')
  })

  it('should list proposals by priority by default', () => {
    // Get priority values from the first few proposals
    let priorities = []
    cy.get('.proposal-priority-score')
      .each(($el) => {
        const priority = parseFloat($el.text())
        priorities.push(priority)
      })
      .then(() => {
        // Check if priorities are in descending order
        for (let i = 1; i < priorities.length; i++) {
          expect(priorities[i-1]).to.be.at.least(priorities[i])
        }
      })
  })

  it('should allow sorting proposals by different criteria', () => {
    // Test sorting by newest
    cy.get('[data-cy="sort-by-newest"]').click()
    cy.get('.proposal-created-at').first().invoke('attr', 'data-timestamp').then(timestamp => {
      cy.get('.proposal-created-at').eq(1).invoke('attr', 'data-timestamp').should('be.lte', timestamp)
    })
    
    // Test sorting by deadline (soonest first)
    cy.get('[data-cy="sort-by-deadline"]').click()
    cy.get('.proposal-deadline').first().invoke('attr', 'data-timestamp').then(timestamp => {
      cy.get('.proposal-deadline').eq(1).invoke('attr', 'data-timestamp').should('be.gte', timestamp)
    })
    
    // Test sorting by priority (default)
    cy.get('[data-cy="sort-by-priority"]').click()
    cy.get('.proposal-priority-score').first().invoke('text').then(priority => {
      cy.get('.proposal-priority-score').eq(1).invoke('text').then(nextPriority => {
        expect(parseFloat(priority)).to.be.at.least(parseFloat(nextPriority))
      })
    })
  })
})