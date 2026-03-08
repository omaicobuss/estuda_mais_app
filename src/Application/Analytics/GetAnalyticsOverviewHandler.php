<?php

declare(strict_types=1);

namespace App\Application\Analytics;

use App\Application\ApiException;
use App\Application\Contracts\AnalyticsReadModelInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Application\Ranking\GetGlobalRankingHandler;
use App\Shared\Clock;

final class GetAnalyticsOverviewHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AnalyticsReadModelInterface $analytics,
        private GetGlobalRankingHandler $globalRanking,
        private Clock $clock
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(string $userId): array
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new ApiException('Usuario nao encontrado.', 404);
        }

        $today = $this->clock->today();
        $todayDate = $today->format('Y-m-d');
        $sinceDateTime = $today->modify('-6 days')->format('Y-m-d 00:00:00');

        $overview = $this->analytics->userOverview($userId, $todayDate, $sinceDateTime);
        $series = $this->filledSeries($this->analytics->userDailySeries($userId, $sinceDateTime), $today);

        $position = null;
        foreach ($this->globalRanking->handle() as $item) {
            if (($item['user_id'] ?? null) === $userId) {
                $position = (int) ($item['position'] ?? 0);
                break;
            }
        }

        return [
            'student' => [
                'id' => $user->id(),
                'name' => $user->name(),
                'xp' => $user->xp(),
                'level' => $user->level(),
                'streak' => $user->streak(),
                'ranking_position' => $position,
            ],
            'overview' => $overview,
            'daily_last_7_days' => $series,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $series
     * @return array<int, array<string, mixed>>
     */
    private function filledSeries(array $series, \DateTimeImmutable $today): array
    {
        $indexed = [];
        foreach ($series as $item) {
            $date = (string) ($item['date'] ?? '');
            if ($date !== '') {
                $indexed[$date] = $item;
            }
        }

        $items = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->modify(sprintf('-%d days', $i))->format('Y-m-d');
            $items[] = $indexed[$date] ?? [
                'date' => $date,
                'sessions' => 0,
                'answers' => 0,
                'correct' => 0,
                'accuracy' => 0.0,
            ];
        }

        return $items;
    }
}

