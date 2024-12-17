<?php
namespace App\Service;

use Random\RandomException;

class TokenService
{
    /**
     * @throws RandomException
     */
    public function generateValidationToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}