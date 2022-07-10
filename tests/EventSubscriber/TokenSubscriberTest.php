<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Controller\ExampleController;
use App\EventSubscriber\TokenSubscriber;
use App\Service\LoginService;
use App\Tests\Helper\RequestHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TokenSubscriberTest extends TestCase
{
    /**
     * Dieser Test prüft, ob bei keiner Angabe eines JWT's eine
     * Exception geworfen wird (Teil im TokenSubscriber Zeile 42)
     */
    public function testExpectExceptionOnInvalidToken(): void
    {
        $loginService = $this->getMockBuilder(LoginService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpKernelInterface = $this->createMock(HttpKernelInterface::class);

        $request = RequestHelper::initRequest(
            '',
            '/api/'
        );

        $controller = $this->getMockBuilder(ExampleController::class)
            ->addMethods(['__invoke'])
            ->onlyMethods(['index'])
            ->getMock();
        $controller->method('__invoke')
            ->willReturn(function() use ($controller) {
                return $controller;
            });

        /** @var ExampleController|callable $controller */
        $controllerEvent = new ControllerEvent(
            $httpKernelInterface,
            $controller,
            $request,
            1
        );

        $tokenSubscriber = new TokenSubscriber($loginService);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid or empty token');

        $tokenSubscriber->onKernelController($controllerEvent);
    }

    /**
     * Dieser Test prüft dass die Methode SubscribedEvents ein Array
     * zurückgibt.
     */
    public function testGetSubscribedEventsReturnsArray(): void
    {
        $this->assertIsArray(TokenSubscriber::getSubscribedEvents());
        $this->assertEquals([
            KernelEvents::CONTROLLER => 'onKernelController'
        ], TokenSubscriber::getSubscribedEvents());
    }

    /**
     * Dieser Test prüft ob das Event Request aus dem Header einen Token
     * korrekt zurückgibt.
     */
    public function testRequestAttributesExists(): void
    {
        $loginService = $this->getMockBuilder(LoginService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loginService->method('checkLogin')
            ->willReturn([
                'userId' => 1,
                'userName' => 'test'
            ]);

        $httpKernelInterface = $this->createMock(HttpKernelInterface::class);
        $request = RequestHelper::initRequest(
            '',
            '/api/',
            null,
            'fakeToken',
            [
                'userId' => 1,
                'userName' => 'test'
            ]
        );
        $controller = $this->getMockBuilder(ExampleController::class)
            ->addMethods(['__invoke'])
            ->onlyMethods(['index'])
            ->getMock();
        $controller->method('__invoke')
            ->willReturn(function() use ($controller) {
                return $controller;
            });
        /** @var ExampleController|callable $controller */
        $controllerEvent = new ControllerEvent(
            $httpKernelInterface,
            $controller,
            $request,
            1
        );

        $subscriber = new TokenSubscriber($loginService);
        $subscriber->onKernelController($controllerEvent);

        $this->assertTrue($request->attributes->has('userId'));
        $this->assertTrue($request->attributes->has('userName'));
        $this->assertEquals(1, $request->attributes->get('userId'));
        $this->assertEquals('test', $request->attributes->get('userName'));
    }
}