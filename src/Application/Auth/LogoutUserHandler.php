<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\Contracts\TokenRepositoryInterface;

final class LogoutUserHandler
{
    public function __construct(private TokenRepositoryInterface $tokens)
    {
    }

    public function handle(string $token): void
    {
        $this->tokens->revokeToken($token);
    }
}
