<?php

declare(strict_types=1);

namespace OCA\Plura\Controller;

use OCA\Plura\Service\CreditService;
use OCA\Plura\Service\ParameterService;
use OCA\Plura\Service\ProposalService;
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
    
    /** @var ProposalService */
    private $proposalService;
    
    /** @var IUserSession */
    private $userSession;

    public function __construct(
        string $appName,
        IRequest $request,
        CreditService $creditService,
        ParameterService $parameterService,
        ProposalService $proposalService,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->creditService = $creditService;
        $this->parameterService = $parameterService;
        $this->proposalService = $proposalService;
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
     * Get all proposals
     * 
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $orderDirection
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'GET', url: '/api/proposals')]
    public function getProposals(int $limit = 50, int $offset = 0, string $orderBy = 'credits_allocated', string $orderDirection = 'DESC'): DataResponse {
        try {
            $proposals = $this->proposalService->getAllProposals($limit, $offset, $orderBy, $orderDirection);
            return new DataResponse($proposals);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * Get a specific proposal
     * 
     * @param int $id
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'GET', url: '/api/proposals/{id}')]
    public function getProposal(int $id): DataResponse {
        try {
            $proposal = $this->proposalService->getProposal($id);
            return new DataResponse($proposal);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * Create a new proposal
     * 
     * @param string $title
     * @param string $description
     * @param string $documentId
     * @param string|null $deadline ISO date string
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'POST', url: '/api/proposals')]
    public function createProposal(string $title, string $description, string $documentId, ?string $deadline = null): DataResponse {
        try {
            $deadlineDate = null;
            if ($deadline !== null) {
                $deadlineDate = new \DateTime($deadline);
            }
            
            $proposal = $this->proposalService->createProposal($title, $description, $documentId, $deadlineDate);
            return new DataResponse($proposal);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * Update a proposal
     * 
     * @param int $id
     * @param string $title
     * @param string $description
     * @param string $status
     * @param string|null $deadline ISO date string
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'PUT', url: '/api/proposals/{id}')]
    public function updateProposal(int $id, string $title, string $description, string $status, ?string $deadline = null): DataResponse {
        try {
            $deadlineDate = null;
            if ($deadline !== null) {
                $deadlineDate = new \DateTime($deadline);
            }
            
            $proposal = $this->proposalService->updateProposal($id, $title, $description, $status, $deadlineDate);
            return new DataResponse($proposal);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * Get detailed information about a proposal
     * 
     * @param int $id
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'GET', url: '/api/proposals/{id}/details')]
    public function getProposalDetails(int $id): DataResponse {
        try {
            $details = $this->proposalService->getProposalDetails($id);
            return new DataResponse($details);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
    
    /**
     * Allocate credits to a proposal
     * 
     * @param int $id
     * @param float $amount
     * @return DataResponse
     */
    #[NoAdminRequired]
    #[ApiRoute(verb: 'POST', url: '/api/proposals/{id}/allocate')]
    public function allocateCredits(int $id, float $amount): DataResponse {
        try {
            $result = $this->proposalService->allocateCredits($id, $amount);
            return new DataResponse($result);
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
