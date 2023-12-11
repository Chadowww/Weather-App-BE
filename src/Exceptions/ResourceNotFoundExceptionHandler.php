<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\JsonResponse;

class ResourceNotFoundExceptionHandler
{
    public function __invoke(ResourceNotFoundException $exception): JsonResponse
    {
        return new JsonResponse([
            'error' => $exception->getMessage()
        ], 404);
    }
}