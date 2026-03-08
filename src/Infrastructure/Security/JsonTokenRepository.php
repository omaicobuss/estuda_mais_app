<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Contracts\TokenRepositoryInterface;
use App\Infrastructure\Persistence\JsonFileStore;

final class JsonTokenRepository implements TokenRepositoryInterface
{
    private const TABLE = 'auth_tokens';

    public function __construct(private JsonFileStore $store)
    {
    }

    public function issueToken(string $userId): string
    {
        $token = $this->issueTokenPair($userId)['access_token'];
        return $token;
    }

    public function issueTokenPair(string $userId): array
    {
        $accessToken = bin2hex(random_bytes(24));
        $refreshToken = bin2hex(random_bytes(32));
        $createdAt = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        $expiresAt = (new \DateTimeImmutable('+30 days'))->format(DATE_ATOM);
        $refreshExpiresAt = (new \DateTimeImmutable('+60 days'))->format(DATE_ATOM);

        $rows = $this->store->all(self::TABLE);
        $rows[] = [
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user_id' => $userId,
            'created_at' => $createdAt,
            'expires_at' => $expiresAt,
            'refresh_expires_at' => $refreshExpiresAt,
            'revoked_at' => null,
        ];
        $this->store->replaceAll(self::TABLE, $rows);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
            'refresh_expires_at' => $refreshExpiresAt,
        ];
    }

    public function findUserIdByToken(string $token): ?string
    {
        $now = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        foreach ($this->store->all(self::TABLE) as $row) {
            if (($row['token'] ?? null) === $token
                && ($row['revoked_at'] ?? null) === null
                && (($row['expires_at'] ?? null) === null || (string) $row['expires_at'] > $now)
            ) {
                return (string) $row['user_id'];
            }
        }

        return null;
    }

    public function revokeToken(string $token): void
    {
        $rows = $this->store->all(self::TABLE);
        $now = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
        foreach ($rows as $index => $row) {
            if (($row['token'] ?? null) === $token) {
                $rows[$index]['revoked_at'] = $now;
            }
        }
        $this->store->replaceAll(self::TABLE, $rows);
    }

    public function refreshSession(string $refreshToken): ?array
    {
        $rows = $this->store->all(self::TABLE);
        $now = (new \DateTimeImmutable('now'))->format(DATE_ATOM);

        foreach ($rows as $index => $row) {
            if (($row['refresh_token'] ?? null) !== $refreshToken) {
                continue;
            }

            if (($row['revoked_at'] ?? null) !== null) {
                return null;
            }

            if (($row['refresh_expires_at'] ?? null) !== null && (string) $row['refresh_expires_at'] <= $now) {
                return null;
            }

            $rows[$index]['revoked_at'] = $now;
            $this->store->replaceAll(self::TABLE, $rows);

            return $this->issueTokenPair((string) $row['user_id']);
        }

        return null;
    }
}
