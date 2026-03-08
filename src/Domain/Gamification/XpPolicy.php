<?php

declare(strict_types=1);

namespace App\Domain\Gamification;

use App\Domain\Study\StudySession;

final class XpPolicy
{
    public const COMPLETE_DECK_XP = 50;
    public const DAILY_STREAK_XP = 10;
    public const HIGH_ACCURACY_XP = 20;

    /**
     * @return array<int, array{reason: string, xp: int}>
     */
    public function rewardsForSession(StudySession $session, bool $firstStudyOfTheDay): array
    {
        $rewards = [];

        if ($session->isCompleted()) {
            $rewards[] = ['reason' => 'DECK_COMPLETED', 'xp' => self::COMPLETE_DECK_XP];
        }

        if ($session->accuracy() >= 0.8) {
            $rewards[] = ['reason' => 'HIGH_ACCURACY', 'xp' => self::HIGH_ACCURACY_XP];
        }

        if ($firstStudyOfTheDay) {
            $rewards[] = ['reason' => 'DAILY_STREAK', 'xp' => self::DAILY_STREAK_XP];
        }

        return $rewards;
    }
}
