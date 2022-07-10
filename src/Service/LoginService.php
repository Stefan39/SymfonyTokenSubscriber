<?php

declare(strict_types=1);

namespace App\Service;

class LoginService
{
    public function checkLogin(string $token): array
    {
        // fake encode token
        $decoded = base64_decode($token);

        return json_decode($decoded, true);
    }

}