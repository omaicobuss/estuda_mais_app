<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Flashcard\Flashcard;

interface FlashcardRepositoryInterface
{
    public function nextId(): string;

    public function save(Flashcard $flashcard): void;

    public function findById(string $id): ?Flashcard;

    /**
     * @return array<int, Flashcard>
     */
    public function findByDeckId(string $deckId): array;
}
