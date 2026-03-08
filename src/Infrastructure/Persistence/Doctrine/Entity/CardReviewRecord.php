<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'card_reviews')]
#[ORM\UniqueConstraint(name: 'uk_user_flashcard', columns: ['user_id', 'flashcard_id'])]
final class CardReviewRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 40)]
    private string $id;

    #[ORM\Column(name: 'user_id', type: 'string', length: 40)]
    private string $userId;

    #[ORM\Column(name: 'flashcard_id', type: 'string', length: 40)]
    private string $flashcardId;

    #[ORM\Column(type: 'integer')]
    private int $repetition = 0;

    #[ORM\Column(name: 'interval_days', type: 'integer')]
    private int $intervalDays = 1;

    #[ORM\Column(name: 'ease_factor', type: 'float')]
    private float $easeFactor = 2.5;

    #[ORM\Column(name: 'next_review', type: 'date_immutable')]
    private \DateTimeImmutable $nextReview;

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

    public function getFlashcardId(): string
    {
        return $this->flashcardId;
    }

    public function setFlashcardId(string $flashcardId): void
    {
        $this->flashcardId = $flashcardId;
    }

    public function getRepetition(): int
    {
        return $this->repetition;
    }

    public function setRepetition(int $repetition): void
    {
        $this->repetition = $repetition;
    }

    public function getIntervalDays(): int
    {
        return $this->intervalDays;
    }

    public function setIntervalDays(int $intervalDays): void
    {
        $this->intervalDays = $intervalDays;
    }

    public function getEaseFactor(): float
    {
        return $this->easeFactor;
    }

    public function setEaseFactor(float $easeFactor): void
    {
        $this->easeFactor = $easeFactor;
    }

    public function getNextReview(): \DateTimeImmutable
    {
        return $this->nextReview;
    }

    public function setNextReview(\DateTimeImmutable $nextReview): void
    {
        $this->nextReview = $nextReview;
    }
}
