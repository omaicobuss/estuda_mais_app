<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Marketplace\Purchase;

interface PurchaseRepositoryInterface
{
    public function nextId(): string;

    public function save(Purchase $purchase): void;

    public function findByUserAndDeck(string $userId, string $deckId): ?Purchase;

    /**
     * @return array<int, Purchase>
     */
    public function findByUser(string $userId): array;
}
