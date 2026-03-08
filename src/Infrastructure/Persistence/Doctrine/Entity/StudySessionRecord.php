<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'study_sessions')]
#[ORM\Index(name: 'idx_study_sessions_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_study_sessions_deck_id', columns: ['deck_id'])]
final class StudySessionRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 40)]
    private string $id;

    #[ORM\Column(name: 'user_id', type: 'string', length: 40)]
    private string $userId;

    #[ORM\Column(name: 'deck_id', type: 'string', length: 40)]
    private string $deckId;

    #[ORM\Column(name: 'total_questions', type: 'integer')]
    private int $totalQuestions = 0;

    #[ORM\Column(name: 'answered_questions', type: 'integer')]
    private int $answeredQuestions = 0;

    #[ORM\Column(name: 'correct_answers', type: 'integer')]
    private int $correctAnswers = 0;

    #[ORM\Column(name: 'started_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(name: 'ended_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getDeckId(): string
    {
        return $this->deckId;
    }

    public function setDeckId(string $deckId): void
    {
        $this->deckId = $deckId;
    }

    public function getTotalQuestions(): int
    {
        return $this->totalQuestions;
    }

    public function setTotalQuestions(int $totalQuestions): void
    {
        $this->totalQuestions = $totalQuestions;
    }

    public function getAnsweredQuestions(): int
    {
        return $this->answeredQuestions;
    }

    public function setAnsweredQuestions(int $answeredQuestions): void
    {
        $this->answeredQuestions = $answeredQuestions;
    }

    public function getCorrectAnswers(): int
    {
        return $this->correctAnswers;
    }

    public function setCorrectAnswers(int $correctAnswers): void
    {
        $this->correctAnswers = $correctAnswers;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): void
    {
        $this->endedAt = $endedAt;
    }
}
