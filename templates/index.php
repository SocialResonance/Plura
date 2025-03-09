<?php

declare(strict_types=1);

use OCP\Util;

// Script(s)
Util::addScript(OCA\Plura\AppInfo\Application::APP_ID, 'main');

// Variables for Vue
$userCredits = $_['userCredits'] ?? 0;
$matchingFundTotal = $_['matchingFundTotal'] ?? 0;
?>

<div id="plura" data-user-credits="<?php p($userCredits); ?>" data-matching-fund="<?php p($matchingFundTotal); ?>">
    <!-- Vue app will mount here -->
    
    <!-- Fallback content (replaced by Vue) -->
    <div class="plura-loading">
        <div class="app-content-list-item">
            <div id="credit-balance" data-cy="user-credit-balance" class="credit-balance"><?php p(number_format($userCredits, 2)); ?></div>
            <h2><?php p($l->t('Loading Plura...')); ?></h2>
        </div>
    </div>
</div>
