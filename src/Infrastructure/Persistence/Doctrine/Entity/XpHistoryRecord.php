<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'xp_history')]
#[ORM\Index(name: 'idx_xp_history_user_created', columns: ['user_id', 'created_at'])]
final class XpHistoryRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 40)]
    private string $id;

    #[ORM\Column(name: 'user_id', type: 'string', length: 40)]
    private string $userId;

    #[ORM\Column(type: 'integer')]
    private int $xp;

    #[ORM\Column(type: 'string', length: 80)]
    private string $reason;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function setXp(int $xp): void
    {
        $this->xp = $xp;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
