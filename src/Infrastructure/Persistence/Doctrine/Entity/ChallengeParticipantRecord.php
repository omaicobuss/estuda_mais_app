<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'challenge_participants')]
#[ORM\UniqueConstraint(name: 'uk_challenge_participant', columns: ['challenge_id', 'user_id'])]
#[ORM\Index(name: 'idx_challenge_participants_challenge_score', columns: ['challenge_id', 'score'])]
final class ChallengeParticipantRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 40)]
    private string $id;

    #[ORM\Column(name: 'challenge_id', type: 'string', length: 40)]
    private string $challengeId;

    #[ORM\Column(name: 'user_id', type: 'string', length: 40)]
    private string $userId;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $score = 0;

    #[ORM\Column(name: 'joined_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $joinedAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getChallengeId(): string
    {
        return $this->challengeId;
    }

    public function setChallengeId(string $challengeId): void
    {
        $this->challengeId = $challengeId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function getJoinedAt(): \DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeImmutable $joinedAt): void
    {
        $this->joinedAt = $joinedAt;
    }
}
