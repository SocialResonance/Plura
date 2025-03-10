# Cypress Testing for Plura

This directory contains the end-to-end tests for the Plura app using Cypress.

## Authentication

Tests use an optimized login command with session caching for better performance. The implementation follows the official [Cypress documentation on optimizing login](https://docs.cypress.io/guides/end-to-end-testing/testing-your-app#Logging-in).

### Using the Login Command

```javascript
// Simple login with default credentials (admin/admin)
cy.loginToNextcloud()

// Login with custom credentials
cy.loginToNextcloud('username', 'password')
```

### How It Works

The login command uses Cypress's session feature to cache the authentication state between tests:

1. It stores the session data based on the username and password
2. Login is performed only once for a given set of credentials
3. Session validation ensures the authentication is still valid before reusing it
4. Sessions are cached across spec files for maximum performance

### Benefits

- **Faster Tests**: Login happens only once instead of before each test
- **More Reliable**: Reduces flakiness by minimizing authentication steps
- **Cross-Spec Caching**: Authentication state is preserved across different test files

## Test Commands

The following custom commands are available for testing:

- `cy.loginToNextcloud(username, password)` - Log in to Nextcloud with session caching
- `cy.goToPluraApp()` - Navigate to the Plura app
- `cy.createProposal(title, description)` - Create a new proposal
- `cy.createImplementation(content)` - Create a new implementation for the first proposal
- `cy.allocateCredits(amount)` - Allocate credits to a proposal
- `cy.voteOnImplementation(index, voteType, weight)` - Vote on an implementation
- `cy.makeVotePrediction(index, amount)` - Make a prediction on an implementation vote

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
