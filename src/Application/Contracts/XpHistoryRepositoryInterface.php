<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface XpHistoryRepositoryInterface
{
    public function add(string $userId, int $xp, string $reason, string $createdAt): void;
}
