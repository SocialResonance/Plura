/// <reference types="cypress" />

declare namespace Cypress {
  interface Chainable<Subject = any> {
    /**
     * Login to Nextcloud with session caching for better performance
     * @param username - The username to login with (default: 'admin')
     * @param password - The password to login with (default: 'admin')
     * @example cy.loginToNextcloud()
     * @example cy.loginToNextcloud('user', 'password')
     */
    loginToNextcloud(username?: string, password?: string): Chainable<Subject>;

    /**
     * Navigate to Plura app
     * @example cy.goToPluraApp()
     */
    goToPluraApp(): Chainable<Subject>;

    /**
     * Create a new proposal with given title and description
     * @param title - The title of the proposal
     * @param description - The description of the proposal
     * @example cy.createProposal('My Proposal', 'Proposal description')
     */
    createProposal(title: string, description: string): Chainable<Subject>;

    /**
     * Create a new implementation for the first proposal
     * @param content - The content of the implementation
     * @example cy.createImplementation('Implementation content')
     */
    createImplementation(content: string): Chainable<Subject>;

    /**
     * Allocate credits to a proposal
     * @param amount - The amount of credits to allocate
     * @example cy.allocateCredits(10)
     */
    allocateCredits(amount: number): Chainable<Subject>;

    /**
     * Vote on an implementation
     * @param index - The index of the implementation to vote on (default: 0)
     * @param voteType - The type of vote ('up' or 'down', default: 'up')
     * @param weight - The weight of the vote (default: 1)
     * @example cy.voteOnImplementation()
     * @example cy.voteOnImplementation(1, 'down', 2)
     */
    voteOnImplementation(index?: number, voteType?: 'up' | 'down', weight?: number): Chainable<Subject>;

    /**
     * Make a prediction on an implementation vote
     * @param index - The index of the implementation to predict (default: 0)
     * @param amount - The amount of credits to allocate to the prediction
     * @example cy.makeVotePrediction(0, 5)
     */
    makeVotePrediction(index: number, amount: number): Chainable<Subject>;
  }
}