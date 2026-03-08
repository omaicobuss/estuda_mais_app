<?php

declare(strict_types=1);

namespace App\Application\Study;

use App\Application\ApiException;
use App\Application\Contracts\CardReviewRepositoryInterface;
use App\Application\Contracts\DeckRepositoryInterface;
use App\Application\Contracts\FlashcardRepositoryInterface;
use App\Application\Contracts\PurchaseRepositoryInterface;
use App\Application\Contracts\StudySessionRepositoryInterface;
use App\Domain\Study\StudySession;
use App\Shared\Clock;

final class StartStudyHandler
{
    public function __construct(
        private DeckRepositoryInterface $decks,
        private FlashcardRepositoryInterface $flashcards,
        private CardReviewRepositoryInterface $reviews,
        private PurchaseRepositoryInterface $purchases,
        private StudySessionRepositoryInterface $sessions,
        private Clock $clock
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(string $userId, array $payload): array
    {
        $deckId = (string) ($payload['deck_id'] ?? '');
        if ($deckId === '') {
            throw new ApiException('Campo obrigatorio: deck_id.', 422);
        }

        $deck = $this->decks->findById($deckId);
        if ($deck === null) {
            throw new ApiException('Deck nao encontrado.', 404);
        }
        if ($deck->visibility() === 'private' && $deck->creatorId() !== $userId) {
            throw new ApiException('Deck privado indisponivel para este usuario.', 403);
        }
        if (
            $deck->visibility() === 'paid'
            && $deck->creatorId() !== $userId
            && $this->purchases->findByUserAndDeck($userId, $deckId) === null
        ) {
            throw new ApiException('Deck pago nao adquirido.', 403);
        }

        $deckCards = $this->flashcards->findByDeckId($deckId);
        if ($deckCards === []) {
            throw new ApiException('Deck sem flashcards.', 422);
        }

        $today = $this->clock->today();
        $dueCards = [];
        foreach ($deckCards as $card) {
            $review = $this->reviews->findByUserAndFlashcard($userId, $card->id());
            if ($review === null || $review->isDue($today)) {
                $dueCards[] = $card;
            }
        }

        if ($dueCards === []) {
            $dueCards = $deckCards;
        }

        $session = StudySession::create(
            $this->sessions->nextId(),
            $userId,
            $deckId,
            count($dueCards),
            $this->clock->now()
        );
        $this->sessions->save($session);

        return [
            'session_id' => $session->id(),
            'deck_id' => $deckId,
            'cards' => array_map(
                static fn ($card): array => [
                    'id' => $card->id(),
                    'question' => $card->question(),
                    'type' => $card->type(),
                    'options' => $card->options(),
                ],
                $dueCards
            ),
        ];
    }
}
