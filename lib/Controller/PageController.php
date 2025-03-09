<?php

declare(strict_types=1);

namespace OCA\Plura\Controller;

use OCA\Plura\AppInfo\Application;
use OCA\Plura\Service\CreditService;
use OCA\Plura\Service\ParameterService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IUserSession;

class PageController extends Controller {
    /** @var CreditService */
    private $creditService;
    
    /** @var ParameterService */
    private $parameterService;
    
    /** @var IUserSession */
    private $userSession;
    
    public function __construct(
        string $appName, 
        IRequest $request,
        CreditService $creditService,
        ParameterService $parameterService,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->creditService = $creditService;
        $this->parameterService = $parameterService;
        $this->userSession = $userSession;
    }
    
	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/')]
	public function index(): TemplateResponse {
        // Get user credits
        $userCredits = 0;
        $recentTransactions = [];
        $matchingFundTotal = 0;
        $parameters = [];
        
        try {
            if ($this->userSession->isLoggedIn()) {
                $userCreditsObj = $this->creditService->getCurrentUserCredits();
                $userCredits = $userCreditsObj->getCreditAmount();
                $recentTransactions = $this->creditService->getCurrentUserTransactions(5);
            }
            
            $matchingFundTotal = $this->creditService->getMatchingFundTotal();
            $parameters = $this->parameterService->getSystemParameters();
        } catch (\Exception $e) {
            // Log error but continue
            \OC::$server->getLogger()->error('Error loading Plura data: ' . $e->getMessage(), ['app' => 'plura']);
        }
        
		return new TemplateResponse(
			Application::APP_ID,
			'index',
			[
			    'userCredits' => $userCredits,
			    'recentTransactions' => $recentTransactions,
			    'matchingFundTotal' => $matchingFundTotal,
			    'parameters' => $parameters
			]
		);
	}
}
