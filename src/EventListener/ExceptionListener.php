<?php

namespace App\EventListener;

use App\Exceptions\DatabaseException;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\ResourceNotFoundException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    /**
     * @throws \JsonException
     */
    #[AsEventListener]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $message = json_decode($exception->getMessage(), false) ?? $exception->getMessage();

        switch (true) {
            case $exception instanceof InvalidRequestException:
            case $exception instanceof ResourceNotFoundException:
            case $exception instanceof DatabaseException:
                $event->setResponse(new JsonResponse($message, $exception->getCode()));
                break;
            default:
                $event->setResponse(new JsonResponse($message, 500));
        }
    }
}