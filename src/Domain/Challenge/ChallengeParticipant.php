<?php

declare(strict_types=1);

namespace App\Domain\Challenge;

use DateTimeImmutable;

final class ChallengeParticipant
{
    public function __construct(
        private string $id,
        private string $challengeId,
        private string $userId,
        private int $score,
        private string $joinedAt
    ) {
    }

    public static function create(
        string $id,
        string $challengeId,
        string $userId,
        DateTimeImmutable $joinedAt
    ): self {
        return new self($id, $challengeId, $userId, 0, $joinedAt->format(DATE_ATOM));
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['challenge_id'],
            (string) $data['user_id'],
            (int) ($data['score'] ?? 0),
            (string) $data['joined_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'challenge_id' => $this->challengeId,
            'user_id' => $this->userId,
            'score' => $this->score,
            'joined_at' => $this->joinedAt,
        ];
    }

    public function challengeId(): string
    {
        return $this->challengeId;
    }

    public function userId(): string
    {
        return $this->userId;
    }
}
