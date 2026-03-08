<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'auth_tokens')]
#[ORM\Index(name: 'idx_auth_tokens_user_id', columns: ['user_id'])]
#[ORM\UniqueConstraint(name: 'uniq_auth_tokens_refresh_token', columns: ['refresh_token'])]
final class AuthTokenRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 64)]
    private string $token;

    #[ORM\Column(name: 'user_id', type: 'string', length: 40)]
    private string $userId;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'revoked_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $revokedAt = null;

    #[ORM\Column(name: 'refresh_token', type: 'string', length: 64)]
    private string $refreshToken;

    #[ORM\Column(name: 'refresh_expires_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $refreshExpiresAt;

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function setRevokedAt(?\DateTimeImmutable $revokedAt): void
    {
        $this->revokedAt = $revokedAt;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getRefreshExpiresAt(): \DateTimeImmutable
    {
        return $this->refreshExpiresAt;
    }

    public function setRefreshExpiresAt(\DateTimeImmutable $refreshExpiresAt): void
    {
        $this->refreshExpiresAt = $refreshExpiresAt;
    }
}
