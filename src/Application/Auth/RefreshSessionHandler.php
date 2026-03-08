<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\ApiException;
use App\Application\Contracts\TokenRepositoryInterface;

final class RefreshSessionHandler
{
    public function __construct(private TokenRepositoryInterface $tokens)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(array $payload): array
    {
        $refreshToken = trim((string) ($payload['refresh_token'] ?? ''));
        if ($refreshToken === '') {
            throw new ApiException('Campo obrigatorio: refresh_token.', 422);
        }

        $session = $this->tokens->refreshSession($refreshToken);
        if ($session === null) {
            throw new ApiException('Refresh token invalido.', 401);
        }

        return [
            'access_token' => $session['access_token'],
            'refresh_token' => $session['refresh_token'],
            'token_type' => 'Bearer',
            'expires_at' => $session['expires_at'],
            'refresh_expires_at' => $session['refresh_expires_at'],
        ];
    }
}
