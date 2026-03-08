<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface TokenRepositoryInterface
{
    /**
     * @return array{
     *   access_token: string,
     *   refresh_token: string,
     *   expires_at: string,
     *   refresh_expires_at: string
     * }
     */
    public function issueTokenPair(string $userId): array;

    public function issueToken(string $userId): string;

    public function findUserIdByToken(string $token): ?string;

    public function revokeToken(string $token): void;

    /**
     * @return array{
     *   access_token: string,
     *   refresh_token: string,
     *   expires_at: string,
     *   refresh_expires_at: string
     * }|null
     */
    public function refreshSession(string $refreshToken): ?array;
}
