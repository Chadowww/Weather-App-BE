<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\JsonResponse;

class InvalidRequestExceptionHandler
{
    public function __invoke(InvalidRequestException $exception): JsonResponse
    {
        return new JsonResponse([
            'error' => $exception->getMessage()
        ], $exception->getCode());
    }
}