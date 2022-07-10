<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticatedExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        /**
         * Der folgende Aufruf ist tatsächlich sehr wichtig, wenn man eine
         * eigene Response zurückgeben möchte:
         */
        $event->allowCustomResponseCode();

        if ($exception instanceof AuthenticationException && 401 === $exception->getCode()) {
            $response = new JsonResponse(
                [
                    'message' => $exception->getMessage()
                ],
                Response::HTTP_UNAUTHORIZED
            );
            $event->setResponse($response);
        }
    }
}