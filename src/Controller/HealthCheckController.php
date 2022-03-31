<?php

namespace Esb\HealthCheckSymfony\Controller;

use Esb\HealthCheck\HealthCheckService;
use Esb\HealthCheck\Status;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController
{
    /**
     * @Route(
     *     "/healthcheck",
     *     methods={"GET"},
     *     name="healthcheck"
     * )
     */
    public function index(HealthCheckService $healthCheckService): JsonResponse
    {
        $systemStatus = $healthCheckService->getStatus();

        $httpCode = ($systemStatus['status'] ?? null) === Status::OKAY
            ? Response::HTTP_OK
            : Response::HTTP_SERVICE_UNAVAILABLE;

        return new JsonResponse($systemStatus, $httpCode);
    }
}
