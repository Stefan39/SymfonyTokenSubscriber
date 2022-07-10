<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Controller\TokenAuthenticatedController;
use App\Service\LoginService;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TokenSubscriber implements EventSubscriberInterface
{
    private LoginService $loginService;#

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $controller = is_array($controller) ? $controller[0] : $controller;

        if ($controller instanceof TokenAuthenticatedController) {
            $token = $this->getJwtFromHeader($event);
            if (empty($token)) {
                throw new AuthenticationException('Invalid or empty token', 401);
            } else {
                $data = $this->loginService->checkLogin($token);

                $request = $event->getRequest();
                $request->attributes->set('userId', $data['userId']);
                $request->attributes->set('userName', $data['userName']);
            }
        }
    }

    #[ArrayShape([KernelEvents::CONTROLLER => "string"])]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController'
        ];
    }

    private function getJwtFromHeader(ControllerEvent $event): ?string
    {
        return $event->getRequest()->headers->get('x-jwt-token');
    }
}