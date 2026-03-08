<?php

declare(strict_types=1);

namespace App\Application\Flashcard;

use App\Application\ApiException;
use App\Application\Contracts\DeckRepositoryInterface;
use App\Application\Contracts\FlashcardRepositoryInterface;
use App\Domain\Flashcard\Flashcard;

final class CreateFlashcardHandler
{
    public function __construct(
        private DeckRepositoryInterface $decks,
        private FlashcardRepositoryInterface $flashcards
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(array $payload): array
    {
        $deckId = (string) ($payload['deck_id'] ?? '');
        $question = trim((string) ($payload['question'] ?? ''));
        $answer = trim((string) ($payload['answer'] ?? ''));
        $type = (string) ($payload['type'] ?? 'QA');
        $options = isset($payload['options']) && is_array($payload['options']) ? $payload['options'] : [];

        if ($deckId === '' || $question === '' || $answer === '') {
            throw new ApiException('Campos obrigatorios: deck_id, question e answer.', 422);
        }

        if ($this->decks->findById($deckId) === null) {
            throw new ApiException('Deck nao encontrado.', 404);
        }

        $flashcard = Flashcard::create(
            $this->flashcards->nextId(),
            $deckId,
            $type,
            $question,
            $answer,
            $options
        );

        $this->flashcards->save($flashcard);

        return $flashcard->toArray();
    }
}
