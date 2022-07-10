<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\LoginService;
use PHPUnit\Framework\TestCase;

class LoginServiceTest extends TestCase
{
    public function testCheckLogin(): void
    {
        $loginService = new LoginService();

        $expected = [
            'test' => 'test'
        ];

        $data = base64_encode(json_encode($expected));

        $this->assertIsArray($loginService->checkLogin($data));
        $this->assertEquals($expected, $loginService->checkLogin($data));
    }
}