<?php

declare(strict_types=1);

namespace App\Application\Deck;

use App\Application\ApiException;
use App\Application\Contracts\DeckRepositoryInterface;
use App\Domain\Flashcard\Deck;
use App\Shared\Clock;

final class CreateDeckHandler
{
    public function __construct(
        private DeckRepositoryInterface $decks,
        private Clock $clock
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(string $creatorId, array $payload): array
    {
        $title = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $visibility = strtolower(trim((string) ($payload['visibility'] ?? 'public')));
        $price = round((float) ($payload['price'] ?? 0), 2);

        if ($title === '') {
            throw new ApiException('Campo obrigatorio: title.', 422);
        }
        if (!in_array($visibility, ['private', 'public', 'paid'], true)) {
            throw new ApiException('visibility invalida. Use private, public ou paid.', 422);
        }
        if ($price < 0) {
            throw new ApiException('price nao pode ser negativo.', 422);
        }
        if ($visibility === 'paid' && $price <= 0) {
            throw new ApiException('Deck paid precisa de price maior que zero.', 422);
        }

        $deck = Deck::create(
            $this->decks->nextId(),
            $title,
            $description,
            $creatorId,
            $visibility,
            $price,
            $this->clock->now()
        );

        $this->decks->save($deck);

        return $deck->toArray();
    }
}
