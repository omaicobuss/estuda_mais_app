<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Contracts\CardReviewRepositoryInterface;
use App\Domain\Study\CardReview;
use App\Shared\Id;

final class JsonCardReviewRepository implements CardReviewRepositoryInterface
{
    private const TABLE = 'card_reviews';

    public function __construct(private JsonFileStore $store)
    {
    }

    public function nextId(): string
    {
        return Id::generate('rev');
    }

    public function save(CardReview $review): void
    {
        $rows = $this->store->all(self::TABLE);
        $updated = false;

        foreach ($rows as $index => $row) {
            if (($row['id'] ?? null) === $review->id()) {
                $rows[$index] = $review->toArray();
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $rows[] = $review->toArray();
        }

        $this->store->replaceAll(self::TABLE, $rows);
    }

    public function findByUserAndFlashcard(string $userId, string $flashcardId): ?CardReview
    {
        foreach ($this->store->all(self::TABLE) as $row) {
            if (($row['user_id'] ?? null) === $userId && ($row['flashcard_id'] ?? null) === $flashcardId) {
                return CardReview::fromArray($row);
            }
        }

        return null;
    }
}
