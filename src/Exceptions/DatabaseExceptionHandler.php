<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\JsonResponse;

class DatabaseExceptionHandler
{
    public function __invoke(DatabaseException $exception): JsonResponse
    {
        return new JsonResponse([
            'error' => $exception->getMessage()
        ], $exception->getCode());
    }
}