<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\AnalyticsReadModelInterface;
use Doctrine\DBAL\Connection;

final class DoctrineAnalyticsReadModel implements AnalyticsReadModelInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function userOverview(string $userId, string $todayDate, string $sinceDateTime): array
    {
        $sessions = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS total, SUM(CASE WHEN ended_at IS NOT NULL THEN 1 ELSE 0 END) AS finished, SUM(answered_questions) AS answered, SUM(correct_answers) AS correct FROM study_sessions WHERE user_id = :user_id',
            ['user_id' => $userId]
        ) ?: [];

        $xp = $this->connection->fetchAssociative(
            'SELECT SUM(xp) AS total_xp, SUM(CASE WHEN created_at >= :since THEN xp ELSE 0 END) AS week_xp FROM xp_history WHERE user_id = :user_id',
            ['user_id' => $userId, 'since' => $sinceDateTime]
        ) ?: [];

        $cards = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS reviews_total, SUM(CASE WHEN next_review <= :today THEN 1 ELSE 0 END) AS reviews_due FROM card_reviews WHERE user_id = :user_id',
            ['user_id' => $userId, 'today' => $todayDate]
        ) ?: [];

        $authored = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS decks_created, (SELECT COUNT(*) FROM flashcards f WHERE f.deck_id IN (SELECT d.id FROM decks d WHERE d.creator_id = :user_id)) AS flashcards_created FROM decks WHERE creator_id = :user_id',
            ['user_id' => $userId]
        ) ?: [];

        $economy = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS purchases_total, COALESCE(SUM(price), 0) AS purchases_amount FROM purchases WHERE user_id = :user_id',
            ['user_id' => $userId]
        ) ?: [];

        $social = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS challenges_joined FROM challenge_participants WHERE user_id = :user_id',
            ['user_id' => $userId]
        ) ?: [];

        $lastStudyAt = $this->connection->fetchOne(
            'SELECT MAX(ended_at) FROM study_sessions WHERE user_id = :user_id AND ended_at IS NOT NULL',
            ['user_id' => $userId]
        );

        $answered = $this->intValue($sessions['answered'] ?? null);
        $correct = $this->intValue($sessions['correct'] ?? null);

        return [
            'sessions_total' => $this->intValue($sessions['total'] ?? null),
            'sessions_finished' => $this->intValue($sessions['finished'] ?? null),
            'answers_total' => $answered,
            'correct_total' => $correct,
            'avg_accuracy' => $answered > 0 ? round(($correct / $answered) * 100, 2) : 0.0,
            'xp_total' => $this->intValue($xp['total_xp'] ?? null),
            'xp_last_7_days' => $this->intValue($xp['week_xp'] ?? null),
            'reviews_total' => $this->intValue($cards['reviews_total'] ?? null),
            'reviews_due' => $this->intValue($cards['reviews_due'] ?? null),
            'decks_created' => $this->intValue($authored['decks_created'] ?? null),
            'flashcards_created' => $this->intValue($authored['flashcards_created'] ?? null),
            'purchases_total' => $this->intValue($economy['purchases_total'] ?? null),
            'purchases_amount' => round($this->floatValue($economy['purchases_amount'] ?? null), 2),
            'challenges_joined' => $this->intValue($social['challenges_joined'] ?? null),
            'last_study_at' => $lastStudyAt !== false ? (string) $lastStudyAt : null,
        ];
    }

    public function userDailySeries(string $userId, string $sinceDateTime): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT SUBSTR(started_at, 1, 10) AS study_day, COUNT(*) AS sessions, SUM(answered_questions) AS answered, SUM(correct_answers) AS correct FROM study_sessions WHERE user_id = :user_id AND started_at >= :since GROUP BY study_day ORDER BY study_day ASC',
            ['user_id' => $userId, 'since' => $sinceDateTime]
        );

        $items = [];
        foreach ($rows as $row) {
            $answered = $this->intValue($row['answered'] ?? null);
            $correct = $this->intValue($row['correct'] ?? null);

            $items[] = [
                'date' => (string) ($row['study_day'] ?? ''),
                'sessions' => $this->intValue($row['sessions'] ?? null),
                'answers' => $answered,
                'correct' => $correct,
                'accuracy' => $answered > 0 ? round(($correct / $answered) * 100, 2) : 0.0,
            ];
        }

        return $items;
    }

    private function intValue(mixed $value): int
    {
        return (int) ($value ?? 0);
    }

    private function floatValue(mixed $value): float
    {
        return (float) ($value ?? 0);
    }
}

