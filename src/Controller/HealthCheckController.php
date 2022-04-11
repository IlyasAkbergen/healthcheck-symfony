<?php

declare(strict_types=1);

namespace Esb\HealthCheckSymfony\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\HealthCheckService;
use Esb\HealthCheck\Status;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController extends AbstractController
{
    /**
     * @var array<HealthCheck>
     */
    private array $checks = [];

    public function addCheck(HealthCheck $healthCheck): void
    {
        $this->checks[] = $healthCheck;
    }

    /**
     * @Route(
     *     "/healthcheck",
     *     methods={"GET"},
     *     name="healthcheck"
     * )
     */
    public function index(): JsonResponse
    {
        $healthCheckService = new HealthCheckService($this->checks);
        $systemStatus = $healthCheckService->getStatus();

        $httpCode = ($systemStatus['status'] ?? null) === Status::OKAY
            ? Response::HTTP_OK
            : Response::HTTP_SERVICE_UNAVAILABLE;

        return new JsonResponse($systemStatus, $httpCode);
    }
}
