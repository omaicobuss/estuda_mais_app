<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Contracts\FlashcardRepositoryInterface;
use App\Domain\Flashcard\Flashcard;
use App\Shared\Id;

final class JsonFlashcardRepository implements FlashcardRepositoryInterface
{
    private const TABLE = 'flashcards';

    public function __construct(private JsonFileStore $store)
    {
    }

    public function nextId(): string
    {
        return Id::generate('crd');
    }

    public function save(Flashcard $flashcard): void
    {
        $rows = $this->store->all(self::TABLE);
        $updated = false;

        foreach ($rows as $index => $row) {
            if (($row['id'] ?? null) === $flashcard->id()) {
                $rows[$index] = $flashcard->toArray();
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $rows[] = $flashcard->toArray();
        }

        $this->store->replaceAll(self::TABLE, $rows);
    }

    public function findById(string $id): ?Flashcard
    {
        foreach ($this->store->all(self::TABLE) as $row) {
            if (($row['id'] ?? null) === $id) {
                return Flashcard::fromArray($row);
            }
        }

        return null;
    }

    public function findByDeckId(string $deckId): array
    {
        $cards = [];
        foreach ($this->store->all(self::TABLE) as $row) {
            if (($row['deck_id'] ?? null) === $deckId) {
                $cards[] = Flashcard::fromArray($row);
            }
        }

        return $cards;
    }
}
