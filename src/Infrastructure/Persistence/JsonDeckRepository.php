<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Contracts\DeckRepositoryInterface;
use App\Domain\Flashcard\Deck;
use App\Shared\Id;

final class JsonDeckRepository implements DeckRepositoryInterface
{
    private const TABLE = 'decks';

    public function __construct(private JsonFileStore $store)
    {
    }

    public function nextId(): string
    {
        return Id::generate('dek');
    }

    public function save(Deck $deck): void
    {
        $rows = $this->store->all(self::TABLE);
        $updated = false;

        foreach ($rows as $index => $row) {
            if (($row['id'] ?? null) === $deck->id()) {
                $rows[$index] = $deck->toArray();
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $rows[] = $deck->toArray();
        }

        $this->store->replaceAll(self::TABLE, $rows);
    }

    public function findById(string $id): ?Deck
    {
        foreach ($this->store->all(self::TABLE) as $row) {
            if (($row['id'] ?? null) === $id) {
                return Deck::fromArray($row);
            }
        }

        return null;
    }

    public function all(): array
    {
        return array_map(
            static fn (array $row): Deck => Deck::fromArray($row),
            $this->store->all(self::TABLE)
        );
    }
}
