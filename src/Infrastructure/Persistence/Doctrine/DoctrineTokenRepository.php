<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\TokenRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\AuthTokenRecord;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTokenRepository implements TokenRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private int $tokenTtlSeconds,
        private int $refreshTokenTtlSeconds
    ) {
    }

    public function issueTokenPair(string $userId): array
    {
        $accessToken = bin2hex(random_bytes(24));
        $refreshToken = bin2hex(random_bytes(32));
        $createdAt = new \DateTimeImmutable('now');
        $expiresAt = $createdAt->modify(sprintf('+%d seconds', max(60, $this->tokenTtlSeconds)));
        $refreshExpiresAt = $createdAt->modify(sprintf('+%d seconds', max(120, $this->refreshTokenTtlSeconds)));

        $record = new AuthTokenRecord();
        $record->setToken($accessToken);
        $record->setUserId($userId);
        $record->setCreatedAt($createdAt);
        $record->setExpiresAt($expiresAt);
        $record->setRevokedAt(null);
        $record->setRefreshToken($refreshToken);
        $record->setRefreshExpiresAt($refreshExpiresAt);

        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt->format(DATE_ATOM),
            'refresh_expires_at' => $refreshExpiresAt->format(DATE_ATOM),
        ];
    }

    public function issueToken(string $userId): string
    {
        return $this->issueTokenPair($userId)['access_token'];
    }

    public function findUserIdByToken(string $token): ?string
    {
        /** @var AuthTokenRecord|null $record */
        $record = $this->entityManager->find(AuthTokenRecord::class, $token);
        if ($record === null) {
            return null;
        }

        $now = new \DateTimeImmutable('now');
        if ($record->getRevokedAt() !== null) {
            return null;
        }
        if ($record->getExpiresAt() <= $now) {
            return null;
        }

        return $record->getUserId();
    }

    public function revokeToken(string $token): void
    {
        /** @var AuthTokenRecord|null $record */
        $record = $this->entityManager->find(AuthTokenRecord::class, $token);
        if ($record === null || $record->getRevokedAt() !== null) {
            return;
        }

        $record->setRevokedAt(new \DateTimeImmutable('now'));
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function refreshSession(string $refreshToken): ?array
    {
        /** @var AuthTokenRecord|null $record */
        $record = $this->entityManager->getRepository(AuthTokenRecord::class)->findOneBy([
            'refreshToken' => $refreshToken,
        ]);
        if ($record === null) {
            return null;
        }

        $now = new \DateTimeImmutable('now');
        if ($record->getRevokedAt() !== null || $record->getRefreshExpiresAt() <= $now) {
            return null;
        }

        $record->setRevokedAt($now);
        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return $this->issueTokenPair($record->getUserId());
    }
}
