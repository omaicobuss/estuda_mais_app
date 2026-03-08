<?php

declare(strict_types=1);

namespace App\Application\Challenge;

use App\Application\ApiException;
use App\Application\Contracts\ChallengeParticipantRepositoryInterface;
use App\Application\Contracts\ChallengeRepositoryInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Shared\Clock;

final class GetChallengeDetailsHandler
{
    public function __construct(
        private ChallengeRepositoryInterface $challenges,
        private ChallengeParticipantRepositoryInterface $participants,
        private UserRepositoryInterface $users,
        private Clock $clock
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(string $userId, string $challengeId): array
    {
        $challenge = $this->challenges->findById($challengeId);
        if ($challenge === null) {
            throw new ApiException('Desafio nao encontrado.', 404);
        }

        $challengeData = $challenge->toArray();
        $ranking = [];
        $position = 1;

        foreach ($this->participants->findByChallenge($challengeId) as $participant) {
            $participantData = $participant->toArray();
            $participantUser = $this->users->findById((string) $participantData['user_id']);

            $ranking[] = [
                'position' => $position,
                'user_id' => $participantData['user_id'],
                'name' => $participantUser?->name() ?? 'Usuario removido',
                'score' => $participantData['score'],
                'joined_at' => $participantData['joined_at'],
            ];
            $position++;
        }

        return $challengeData + [
            'status' => $this->status($challengeData),
            'joined' => $this->participants->findByChallengeAndUser($challengeId, $userId) !== null,
            'participants_count' => count($ranking),
            'participants' => $ranking,
        ];
    }

    /**
     * @param array<string, mixed> $challenge
     */
    private function status(array $challenge): string
    {
        $today = $this->clock->today()->format('Y-m-d');
        if ($today >= $challenge['start_date'] && $today <= $challenge['end_date']) {
            return 'active';
        }
        if ($today > $challenge['end_date']) {
            return 'finished';
        }

        return 'upcoming';
    }
}

