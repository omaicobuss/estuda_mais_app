<?php

declare(strict_types=1);

namespace App\Application\Marketplace;

use App\Application\Contracts\DeckRepositoryInterface;

final class ListMarketplaceDecksHandler
{
    public function __construct(private DeckRepositoryInterface $decks)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        $items = [];
        foreach ($this->decks->all() as $deck) {
            if ($deck->visibility() !== 'paid' || $deck->price() <= 0) {
                continue;
            }

            $items[] = $deck->toArray();
        }

        return $items;
    }
}
