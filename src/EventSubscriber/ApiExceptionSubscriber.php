<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof HttpException) {
            $event->setResponse(
                new JsonResponse([
                    'success' => false,
                    'data' => null,
                    'message' => $e->getMessage(),
                ], $e->getStatusCode())
            );

            return;
        }

        $event->setResponse(new JsonResponse([
            'success' => false,
            'data' => null,
            'message' => $e->getMessage(),
        ], Response::HTTP_INTERNAL_SERVER_ERROR));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
