<?php

declare(strict_types=1);

namespace App\Application\Study;

use App\Application\ApiException;
use App\Application\Contracts\CardReviewRepositoryInterface;
use App\Application\Contracts\FlashcardRepositoryInterface;
use App\Application\Contracts\StudySessionRepositoryInterface;
use App\Domain\Study\CardReview;
use App\Shared\Clock;

final class AnswerStudyHandler
{
    public function __construct(
        private StudySessionRepositoryInterface $sessions,
        private FlashcardRepositoryInterface $flashcards,
        private CardReviewRepositoryInterface $reviews,
        private Clock $clock
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(string $userId, array $payload): array
    {
        $sessionId = (string) ($payload['session_id'] ?? '');
        $flashcardId = (string) ($payload['flashcard_id'] ?? '');
        $userAnswer = isset($payload['user_answer']) ? trim((string) $payload['user_answer']) : null;
        $isCorrect = filter_var($payload['correct'] ?? null, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        if ($sessionId === '' || $flashcardId === '') {
            throw new ApiException('Campos obrigatorios: session_id e flashcard_id.', 422);
        }

        $session = $this->sessions->findById($sessionId);
        if ($session === null || $session->userId() !== $userId) {
            throw new ApiException('Sessao de estudo nao encontrada.', 404);
        }

        if ($session->isFinished()) {
            throw new ApiException('Sessao de estudo ja finalizada.', 409);
        }

        if ($session->answeredQuestions() >= $session->totalQuestions()) {
            throw new ApiException('Todas as questoes da sessao ja foram respondidas.', 409);
        }

        $flashcard = $this->flashcards->findById($flashcardId);
        if ($flashcard === null) {
            throw new ApiException('Flashcard nao encontrado.', 404);
        }
        if ($flashcard->deckId() !== $session->deckId()) {
            throw new ApiException('Flashcard nao pertence ao deck da sessao.', 422);
        }

        if ($userAnswer !== null) {
            $isCorrect = strcasecmp($userAnswer, trim($flashcard->answer())) === 0;
        }

        if ($isCorrect === null) {
            throw new ApiException('Informe user_answer ou correct.', 422);
        }

        $session->registerAnswer($isCorrect);
        $this->sessions->save($session);

        $review = $this->reviews->findByUserAndFlashcard($userId, $flashcardId);
        if ($review === null) {
            $review = CardReview::create(
                $this->reviews->nextId(),
                $userId,
                $flashcardId,
                $this->clock->today()
            );
        }

        $review->registerResult($isCorrect, $this->clock->today());
        $this->reviews->save($review);

        return [
            'session_id' => $session->id(),
            'answered_questions' => $session->answeredQuestions(),
            'correct_answers' => $session->correctAnswers(),
            'accuracy' => $session->accuracy(),
            'next_review' => $review->nextReview(),
        ];
    }
}
