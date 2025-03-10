# Plura Development Guide

## Build & Run Commands
- Start environment: `docker-compose up -d nextcloud`
- Stop environment: `docker-compose stop nextcloud`
- Reset environment: `docker-compose down -v`
- Build JS assets: `cd workspace/server && npm run build`
- Development mode: `cd workspace/server && npm run dev`
- Watch mode: `cd workspace/server && npm run watch`
- Run tests: `cd workspace/server && npm run test`
- Run single test: `cd workspace/server && npm run test -- path/to/test`
- Lint code: `cd workspace/server && npm run lint`
- Fix linting issues: `cd workspace/server && npm run lint:fix`
- Compile CSS: `cd workspace/server && npm run sass`

## Cypress Testing Commands
- Run Cypress tests headless: `npm run test:ci`
- TDD workflow (lint + tests): `npm run lint && npm run test:ci`

### Testing Workflow Guidelines
- **Always run tests before making changes**: Before modifying code or converting files, run the tests first to establish a baseline
- **Run tests after each significant change**: After code modifications, run tests to verify functionality
- **Debug test failures**: When tests fail, check cypress/screenshots/[test-name] and browser console logs
- **Server requirements**: Tests connect to http://nextcloud.local - ensure Docker environment is running before testing
- **Code conversion protocol**: When converting between JavaScript/TypeScript, maintain identical test behavior:
  1. Run tests on original files to verify functionality
  2. Convert files one at a time or in small groups
  3. Run tests after conversion to verify behavior is maintained  
  4. Commit changes only after tests pass or fail with same errors as original

## Test-Driven Development Methodology
- **Create branch with feature name:** `git checkout -b feature-name`
- **Write failing tests first**: Before implementing any feature, write Cypress tests that define the expected behavior. Start with a test file that has the same name as the feature, but  `feature-name.cy.ts` extension. If you need to divide the e2e test into multiple files, create a folder with the feature name and add the test files there.
- **Implement the minimum code**: Add just enough code to make the tests pass
- **Refactor**: Clean up the code while ensuring the tests still pass
- **Use data-cy attributes**: Always add data-cy attributes to elements for stable test selectors:
  ```html
  <button data-cy="new-proposal-button">Create Proposal</button>
  ```
- **CI integration**: All tests must pass in CI before merging PRs. Run tests with `npm run test:ci`. When a test fails, go to cypress/screenshots/feature-name.cy.ts and check the screenshot together with the console log to see what went wrong.

## Code Style Guidelines
- Use TypeScript for type safety
- Follow ESLint configuration for JS/TS formatting
- Use stylelint for SCSS/Vue styling
- Follow Vue 2.x component patterns
- Use @nextcloud/* packages for common functionality
- Component naming: PascalCase for components, kebab-case for files
- Handle errors with proper try/catch blocks and log with @nextcloud/logger
- Use async/await for asynchronous operations
- Prefer Vue's reactive patterns over direct DOM manipulation

### TypeScript Guidelines
- All new code should be written in TypeScript
- TypeScript files should have explicit type annotations for function parameters and return types
- When converting JavaScript to TypeScript:
  - Check package.json for TypeScript dependencies (typescript, ts-loader)
  - Ensure proper tsconfig.json configuration
  - Verify any custom types are declared in appropriate .d.ts files
  - Test incrementally after each file conversion
  - Add type annotations without changing existing logic
  - Run `npm run lint:ts` to verify type correctness

## Nextcloud App Development
- App names must be in PascalCase (e.g., "Plura" not "plura")
- App structure must follow Nextcloud conventions:
  - appinfo/info.xml: App metadata and configuration
  - lib/: PHP backend code
  - src/: Vue.js frontend code
  - templates/: PHP templates for pages
- API endpoints can be defined with #[FrontpageRoute] or #[ApiRoute] attributes
- Use QBMapper for database operations instead of raw SQL
- App database tables should be prefixed with app name (e.g., "plura_items")
- Use NcContent, NcAppNavigation, and NcAppContent components for UI layout
- Database migrations use Version{Major}{Minor}{Patch}Date{YYYYMMDDHHMMSS}.php naming
- Increase app version in info.xml to trigger migrations

## Plural Management Implementation

### Roles in the System
- **Contributors**: Create implementations that address proposed issues
- **Managers**: Use credits to prioritize issues and vote on implementations
- **Administrators**: Set system parameters to guide behavior and maintain health

### Credit Distribution Mechanisms
- **Initial Credit Allocation**:
  - New users receive 100 credits upon account creation
  - Credits are stored in the plura_user_credits table with user_id and credit_amount fields
  - Administrators can allocate initial credits to founders and early contributors

- **Credit Earning**:
  - For approved implementations: Author receives proportional credits based on the quadratic priority of the issue
  - For correct vote predictions: User receives 2× their stake when correctly predicting vote outcomes
  - Credits used for voting are recycled into the matching fund for future contribution rewards

- **Credit Spending**:
  - Issue prioritization: Credits spent according to quadratic funding formula (QF = (∑√Pi)²)
  - Voting on implementations: Quadratic cost (v² credits for vote strength v)
  - Prediction: Optional additional credits staked alongside votes

### Quadratic Funding for Issue Prioritization
- Each issue is prioritized using a quadratic funding formula: QP = (∑√Pi)²
  - Pi represents an individual's assigned priority credits
- Total contribution payout (CP) = Sum of direct priority credits + matching funds
- Matching funds come from a pool supplied by admin/founders and recycled voting credits
- When a contribution addresses an issue, priority credits are frozen until vote resolution

### Vote and Prediction System
- **Vote Cost Formula**: K × v² (where K is the admin-set parameter controlling voting cost)
- **Vote Weighting**: Votes for/against are tallied with quadratic weighting
- **Prediction Mechanism**:
  - Optional prediction costs v additional credits (on top of K × v² voting cost)
  - Correct prediction pays out 2v credits
  - Prediction rewards come from the contribution payout as a processing fee
  - K can be tuned by administrators (higher K = less profitable voting)

### Implementation Optimizations
- Each vote prediction should be designed to maximize $2\rho v - Kv² - v$ where $\rho$ is probability of success
- For pure credit maximization, optimal voting strength is $v = \frac{\rho - 1}{K}$ for $\rho > 0.5$
- Administrators should set K such that $\alpha CP_j > \frac{N}{K}$ to ensure no more than α proportion of contribution payout goes to prediction rewards (where N is number of voters)

### Database Schema
- plura_proposals: id, title, description, document_id, status, credits_allocated, created_at, deadline
- plura_implementations: id, proposal_id, user_id, content, status, created_at
- plura_votes: id, implementation_id, user_id, vote_type, vote_weight, prediction_amount, created_at
- plura_user_credits: user_id, credit_amount, last_updated
- plura_credit_transactions: id, user_id, amount, transaction_type, related_entity_id, created_at
- plura_matching_fund: total_amount, last_distribution_date
- plura_admin_parameters: parameter_name, parameter_value, last_updated

### Critical System Parameters
- Prediction subsidy parameter (K): Controls vote cost vs. prediction reward balance
- Matching pool percentage: Percentage of credits allocated to the matching fund
- Minimum vote threshold: Minimum number of votes required for implementation approval
- Proposal funding period: Minimum time a proposal must remain open for funding
- Implementation voting period: Duration of voting window for each implementation
