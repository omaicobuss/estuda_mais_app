<?php

declare(strict_types=1);

namespace App\Domain\Study;

use DateTimeImmutable;

final class StudySession
{
    public function __construct(
        private string $id,
        private string $userId,
        private string $deckId,
        private int $totalQuestions,
        private int $answeredQuestions,
        private int $correctAnswers,
        private string $startedAt,
        private ?string $endedAt
    ) {
    }

    public static function create(
        string $id,
        string $userId,
        string $deckId,
        int $totalQuestions,
        DateTimeImmutable $startedAt
    ): self {
        return new self(
            $id,
            $userId,
            $deckId,
            max(0, $totalQuestions),
            0,
            0,
            $startedAt->format(DATE_ATOM),
            null
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['user_id'],
            (string) $data['deck_id'],
            (int) ($data['total_questions'] ?? 0),
            (int) ($data['answered_questions'] ?? 0),
            (int) ($data['correct_answers'] ?? 0),
            (string) $data['started_at'],
            isset($data['ended_at']) ? (string) $data['ended_at'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'deck_id' => $this->deckId,
            'total_questions' => $this->totalQuestions,
            'answered_questions' => $this->answeredQuestions,
            'correct_answers' => $this->correctAnswers,
            'started_at' => $this->startedAt,
            'ended_at' => $this->endedAt,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function deckId(): string
    {
        return $this->deckId;
    }

    public function totalQuestions(): int
    {
        return $this->totalQuestions;
    }

    public function answeredQuestions(): int
    {
        return $this->answeredQuestions;
    }

    public function correctAnswers(): int
    {
        return $this->correctAnswers;
    }

    public function isFinished(): bool
    {
        return $this->endedAt !== null;
    }

    public function registerAnswer(bool $correct): void
    {
        if ($this->isFinished()) {
            return;
        }

        $this->answeredQuestions++;
        if ($correct) {
            $this->correctAnswers++;
        }
    }

    public function finish(DateTimeImmutable $endedAt): void
    {
        if ($this->isFinished()) {
            return;
        }

        $this->endedAt = $endedAt->format(DATE_ATOM);
    }

    public function isCompleted(): bool
    {
        return $this->totalQuestions > 0 && $this->answeredQuestions >= $this->totalQuestions;
    }

    public function accuracy(): float
    {
        if ($this->answeredQuestions === 0) {
            return 0.0;
        }

        return $this->correctAnswers / $this->answeredQuestions;
    }
}
