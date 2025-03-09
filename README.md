# Plura  
Collaborative document editing powered by community wisdom


## What is Plura?
Plura is a NextCloud app that implements Plural Management for collaborative document editing. Instead of relying on rigid hierarchies or chaotic free-for-alls, Plura creates a merit-based system where influence is earned through contributions and community alignment.

## How It Works
Plura introduces a two-stage approach to document editing:

### 1. Edit Proposal: Identifying What Needs Changing
- Anyone can propose an "Edit proposal" – a description of what should be changed in a document
- Community members allocate "management credits" to proposals they find important
- The more support a proposal receives, the higher its priority
- Popular proposals attract more attention and implementation efforts

### 2. Edit Implementation: Proposing Concrete Changes
- Users can create an "Edit Implementation" that addresses specific proposals
- Multiple implementations can be proposed for a single proposal
- The community votes to approve or reject each implementation
- When approved, the implementation is applied and the contributor is rewarded

## The Credit System

### Mathematical Distribution and Calculation

#### Priority Setting
For each edit proposal, the priority is calculated using quadratic funding:

```
Priority = (√P₁ + √P₂ + ... + √Pₙ)²
```

Where P₁, P₂, etc. are the number of credits each user assigns to the proposal.

This quadratic approach ensures:
1. Broad support from many users has greater impact than large contributions from few users
2. A single user can't dominate the priority-setting process

#### Matching Fund
The system includes a matching fund (M) to amplify community preferences:
```
TotalPriority = UserCredits + MatchingFund
```

When a matching fund is limited, the matching allocation is calculated as:
```
MatchAllocation = (Priority - UserCredits) × (TotalMatchingFund / TotalMatchingNeeded)
```

#### Contribution Rewards
When an implementation is approved, the contributor receives credits equal to:
```
Reward = TotalPriority - VotingFees
```

Where `VotingFees` are the processing costs for prediction rewards.

#### Voting and Prediction
Voting on implementations uses quadratic voting:
- Cost to cast a vote of strength v: v²
- For a vote with prediction, cost becomes: Kv² + v
- Correct predictions pay out: 2v

The parameter K controls the balance between voting and prediction:
- K = 0: No cost to vote, only prediction costs
- K = 1: Standard quadratic voting with prediction as an option
- K > 1: Higher voting costs relative to prediction

Recommended setting: K = 0.1 to 0.5 for optimal community participation

### Earning Credits
- **Implementation rewards**: Receive credits proportional to the priority of the proposal you address
- **Prediction rewards**: Earn 2v credits for correctly predicting a vote outcome (costs v to predict)
- **Initial allocation**: New users receive a starter amount (typically 10-25 credits)

### Spending Credits
- **Funding proposals**: Allocate credits to prioritize edit proposals
- **Voting**: Spend v² credits to cast a vote of strength v
- **Prediction**: Bet v credits on a vote outcome for potential 2v return

## Technical Implementation

The system maintains three core data structures:
1. **User Credit Ledger**: Tracks each user's available credits
2. **Proposal Registry**: Stores proposals and their allocated credits
3. **Implementation Vote Records**: Tracks votes, predictions, and outcomes

All transactions are recorded in an immutable log for transparency and auditability.

## Configuration Parameters

Administrators can adjust:
- K: The prediction subsidy parameter (default: 0.25)
- Initial credit allocation for new users (default: 15)
- Matching fund size and replenishment rate
- Voting period duration (default: 72 hours)

## Security and Manipulation Prevention

To prevent gaming of the system:
- Credits cannot be transferred directly between users
- A single user's vote influence is capped by the quadratic cost function
- Prediction rewards are only profitable for smaller votes, preventing large credit holders from exploiting prediction markets
- Maximum vote size and maximum prediction size parameters can be set

## Deployment Recommendations

For optimal results in a new community:
1. Start with K = 0.1 to encourage participation through prediction
2. Set initial matching fund to approximately 25% of total allocated credits
3. Gradually increase K as the system stabilizes
4. Consider using federated identity to prevent sockpuppet accounts
