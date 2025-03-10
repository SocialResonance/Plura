<?php

declare(strict_types=1);

namespace OCA\Plura\AppInfo;

use OCA\Plura\Service\CreditService;
use OCA\Plura\Service\ParameterService;
use OCA\Plura\Service\ProposalService;
use OCA\Plura\Db\UserCreditsMapper;
use OCA\Plura\Db\CreditTransactionMapper;
use OCA\Plura\Db\AdminParametersMapper;
use OCA\Plura\Db\MatchingFundMapper;
use OCA\Plura\Db\ProposalMapper;
use OCA\Plura\Db\ProposalCreditMapper;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IUserSession;
use OCP\IGroupManager;
use OCP\Files\IRootFolder;

class Application extends App implements IBootstrap {
	public const APP_ID = 'plura';

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
	    // Register services
	    $context->registerService(CreditService::class, function($c) {
	        return new CreditService(
	            $c->get(UserCreditsMapper::class),
	            $c->get(CreditTransactionMapper::class),
	            $c->get(AdminParametersMapper::class),
	            $c->get(MatchingFundMapper::class),
	            $c->get(IUserSession::class)
	        );
	    });
	    
	    $context->registerService(ParameterService::class, function($c) {
	        return new ParameterService(
	            $c->get(AdminParametersMapper::class),
	            $c->get(IUserSession::class),
	            $c->get(IGroupManager::class)
	        );
	    });
	    
	    $context->registerService(ProposalService::class, function($c) {
	        return new ProposalService(
	            $c->get(ProposalMapper::class),
	            $c->get(ProposalCreditMapper::class),
	            $c->get(CreditTransactionMapper::class),
	            $c->get(MatchingFundMapper::class),
	            $c->get(UserCreditsMapper::class),
	            $c->get(IUserSession::class),
	            $c->get(IRootFolder::class),
	            $c->get(ParameterService::class)
	        );
	    });
	}

	public function boot(IBootContext $context): void {
	}
}
