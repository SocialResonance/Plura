// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

// Login to Nextcloud (adjust username/password as needed)
Cypress.Commands.add('loginToNextcloud', (username = 'admin', password = 'admin') => {
  cy.visit('/')
  
  // Check if we're already logged in
  cy.url().then(url => {
    if (url.includes('/index.php/apps/')) {
      // Already logged in, just return
      return;
    }
    
    // Not logged in, perform login
    cy.get('input[name=user]', { timeout: 10000 }).type(username)
    cy.get('input[name=password]').type(password)
    cy.get('input[type=submit]').click()
    
    // After login, we should be redirected to some app page
    cy.url({ timeout: 10000 }).should('include', '/index.php/apps/')
  })
})

// Navigate to Plura app
Cypress.Commands.add('goToPluraApp', () => {
  cy.visit('/index.php/apps/plura/')
})

// Create a new proposal with given title and description
Cypress.Commands.add('createProposal', (title, description) => {
  cy.get('[data-cy="new-proposal-button"]').click()
  cy.get('[data-cy="proposal-title-input"]').type(title)
  cy.get('[data-cy="proposal-description-input"]').type(description)
  cy.get('[data-cy="proposal-submit-button"]').click()
  // Wait for the proposal to be created and appear in the list
  cy.contains('.proposal-item', title).should('be.visible')
})

// Create a new implementation for the first proposal
Cypress.Commands.add('createImplementation', (content) => {
  // Open the first proposal
  cy.get('.proposal-item').first().click()
  // Create a new implementation
  cy.get('[data-cy="new-implementation-button"]').click()
  cy.get('[data-cy="implementation-content-input"]').type(content)
  cy.get('[data-cy="implementation-submit-button"]').click()
  // Wait for the implementation to be created
  cy.contains('.implementation-item', content.substring(0, 20)).should('be.visible')
})

// Allocate credits to a proposal
Cypress.Commands.add('allocateCredits', (amount) => {
  cy.get('[data-cy="credit-allocation-input"]').clear().type(amount.toString())
  cy.get('[data-cy="allocate-credits-button"]').click()
})

// Vote on an implementation
Cypress.Commands.add('voteOnImplementation', (index = 0, voteType = 'up', weight = 1) => {
  cy.get('.implementation-item').eq(index).within(() => {
    if (voteType === 'up') {
      cy.get('[data-cy="upvote-button"]').click()
    } else {
      cy.get('[data-cy="downvote-button"]').click()
    }
    
    if (weight > 1) {
      cy.get('[data-cy="vote-weight-input"]').clear().type(weight.toString())
      cy.get('[data-cy="apply-weight-button"]').click()
    }
  })
})

// Make a prediction on an implementation vote
Cypress.Commands.add('makeVotePrediction', (index = 0, amount) => {
  cy.get('.implementation-item').eq(index).within(() => {
    cy.get('[data-cy="prediction-toggle"]').click()
    cy.get('[data-cy="prediction-amount-input"]').clear().type(amount.toString())
    cy.get('[data-cy="submit-prediction-button"]').click()
  })
})