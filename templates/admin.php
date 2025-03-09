<?php
declare(strict_types=1);

use OCP\Util;

// Add scripts
Util::addScript(OCA\Plura\AppInfo\Application::APP_ID, 'admin');

// Variables from the controller
$parameters = $_['parameters'] ?? [];
$matchingFundTotal = $_['matchingFundTotal'] ?? 0;
?>

<div id="plura-admin-settings" class="section" data-cy="admin-parameters">
    <h2><?php p($l->t('Plura Settings')); ?></h2>
    
    <div class="plura-admin-section">
        <h3><?php p($l->t('System Parameters')); ?></h3>
        
        <div class="plura-param-group">
            <label for="k_value"><?php p($l->t('Prediction Subsidy Parameter (K)')); ?></label>
            <input type="number" id="k_value" name="k_value" 
                   min="0.1" max="2" step="0.1" 
                   value="<?php p($parameters['k_value'] ?? '0.25'); ?>"
                   data-cy="param-k-value">
            <p class="plura-param-desc">
                <?php p($l->t('Controls the balance between voting costs and prediction rewards. Recommended: 0.1-0.5')); ?>
            </p>
        </div>
        
        <div class="plura-param-group">
            <label for="initial_credit_amount"><?php p($l->t('Initial Credit Amount')); ?></label>
            <input type="number" id="initial_credit_amount" name="initial_credit_amount" 
                   min="1" max="1000" step="1" 
                   value="<?php p($parameters['initial_credit_amount'] ?? '100'); ?>"
                   data-cy="param-initial-credits">
            <p class="plura-param-desc">
                <?php p($l->t('Credits allocated to new users upon account creation.')); ?>
            </p>
        </div>
        
        <div class="plura-param-group">
            <label for="matching_fund_percentage"><?php p($l->t('Matching Fund Percentage')); ?></label>
            <input type="number" id="matching_fund_percentage" name="matching_fund_percentage" 
                   min="0" max="100" step="1" 
                   value="<?php p($parameters['matching_fund_percentage'] ?? '25'); ?>"
                   data-cy="param-matching-fund">
            <p class="plura-param-desc">
                <?php p($l->t('Percentage of credits allocated to the matching fund.')); ?>
            </p>
        </div>
        
        <div class="plura-param-group">
            <label for="minimum_vote_threshold"><?php p($l->t('Minimum Vote Threshold')); ?></label>
            <input type="number" id="minimum_vote_threshold" name="minimum_vote_threshold" 
                   min="1" max="100" step="1" 
                   value="<?php p($parameters['minimum_vote_threshold'] ?? '5'); ?>"
                   data-cy="param-vote-threshold">
            <p class="plura-param-desc">
                <?php p($l->t('Minimum number of votes required for implementation approval.')); ?>
            </p>
        </div>
        
        <div class="plura-param-group">
            <label for="proposal_funding_period_days"><?php p($l->t('Proposal Funding Period (days)')); ?></label>
            <input type="number" id="proposal_funding_period_days" name="proposal_funding_period_days" 
                   min="1" max="90" step="1" 
                   value="<?php p($parameters['proposal_funding_period_days'] ?? '14'); ?>"
                   data-cy="param-proposal-period">
            <p class="plura-param-desc">
                <?php p($l->t('Minimum time a proposal must remain open for funding.')); ?>
            </p>
        </div>
        
        <div class="plura-param-group">
            <label for="implementation_voting_period_hours"><?php p($l->t('Implementation Voting Period (hours)')); ?></label>
            <input type="number" id="implementation_voting_period_hours" name="implementation_voting_period_hours" 
                   min="1" max="336" step="1" 
                   value="<?php p($parameters['implementation_voting_period_hours'] ?? '72'); ?>"
                   data-cy="param-impl-period">
            <p class="plura-param-desc">
                <?php p($l->t('Duration of voting window for each implementation.')); ?>
            </p>
        </div>
        
        <button id="save-parameters" class="primary"><?php p($l->t('Save Parameters')); ?></button>
    </div>
    
    <div class="plura-admin-section">
        <h3><?php p($l->t('Matching Fund')); ?></h3>
        
        <div class="plura-param-group">
            <label for="matching_fund_total"><?php p($l->t('Current Matching Fund Balance')); ?></label>
            <input type="text" id="matching_fund_total" name="matching_fund_total" 
                   value="<?php p(number_format($matchingFundTotal, 2)); ?>" readonly>
        </div>
        
        <div class="plura-param-group">
            <label for="add_to_matching_fund"><?php p($l->t('Add to Matching Fund')); ?></label>
            <input type="number" id="add_to_matching_fund" name="add_to_matching_fund" 
                   min="1" max="10000" step="1" value="100">
            <button id="add-to-fund" class="primary"><?php p($l->t('Add Credits')); ?></button>
        </div>
    </div>
</div>