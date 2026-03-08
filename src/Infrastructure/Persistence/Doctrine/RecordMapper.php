<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Flashcard\Deck;
use App\Domain\Flashcard\Flashcard;
use App\Domain\Study\CardReview;
use App\Domain\Study\StudySession;
use App\Domain\User\User;
use App\Infrastructure\Persistence\Doctrine\Entity\CardReviewRecord;
use App\Infrastructure\Persistence\Doctrine\Entity\DeckRecord;
use App\Infrastructure\Persistence\Doctrine\Entity\FlashcardRecord;
use App\Infrastructure\Persistence\Doctrine\Entity\StudySessionRecord;
use App\Infrastructure\Persistence\Doctrine\Entity\UserRecord;

final class RecordMapper
{
    public function toUserDomain(UserRecord $record): User
    {
        return User::fromArray([
            'id' => $record->getId(),
            'email' => $record->getEmail(),
            'password_hash' => $record->getPasswordHash(),
            'name' => $record->getName(),
            'role' => $record->getRole(),
            'xp' => $record->getXp(),
            'level' => $record->getLevel(),
            'streak' => $record->getStreak(),
            'avatar_id' => $record->getAvatarId(),
            'last_study_date' => $record->getLastStudyDate()?->format('Y-m-d'),
            'created_at' => $record->getCreatedAt()->format(DATE_ATOM),
        ]);
    }

    public function toUserRecord(User $user, UserRecord $record): void
    {
        $data = $user->toArray();
        $record->setId((string) $data['id']);
        $record->setEmail((string) $data['email']);
        $record->setPasswordHash((string) $data['password_hash']);
        $record->setName((string) $data['name']);
        $record->setRole((string) $data['role']);
        $record->setXp((int) $data['xp']);
        $record->setLevel((int) $data['level']);
        $record->setStreak((int) $data['streak']);
        $record->setAvatarId(isset($data['avatar_id']) ? $data['avatar_id'] : null);
        $record->setCreatedAt(new \DateTimeImmutable((string) $data['created_at']));
        $record->setLastStudyDate(
            isset($data['last_study_date']) && $data['last_study_date'] !== null
                ? new \DateTimeImmutable((string) $data['last_study_date'])
                : null
        );
    }

    public function toDeckDomain(DeckRecord $record): Deck
    {
        return Deck::fromArray([
            'id' => $record->getId(),
            'title' => $record->getTitle(),
            'description' => $record->getDescription(),
            'creator_id' => $record->getCreatorId(),
            'visibility' => $record->getVisibility(),
            'price' => $record->getPrice(),
            'created_at' => $record->getCreatedAt()->format(DATE_ATOM),
        ]);
    }

    public function toDeckRecord(Deck $deck, DeckRecord $record): void
    {
        $data = $deck->toArray();
        $record->setId((string) $data['id']);
        $record->setTitle((string) $data['title']);
        $record->setDescription((string) ($data['description'] ?? ''));
        $record->setCreatorId((string) $data['creator_id']);
        $record->setVisibility((string) ($data['visibility'] ?? 'public'));
        $record->setPrice((float) ($data['price'] ?? 0));
        $record->setCreatedAt(new \DateTimeImmutable((string) $data['created_at']));
    }

    public function toFlashcardDomain(FlashcardRecord $record): Flashcard
    {
        return Flashcard::fromArray([
            'id' => $record->getId(),
            'deck_id' => $record->getDeckId(),
            'type' => $record->getType(),
            'question' => $record->getQuestion(),
            'answer' => $record->getAnswer(),
            'options' => $record->getOptions(),
        ]);
    }

    public function toFlashcardRecord(Flashcard $flashcard, FlashcardRecord $record): void
    {
        $data = $flashcard->toArray();
        $record->setId((string) $data['id']);
        $record->setDeckId((string) $data['deck_id']);
        $record->setType((string) $data['type']);
        $record->setQuestion((string) $data['question']);
        $record->setAnswer((string) $data['answer']);
        $record->setOptions(is_array($data['options'] ?? null) ? $data['options'] : []);
    }

    public function toStudySessionDomain(StudySessionRecord $record): StudySession
    {
        return StudySession::fromArray([
            'id' => $record->getId(),
            'user_id' => $record->getUserId(),
            'deck_id' => $record->getDeckId(),
            'total_questions' => $record->getTotalQuestions(),
            'answered_questions' => $record->getAnsweredQuestions(),
            'correct_answers' => $record->getCorrectAnswers(),
            'started_at' => $record->getStartedAt()->format(DATE_ATOM),
            'ended_at' => $record->getEndedAt()?->format(DATE_ATOM),
        ]);
    }

    public function toStudySessionRecord(StudySession $session, StudySessionRecord $record): void
    {
        $data = $session->toArray();
        $record->setId((string) $data['id']);
        $record->setUserId((string) $data['user_id']);
        $record->setDeckId((string) $data['deck_id']);
        $record->setTotalQuestions((int) $data['total_questions']);
        $record->setAnsweredQuestions((int) $data['answered_questions']);
        $record->setCorrectAnswers((int) $data['correct_answers']);
        $record->setStartedAt(new \DateTimeImmutable((string) $data['started_at']));
        $record->setEndedAt(
            isset($data['ended_at']) && $data['ended_at'] !== null
                ? new \DateTimeImmutable((string) $data['ended_at'])
                : null
        );
    }

    public function toCardReviewDomain(CardReviewRecord $record): CardReview
    {
        return CardReview::fromArray([
            'id' => $record->getId(),
            'user_id' => $record->getUserId(),
            'flashcard_id' => $record->getFlashcardId(),
            'repetition' => $record->getRepetition(),
            'interval' => $record->getIntervalDays(),
            'ease_factor' => $record->getEaseFactor(),
            'next_review' => $record->getNextReview()->format('Y-m-d'),
        ]);
    }

    public function toCardReviewRecord(CardReview $review, CardReviewRecord $record): void
    {
        $data = $review->toArray();
        $record->setId((string) $data['id']);
        $record->setUserId((string) $data['user_id']);
        $record->setFlashcardId((string) $data['flashcard_id']);
        $record->setRepetition((int) $data['repetition']);
        $record->setIntervalDays((int) $data['interval']);
        $record->setEaseFactor((float) $data['ease_factor']);
        $record->setNextReview(new \DateTimeImmutable((string) $data['next_review']));
    }
}
