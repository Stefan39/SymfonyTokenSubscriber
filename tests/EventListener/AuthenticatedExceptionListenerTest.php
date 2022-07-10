<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\AuthenticatedExceptionListener;
use App\Tests\Helper\RequestHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticatedExceptionListenerTest extends TestCase
{
    public function testAuthenticatedExceptionListenerReturnsJsonResponse(): void
    {
        $httpKernelInterface = $this->createMock(HttpKernelInterface::class);
        $request = RequestHelper::initRequest(
            '',
            '/api/',
            null,
            null
        );

        $exceptionEvent = new ExceptionEvent(
            $httpKernelInterface,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new AuthenticationException('exception', 401)
        );

        $expected = json_encode([
            'message' => 'exception'
        ]);

        $eventListener = new AuthenticatedExceptionListener();
        $eventListener->onKernelException($exceptionEvent);

        $response = $exceptionEvent->getResponse();

        $this->assertEquals($expected, $response->getContent());
    }
}