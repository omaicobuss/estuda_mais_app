<?php

declare(strict_types=1);

namespace App\Domain\Challenge;

use DateTimeImmutable;

final class Challenge
{
    public function __construct(
        private string $id,
        private string $title,
        private string $type,
        private string $startDate,
        private string $endDate,
        private int $rewardXp,
        private string $createdAt
    ) {
    }

    public static function create(
        string $id,
        string $title,
        string $type,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $rewardXp,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            $id,
            trim($title),
            trim(strtolower($type)),
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            max(0, $rewardXp),
            $createdAt->format(DATE_ATOM)
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['title'],
            (string) $data['type'],
            (string) $data['start_date'],
            (string) $data['end_date'],
            (int) ($data['reward_xp'] ?? 0),
            (string) $data['created_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'reward_xp' => $this->rewardXp,
            'created_at' => $this->createdAt,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }

    public function startDate(): string
    {
        return $this->startDate;
    }

    public function endDate(): string
    {
        return $this->endDate;
    }

    public function isOpenOn(DateTimeImmutable $date): bool
    {
        $day = $date->format('Y-m-d');
        return $day >= $this->startDate && $day <= $this->endDate;
    }
}
