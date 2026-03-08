<?php

declare(strict_types=1);

namespace App\Application\Ranking;

use App\Application\Contracts\UserRepositoryInterface;

final class GetGlobalRankingHandler
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(int $limit = 50): array
    {
        $all = $this->users->all();
        usort($all, static function ($a, $b): int {
            if ($a->xp() !== $b->xp()) {
                return $b->xp() <=> $a->xp();
            }

            if ($a->streak() !== $b->streak()) {
                return $b->streak() <=> $a->streak();
            }

            return strcmp($a->name(), $b->name());
        });

        $ranking = [];
        foreach (array_slice($all, 0, $limit) as $index => $user) {
            $ranking[] = [
                'position' => $index + 1,
                'user_id' => $user->id(),
                'name' => $user->name(),
                'xp' => $user->xp(),
                'level' => $user->level(),
                'streak' => $user->streak(),
            ];
        }

        return $ranking;
    }
}
