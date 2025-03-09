# Plura App Test-Driven Development with Cypress

This directory contains end-to-end tests for the Plura app using Cypress.

## Test-Driven Development (TDD) Workflow

The Plura app follows a Test-Driven Development approach, which means:

1. **Write a failing test** - Before implementing a feature, write a test that defines the expected behavior
2. **Implement the feature** - Write the minimum code needed to make the test pass
3. **Refactor** - Clean up the code while ensuring the tests still pass

## Running Tests

```bash
# Open Cypress in interactive mode (for development)
npm run test:tdd

# Run tests in headless mode (for CI)
npm run test:ci

# Combined TDD workflow (watch for file changes and keep Cypress open)
npm run tdd
```

## Test Structure

- **e2e/** - End-to-end tests organized by feature
  - `app.cy.js` - Basic app interface tests
  - `proposals.cy.js` - Tests for proposal creation and management
  - `implementations.cy.js` - Tests for implementation submission and voting
  - `user-credits.cy.js` - Tests for user credit functionality

## Custom Commands

We've created several custom Cypress commands to make testing easier:

- `cy.loginToNextcloud(username, password)` - Logs in to Nextcloud
- `cy.goToPluraApp()` - Navigates to the Plura app
- `cy.createProposal(title, description)` - Creates a new proposal
- `cy.createImplementation(content)` - Creates a new implementation
- `cy.allocateCredits(amount)` - Allocates credits to a proposal
- `cy.voteOnImplementation(index, voteType, weight)` - Votes on an implementation
- `cy.makeVotePrediction(index, amount)` - Makes a prediction on an implementation

## Best Practices

1. **Use data-cy attributes** - Always use `data-cy` attributes for test selectors to ensure test stability
   ```html
   <button data-cy="submit-button">Submit</button>
   ```

2. **Follow the TDD workflow** - Write tests first, then implement the feature

3. **Keep tests isolated** - Each test should be independent and not rely on the state from previous tests

4. **Use custom commands** - Abstract common actions into custom commands

## Example

```javascript
describe('Creating a new proposal', () => {
  beforeEach(() => {
    cy.loginToNextcloud()
    cy.goToPluraApp()
  })

  it('should create a new proposal successfully', () => {
    const title = 'Test Proposal'
    const description = 'This is a test proposal description'
    
    cy.createProposal(title, description)
    cy.contains('.proposal-item', title).should('be.visible')
  })
})
```