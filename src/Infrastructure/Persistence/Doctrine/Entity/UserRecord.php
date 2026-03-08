<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_users_email', columns: ['email'])]
final class UserRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 40)]
    private string $id;

    #[ORM\Column(type: 'string', length: 190)]
    private string $email;

    #[ORM\Column(name: 'password_hash', type: 'string', length: 255)]
    private string $passwordHash;

    #[ORM\Column(type: 'string', length: 120)]
    private string $name;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'student'])]
    private string $role = 'student';

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $xp = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $level = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $streak = 0;

    #[ORM\Column(name: 'avatar_id', type: 'string', length: 80, nullable: true)]
    private ?string $avatarId = null;

    #[ORM\Column(name: 'last_study_date', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastStudyDate = null;

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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function setXp(int $xp): void
    {
        $this->xp = $xp;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getStreak(): int
    {
        return $this->streak;
    }

    public function setStreak(int $streak): void
    {
        $this->streak = $streak;
    }

    public function getAvatarId(): ?string
    {
        return $this->avatarId;
    }

    public function setAvatarId(?string $avatarId): void
    {
        $this->avatarId = $avatarId;
    }

    public function getLastStudyDate(): ?\DateTimeImmutable
    {
        return $this->lastStudyDate;
    }

    public function setLastStudyDate(?\DateTimeImmutable $lastStudyDate): void
    {
        $this->lastStudyDate = $lastStudyDate;
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
