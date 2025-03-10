// ***********************************************
// This example commands.ts shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

// Login to Nextcloud with session caching for better performance
Cypress.Commands.add('loginToNextcloud', (username = 'admin', password = 'admin') => {
  // Use session for caching authenticated state
  cy.session([username, password], () => {
    cy.visit('/')

    // Not logged in, perform login
    cy.get('input[name=user]', { timeout: 10000 }).type(username)
    cy.get('input[name=password]').type(password)
    cy.get('.button-vue--icon-and-text').click()

    // After login, we should be redirected to some app page
    cy.url({ timeout: 10000 }).should('include', '/index.php/apps/')

    // Additional verification that login was successful
    cy.get('#header').should('be.visible')
  }, {
    // Session options
    cacheAcrossSpecs: true, // Cache the session across different spec files
    validate() {
      // Validate session is still active before using it
      cy.visit('/')
      cy.url().should('include', '/index.php/apps/')
    },
  })

  // After session setup, navigate to the start page
  cy.visit('/')
})

// Navigate to Plura app
Cypress.Commands.add('goToPluraApp', () => {
  cy.visit('/index.php/apps/plura/')
})

// Create a new proposal with given title and description
Cypress.Commands.add('createProposal', (title: string, description: string) => {
  cy.get('[data-cy="new-proposal-button"]').click()
  cy.get('[data-cy="proposal-title-input"]').type(title)
  cy.get('[data-cy="proposal-description-input"]').type(description)
  cy.get('[data-cy="proposal-submit-button"]').click()
  // Wait for the proposal to be created and appear in the list
  cy.contains('.proposal-item', title).should('be.visible')
})

// Create a new implementation for the first proposal
Cypress.Commands.add('createImplementation', (content: string) => {
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
Cypress.Commands.add('allocateCredits', (amount: number) => {
  cy.get('[data-cy="credit-allocation-input"]').clear().type(amount.toString())
  cy.get('[data-cy="allocate-credits-button"]').click()
})

// Vote on an implementation
Cypress.Commands.add('voteOnImplementation', (index = 0, voteType: 'up' | 'down' = 'up', weight = 1) => {
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
Cypress.Commands.add('makeVotePrediction', (index = 0, amount: number) => {
  cy.get('.implementation-item').eq(index).within(() => {
    cy.get('[data-cy="prediction-toggle"]').click()
    cy.get('[data-cy="prediction-amount-input"]').clear().type(amount.toString())
    cy.get('[data-cy="submit-prediction-button"]').click()
  })
})