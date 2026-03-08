<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Study\CardReview;

interface CardReviewRepositoryInterface
{
    public function nextId(): string;

    public function save(CardReview $review): void;

    public function findByUserAndFlashcard(string $userId, string $flashcardId): ?CardReview;
}
