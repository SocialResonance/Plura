<?php

declare(strict_types=1);

namespace OCA\Plura\Controller;

use OCA\Plura\Service\CreditService;
use OCA\Plura\Service\ParameterService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use OCP\IUserSession;

class ApiController extends OCSController {
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

    /**
     * Basic API endpoint test
     *
     * @return DataResponse<Http::STATUS_OK, array{message: string}, array{}>
     *
     * 200: Data returned
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'GET', url: '/api')]
    public function index(): DataResponse {
        return new DataResponse(
            ['message' => 'Plura API is working!']
        );
    }
    
    /**
     * Get current user's credit balance
     *
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'GET', url: '/api/credits')]
    public function getUserCredits(): DataResponse {
        try {
            $userCredits = $this->creditService->getCurrentUserCredits();
            return new DataResponse($userCredits);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get current user's transaction history
     *
     * @param int $limit
     * @param int $offset
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'GET', url: '/api/transactions')]
    public function getUserTransactions(int $limit = 50, int $offset = 0): DataResponse {
        try {
            $transactions = $this->creditService->getCurrentUserTransactions($limit, $offset);
            return new DataResponse($transactions);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get system parameters
     *
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'GET', url: '/api/parameters')]
    public function getSystemParameters(): DataResponse {
        try {
            $params = $this->parameterService->getSystemParameters();
            return new DataResponse($params);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update a system parameter (admin only)
     *
     * @param string $name
     * @param string $value
     * @return DataResponse
     */
    #[ApiRoute(verb: 'PUT', url: '/api/parameters/{name}')]
    public function updateParameter(string $name, string $value): DataResponse {
        try {
            $parameter = $this->parameterService->updateParameter($name, $value);
            return new DataResponse($parameter);
        } catch (\InvalidArgumentException $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_FORBIDDEN
            );
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get the matching fund total
     *
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'GET', url: '/api/matching-fund')]
    public function getMatchingFundTotal(): DataResponse {
        try {
            $total = $this->creditService->getMatchingFundTotal();
            return new DataResponse(['total' => $total]);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
}
