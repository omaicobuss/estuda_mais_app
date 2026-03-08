<?php

declare(strict_types=1);

namespace App\Domain\Study;

use DateTimeImmutable;

final class CardReview
{
    public function __construct(
        private string $id,
        private string $userId,
        private string $flashcardId,
        private int $repetition,
        private int $interval,
        private float $easeFactor,
        private string $nextReview
    ) {
    }

    public static function create(
        string $id,
        string $userId,
        string $flashcardId,
        DateTimeImmutable $today
    ): self {
        return new self(
            $id,
            $userId,
            $flashcardId,
            0,
            1,
            2.5,
            $today->modify('+1 day')->format('Y-m-d')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['user_id'],
            (string) $data['flashcard_id'],
            (int) ($data['repetition'] ?? 0),
            (int) ($data['interval'] ?? 1),
            (float) ($data['ease_factor'] ?? 2.5),
            (string) $data['next_review']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'flashcard_id' => $this->flashcardId,
            'repetition' => $this->repetition,
            'interval' => $this->interval,
            'ease_factor' => $this->easeFactor,
            'next_review' => $this->nextReview,
        ];
    }

    public function flashcardId(): string
    {
        return $this->flashcardId;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function nextReview(): string
    {
        return $this->nextReview;
    }

    public function isDue(DateTimeImmutable $today): bool
    {
        return $this->nextReview <= $today->format('Y-m-d');
    }

    public function registerResult(bool $isCorrect, DateTimeImmutable $today): void
    {
        if ($isCorrect) {
            $this->repetition++;
            if ($this->repetition === 1) {
                $this->interval = 1;
            } else {
                $this->interval = max(1, (int) round($this->interval * $this->easeFactor));
            }
            $this->easeFactor += 0.1;
        } else {
            $this->repetition = 0;
            $this->interval = 1;
        }

        $this->nextReview = $today->modify(sprintf('+%d day', $this->interval))->format('Y-m-d');
    }
}
