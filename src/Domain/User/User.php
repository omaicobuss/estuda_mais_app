<?php

declare(strict_types=1);

namespace App\Domain\User;

use DateTimeImmutable;

final class User
{
    public function __construct(
        private string $id,
        private string $email,
        private string $passwordHash,
        private string $name,
        private string $role,
        private int $xp,
        private int $level,
        private int $streak,
        private ?string $avatarId,
        private ?string $lastStudyDate,
        private string $createdAt
    ) {
    }

    public static function create(
        string $id,
        string $email,
        string $plainPassword,
        string $name,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            $id,
            strtolower(trim($email)),
            password_hash($plainPassword, PASSWORD_BCRYPT),
            trim($name),
            'student',
            0,
            0,
            0,
            null,
            null,
            $createdAt->format(DATE_ATOM)
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['email'],
            (string) $data['password_hash'],
            (string) $data['name'],
            (string) ($data['role'] ?? 'student'),
            (int) ($data['xp'] ?? 0),
            (int) ($data['level'] ?? 0),
            (int) ($data['streak'] ?? 0),
            isset($data['avatar_id']) ? (string) $data['avatar_id'] : null,
            isset($data['last_study_date']) ? (string) $data['last_study_date'] : null,
            (string) $data['created_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'name' => $this->name,
            'role' => $this->role,
            'xp' => $this->xp,
            'level' => $this->level,
            'streak' => $this->streak,
            'avatar_id' => $this->avatarId,
            'last_study_date' => $this->lastStudyDate,
            'created_at' => $this->createdAt,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function role(): string
    {
        return $this->role;
    }

    public function xp(): int
    {
        return $this->xp;
    }

    public function level(): int
    {
        return $this->level;
    }

    public function streak(): int
    {
        return $this->streak;
    }

    public function avatarId(): ?string
    {
        return $this->avatarId;
    }

    public function lastStudyDate(): ?string
    {
        return $this->lastStudyDate;
    }

    public function setAvatarId(?string $avatarId): void
    {
        $normalized = $avatarId !== null ? trim($avatarId) : null;
        $this->avatarId = $normalized !== '' ? $normalized : null;
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }

    public function markStudyOn(DateTimeImmutable $today): bool
    {
        $todayString = $today->format('Y-m-d');

        if ($this->lastStudyDate === $todayString) {
            return false;
        }

        if ($this->lastStudyDate === null) {
            $this->streak = 1;
        } else {
            $last = DateTimeImmutable::createFromFormat('Y-m-d', $this->lastStudyDate);
            $isConsecutiveDay = $last !== false
                && $last->modify('+1 day')->format('Y-m-d') === $todayString;

            $this->streak = $isConsecutiveDay ? $this->streak + 1 : 1;
        }

        $this->lastStudyDate = $todayString;

        return true;
    }

    public function addXp(int $xp): void
    {
        if ($xp <= 0) {
            return;
        }

        $this->xp += $xp;
        $this->level = (int) floor(sqrt($this->xp / 100));
    }
}
