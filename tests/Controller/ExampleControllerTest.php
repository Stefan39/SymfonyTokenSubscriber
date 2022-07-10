<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ExampleController;
use App\Tests\Helper\RequestHelper;
use PHPUnit\Framework\TestCase;

class ExampleControllerTest extends TestCase
{
    public function testControllerExpectsJsonResponse(): void
    {
        $expectedData = [
            'userId' => 1,
            'userName' => 'test'
        ];
        $expectedJson = json_encode($expectedData);

        $token = base64_encode($expectedJson);

        $controller = new ExampleController();
        $request = RequestHelper::initRequest(
            '',
            '/api/',
            null,
            $token,
            $expectedData
        );

        $response = $controller->index($request);

        $this->assertIsString($response->getContent());
        $this->assertEquals($expectedJson, $response->getContent());
    }
}