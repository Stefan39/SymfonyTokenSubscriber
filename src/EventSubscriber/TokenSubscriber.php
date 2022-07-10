<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Controller\TokenAuthenticatedController;
use App\Service\LoginService;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TokenSubscriber implements EventSubscriberInterface
{
    /**
     * Da man in Sachen CleanCode auch die Methoden innerhalb einer Klasse relativ
     * sauber und leicht verständlich halten sollte, lagere ich die eigentliche
     * Prüfung des JWT's in einem eigenem Service aus, was hier aber nicht
     * Bestandteil sein soll. Diesen Service kann ich aber wiederum via Auto-
     * wiring über den Constructor in den Subscriber holen!
     */
    private LoginService $loginService;#

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    /**
     * @throws Exception
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $controller = is_array($controller) ? $controller[0] : $controller;

        /**
         * Alle Controller die von diesem gen. Interface ableiten werden mit
         * diesem Event behandelt. Granularer kann man es sogar auf die Actions
         * innerhalb des Controllers runterbrechen (die sitzen dann im Controller-
         * Array).
         */
        if ($controller instanceof TokenAuthenticatedController) {
            $token = $this->getJwtFromHeader($event);
            if (empty($token)) {
                throw new AuthenticationException('Invalid or empty token', 401);
            } else {
                /**
                 * Methode prüft ob es ein gültiger Token ist.
                 * Wenn invalid: Wird eine AuthenticationException mit Code 401
                 * geworfen.
                 * Wenn gültig und eingeloggt, werden verschiedene Parameter wie
                 * gewünscht als Array zurückgegeben
                 */
                $data = $this->loginService->checkLogin($token);

                /**
                 * Zur weiteren Verwendung möchte ich im Controller auf die
                 * User Daten zugreifen können (weshalb ich Daten aus dem Array
                 * an dem Request als Attribute hänge):
                 */
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