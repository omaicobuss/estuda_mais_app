<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Challenge\ChallengeParticipant;

interface ChallengeParticipantRepositoryInterface
{
    public function nextId(): string;

    public function save(ChallengeParticipant $participant): void;

    public function findByChallengeAndUser(string $challengeId, string $userId): ?ChallengeParticipant;

    /**
     * @return array<int, ChallengeParticipant>
     */
    public function findByChallenge(string $challengeId): array;
}
