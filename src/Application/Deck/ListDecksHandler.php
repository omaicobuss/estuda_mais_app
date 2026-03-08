<?php

declare(strict_types=1);

namespace App\Application\Deck;

use App\Application\Contracts\DeckRepositoryInterface;

final class ListDecksHandler
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
            if ($deck->visibility() === 'private') {
                continue;
            }
            $items[] = $deck->toArray();
        }

        return $items;
    }
}
