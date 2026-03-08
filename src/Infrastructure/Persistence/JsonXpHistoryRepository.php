<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Contracts\XpHistoryRepositoryInterface;
use App\Shared\Id;

final class JsonXpHistoryRepository implements XpHistoryRepositoryInterface
{
    private const TABLE = 'xp_history';

    public function __construct(private JsonFileStore $store)
    {
    }

    public function add(string $userId, int $xp, string $reason, string $createdAt): void
    {
        $rows = $this->store->all(self::TABLE);
        $rows[] = [
            'id' => Id::generate('xph'),
            'user_id' => $userId,
            'xp' => $xp,
            'reason' => $reason,
            'created_at' => $createdAt,
        ];

        $this->store->replaceAll(self::TABLE, $rows);
    }
}
