<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface AnalyticsReadModelInterface
{
    /**
     * @return array<string, mixed>
     */
    public function userOverview(string $userId, string $todayDate, string $sinceDateTime): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function userDailySeries(string $userId, string $sinceDateTime): array;
}

