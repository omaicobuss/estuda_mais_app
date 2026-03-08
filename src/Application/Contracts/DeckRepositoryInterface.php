<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Flashcard\Deck;

interface DeckRepositoryInterface
{
    public function nextId(): string;

    public function save(Deck $deck): void;

    public function findById(string $id): ?Deck;

    /**
     * @return array<int, Deck>
     */
    public function all(): array;
}
