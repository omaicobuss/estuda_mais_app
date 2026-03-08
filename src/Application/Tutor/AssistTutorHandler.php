<?php

declare(strict_types=1);

namespace App\Application\Tutor;

use App\Application\ApiException;
use App\Application\Contracts\AnalyticsReadModelInterface;
use App\Application\Contracts\DeckRepositoryInterface;
use App\Application\Contracts\FlashcardRepositoryInterface;
use App\Application\Contracts\PurchaseRepositoryInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Shared\Clock;

final class AssistTutorHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private DeckRepositoryInterface $decks,
        private FlashcardRepositoryInterface $flashcards,
        private PurchaseRepositoryInterface $purchases,
        private AnalyticsReadModelInterface $analytics,
        private Clock $clock
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(string $userId, array $payload): array
    {
        $question = trim((string) ($payload['question'] ?? ''));
        $deckId = trim((string) ($payload['deck_id'] ?? ''));

        if ($question === '') {
            throw new ApiException('Campo obrigatorio: question.', 422);
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new ApiException('Usuario nao encontrado.', 404);
        }

        $todayDate = $this->clock->today()->format('Y-m-d');
        $sinceDateTime = $this->clock->now()->modify('-7 days')->format('Y-m-d H:i:s');
        $overview = $this->analytics->userOverview($userId, $todayDate, $sinceDateTime);

        $relatedCards = [];
        $deckContext = null;
        if ($deckId !== '') {
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

            $deckContext = $deck->toArray();
            $relatedCards = $this->relatedCards($question, $this->flashcards->findByDeckId($deckId));
        }

        $accuracy = (float) ($overview['avg_accuracy'] ?? 0.0);
        $sessions = (int) ($overview['sessions_finished'] ?? 0);
        $due = (int) ($overview['reviews_due'] ?? 0);

        $stage = 'intermediate';
        if ($sessions < 3 || $accuracy < 60) {
            $stage = 'beginner';
        } elseif ($accuracy >= 85 && $sessions >= 8) {
            $stage = 'advanced';
        }

        $actions = [];
        if ($due > 0) {
            $actions[] = sprintf('Priorize %d cards vencidos hoje.', $due);
        }
        if ($accuracy < 70) {
            $actions[] = 'Refaca um deck curto com foco em acuracia acima de 80%.';
        } else {
            $actions[] = 'Mantenha sessoes curtas e frequentes para consolidar o aprendizado.';
        }
        if ($deckContext !== null && $relatedCards === []) {
            $actions[] = 'No deck atual, comece pelas perguntas conceituais antes das objetivas.';
        }
        if ($relatedCards !== []) {
            $actions[] = 'Use as perguntas relacionadas abaixo como revisao dirigida.';
        }

        return [
            'question' => $question,
            'student' => [
                'id' => $user->id(),
                'name' => $user->name(),
                'level' => $user->level(),
                'xp' => $user->xp(),
                'streak' => $user->streak(),
            ],
            'diagnosis' => [
                'stage' => $stage,
                'sessions_finished' => $sessions,
                'avg_accuracy' => $accuracy,
                'reviews_due' => $due,
            ],
            'deck_context' => $deckContext,
            'related_cards' => $relatedCards,
            'next_actions' => $actions,
        ];
    }

    /**
     * @param array<int, mixed> $cards
     * @return array<int, array<string, mixed>>
     */
    private function relatedCards(string $question, array $cards): array
    {
        $keywords = $this->keywords($question);
        if ($keywords === []) {
            return [];
        }

        $matches = [];
        foreach ($cards as $card) {
            $text = strtolower($card->question() . ' ' . $card->answer());
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $score++;
                }
            }

            if ($score > 0) {
                $matches[] = [
                    'score' => $score,
                    'id' => $card->id(),
                    'question' => $card->question(),
                    'answer' => $card->answer(),
                    'type' => $card->type(),
                ];
            }
        }

        usort(
            $matches,
            static fn (array $a, array $b): int => $b['score'] <=> $a['score']
        );

        return array_slice($matches, 0, 5);
    }

    /**
     * @return array<int, string>
     */
    private function keywords(string $question): array
    {
        $words = preg_split('/[^a-z0-9]+/i', strtolower($question)) ?: [];
        $stop = [
            'a', 'o', 'e', 'de', 'do', 'da', 'em', 'para', 'por', 'com', 'sem', 'que', 'como',
            'qual', 'quais', 'um', 'uma', 'os', 'as', 'no', 'na', 'nos', 'nas',
        ];

        $tokens = [];
        foreach ($words as $word) {
            $word = trim($word);
            if ($word === '' || strlen($word) < 3 || in_array($word, $stop, true)) {
                continue;
            }
            $tokens[$word] = true;
        }

        return array_keys($tokens);
    }
}

