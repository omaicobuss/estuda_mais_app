<?php

declare(strict_types=1);

namespace App\Application\Marketplace;

use App\Application\ApiException;
use App\Application\Contracts\DeckRepositoryInterface;
use App\Application\Contracts\PurchaseRepositoryInterface;
use App\Domain\Marketplace\Purchase;
use App\Shared\Clock;

final class BuyMarketplaceDeckHandler
{
    public function __construct(
        private DeckRepositoryInterface $decks,
        private PurchaseRepositoryInterface $purchases,
        private Clock $clock
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(string $userId, array $payload): array
    {
        $deckId = trim((string) ($payload['deck_id'] ?? ''));
        if ($deckId === '') {
            throw new ApiException('Campo obrigatorio: deck_id.', 422);
        }

        $deck = $this->decks->findById($deckId);
        if ($deck === null) {
            throw new ApiException('Deck nao encontrado.', 404);
        }
        if ($deck->visibility() !== 'paid' || $deck->price() <= 0) {
            throw new ApiException('Deck nao disponivel no marketplace.', 422);
        }
        if ($deck->creatorId() === $userId) {
            throw new ApiException('Criador do deck nao precisa comprar o proprio deck.', 422);
        }

        if ($this->purchases->findByUserAndDeck($userId, $deckId) !== null) {
            throw new ApiException('Deck ja adquirido.', 409);
        }

        $purchase = Purchase::createApproved(
            $this->purchases->nextId(),
            $userId,
            $deckId,
            $deck->price(),
            'simulated',
            $this->clock->now()
        );
        $this->purchases->save($purchase);

        return $purchase->toArray();
    }
}
