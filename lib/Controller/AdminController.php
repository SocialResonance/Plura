<?php

declare(strict_types=1);

namespace OCA\Plura\Controller;

use OCA\Plura\Service\ParameterService;
use OCA\Plura\Service\CreditService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\IUserSession;

class AdminController extends Controller {
    /** @var ParameterService */
    private $parameterService;
    
    /** @var CreditService */
    private $creditService;
    
    /** @var IUserSession */
    private $userSession;

    public function __construct(
        string $appName,
        IRequest $request,
        ParameterService $parameterService,
        CreditService $creditService,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->parameterService = $parameterService;
        $this->creditService = $creditService;
        $this->userSession = $userSession;
    }

    /**
     * Admin settings page
     *
     * @return TemplateResponse
     */
    #[FrontpageRoute(verb: 'GET', url: '/settings/admin/plura')]
    public function index(): TemplateResponse {
        $parameters = $this->parameterService->getSystemParameters();
        $matchingFundTotal = $this->creditService->getMatchingFundTotal();
        
        return new TemplateResponse('plura', 'admin', [
            'parameters' => $parameters,
            'matchingFundTotal' => $matchingFundTotal
        ], 'user');
    }

    /**
     * Update a parameter
     *
     * @param string $name
     * @param string $value
     * @return JSONResponse
     */
    #[FrontpageRoute(verb: 'POST', url: '/settings/admin/plura/parameters')]
    public function updateParameter(string $name, string $value): JSONResponse {
        try {
            $parameter = $this->parameterService->updateParameter($name, $value);
            return new JSONResponse($parameter);
        } catch (\InvalidArgumentException $e) {
            return new JSONResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_FORBIDDEN
            );
        } catch (\Exception $e) {
            return new JSONResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Add to the matching fund
     *
     * @param float $amount
     * @return JSONResponse
     */
    #[FrontpageRoute(verb: 'POST', url: '/settings/admin/plura/matching-fund')]
    public function addToMatchingFund(float $amount): JSONResponse {
        try {
            $newTotal = $this->creditService->addToMatchingFund($amount);
            return new JSONResponse(['total' => $newTotal]);
        } catch (\InvalidArgumentException $e) {
            return new JSONResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_FORBIDDEN
            );
        } catch (\Exception $e) {
            return new JSONResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
}