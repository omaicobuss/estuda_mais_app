<?php

declare(strict_types=1);

namespace App\Application\Challenge;

use App\Application\ApiException;
use App\Application\Contracts\ChallengeParticipantRepositoryInterface;
use App\Application\Contracts\ChallengeRepositoryInterface;
use App\Domain\Challenge\ChallengeParticipant;
use App\Shared\Clock;

final class JoinChallengeHandler
{
    public function __construct(
        private ChallengeRepositoryInterface $challenges,
        private ChallengeParticipantRepositoryInterface $participants,
        private Clock $clock
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(string $userId, array $payload): array
    {
        $challengeId = trim((string) ($payload['challenge_id'] ?? ''));
        if ($challengeId === '') {
            throw new ApiException('Campo obrigatorio: challenge_id.', 422);
        }

        $challenge = $this->challenges->findById($challengeId);
        if ($challenge === null) {
            throw new ApiException('Desafio nao encontrado.', 404);
        }

        if (!$challenge->isOpenOn($this->clock->today())) {
            throw new ApiException('Desafio fora do periodo ativo.', 422);
        }

        if ($this->participants->findByChallengeAndUser($challengeId, $userId) !== null) {
            throw new ApiException('Usuario ja inscrito no desafio.', 409);
        }

        $participant = ChallengeParticipant::create(
            $this->participants->nextId(),
            $challengeId,
            $userId,
            $this->clock->now()
        );
        $this->participants->save($participant);

        return $participant->toArray();
    }
}

