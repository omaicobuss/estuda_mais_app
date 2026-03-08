<?php

declare(strict_types=1);

namespace App\Application\Marketplace;

use App\Application\Contracts\PurchaseRepositoryInterface;

final class ListPurchasesHandler
{
    public function __construct(private PurchaseRepositoryInterface $purchases)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(string $userId): array
    {
        $items = [];
        foreach ($this->purchases->findByUser($userId) as $purchase) {
            $items[] = $purchase->toArray();
        }

        return $items;
    }
}
