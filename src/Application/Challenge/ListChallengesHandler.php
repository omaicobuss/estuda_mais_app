<?php

declare(strict_types=1);

namespace App\Application\Challenge;

use App\Application\Contracts\ChallengeParticipantRepositoryInterface;
use App\Application\Contracts\ChallengeRepositoryInterface;
use App\Shared\Clock;

final class ListChallengesHandler
{
    public function __construct(
        private ChallengeRepositoryInterface $challenges,
        private ChallengeParticipantRepositoryInterface $participants,
        private Clock $clock
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(string $userId): array
    {
        $today = $this->clock->today()->format('Y-m-d');
        $items = [];

        foreach ($this->challenges->all() as $challenge) {
            $data = $challenge->toArray();
            $status = 'upcoming';
            if ($today >= $data['start_date'] && $today <= $data['end_date']) {
                $status = 'active';
            } elseif ($today > $data['end_date']) {
                $status = 'finished';
            }

            $items[] = $data + [
                'status' => $status,
                'joined' => $this->participants->findByChallengeAndUser($challenge->id(), $userId) !== null,
            ];
        }

        return $items;
    }
}
