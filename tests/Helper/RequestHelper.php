<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestHelper extends TestCase
{
    public static function initRequest(
        string $returnValue,
        ?string $uri = null,
        ?string $query = null,
        ?string $token = null,
        ?array $setAttributes = null
    ): Request {
        $headerBag = [];
        $attributes = [];

        $requestMock = (new RequestHelper())->createMock(Request::class);
        if (!empty($token)) {
            // In dem System wird im Header x-jwt-token der JWT abgelegt!
            $headerBag['x-jwt-token'] = $token;
        }

        if (!empty($setAttributes)) {
            $attributes = $setAttributes;
        }

        $requestMock->headers = new HeaderBag($headerBag);
        $requestMock->attributes = new ParameterBag($attributes);

        $requestMock->method('getRequestUri')
            ->willReturn($uri);
        $requestMock->method('getContent')
            ->willReturn($returnValue);
        $requestMock->method('get')
            ->willReturn($query);

        return $requestMock;
    }
}