<?php

declare(strict_types=1);

namespace App\Application\Ai;

use App\Application\ApiException;
use App\Application\Contracts\AiCardGeneratorInterface;
use App\Application\Contracts\DeckRepositoryInterface;
use App\Application\Contracts\FlashcardRepositoryInterface;
use App\Domain\Flashcard\Flashcard;

final class GenerateAiFlashcardsHandler
{
    public function __construct(
        private DeckRepositoryInterface $decks,
        private FlashcardRepositoryInterface $flashcards,
        private AiCardGeneratorInterface $generator
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(string $userId, array $payload): array
    {
        $deckId = trim((string) ($payload['deck_id'] ?? ''));
        $topic = trim((string) ($payload['topic'] ?? ''));
        $sourceText = isset($payload['source_text']) ? (string) $payload['source_text'] : null;
        $count = (int) ($payload['count'] ?? 5);
        $persist = filter_var($payload['persist'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        if ($deckId === '') {
            throw new ApiException('Campo obrigatorio: deck_id.', 422);
        }
        if ($topic === '') {
            throw new ApiException('Campo obrigatorio: topic.', 422);
        }
        if ($count < 1 || $count > 20) {
            throw new ApiException('count deve estar entre 1 e 20.', 422);
        }

        $deck = $this->decks->findById($deckId);
        if ($deck === null) {
            throw new ApiException('Deck nao encontrado.', 404);
        }
        if ($deck->creatorId() !== $userId) {
            throw new ApiException('Apenas o criador pode gerar cards para este deck.', 403);
        }

        $generated = $this->generator->generate($topic, $count, $sourceText);
        $shouldPersist = $persist !== false;

        $cards = [];
        foreach ($generated as $item) {
            $flashcard = Flashcard::create(
                $this->flashcards->nextId(),
                $deckId,
                (string) ($item['type'] ?? 'QA'),
                (string) ($item['question'] ?? ''),
                (string) ($item['answer'] ?? ''),
                is_array($item['options'] ?? null) ? $item['options'] : []
            );

            if ($shouldPersist) {
                $this->flashcards->save($flashcard);
            }

            $cards[] = $flashcard->toArray();
        }

        return [
            'deck_id' => $deckId,
            'topic' => $topic,
            'persisted' => $shouldPersist,
            'generated_count' => count($cards),
            'cards' => $cards,
        ];
    }
}

