<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\ChallengeParticipantRepositoryInterface;
use App\Domain\Challenge\ChallengeParticipant;
use App\Infrastructure\Persistence\Doctrine\Entity\ChallengeParticipantRecord;
use App\Shared\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineChallengeParticipantRepository implements ChallengeParticipantRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function nextId(): string
    {
        return Id::generate('chp');
    }

    public function save(ChallengeParticipant $participant): void
    {
        $data = $participant->toArray();
        $record = $this->entityManager->find(ChallengeParticipantRecord::class, (string) $data['id']) ?? new ChallengeParticipantRecord();

        $record->setId((string) $data['id']);
        $record->setChallengeId((string) $data['challenge_id']);
        $record->setUserId((string) $data['user_id']);
        $record->setScore((int) $data['score']);
        $record->setJoinedAt(new \DateTimeImmutable((string) $data['joined_at']));

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findByChallengeAndUser(string $challengeId, string $userId): ?ChallengeParticipant
    {
        /** @var ChallengeParticipantRecord|null $record */
        $record = $this->entityManager->getRepository(ChallengeParticipantRecord::class)->findOneBy([
            'challengeId' => $challengeId,
            'userId' => $userId,
        ]);
        if ($record === null) {
            return null;
        }

        return ChallengeParticipant::fromArray([
            'id' => $record->getId(),
            'challenge_id' => $record->getChallengeId(),
            'user_id' => $record->getUserId(),
            'score' => $record->getScore(),
            'joined_at' => $record->getJoinedAt()->format(DATE_ATOM),
        ]);
    }

    public function findByChallenge(string $challengeId): array
    {
        /** @var array<int, ChallengeParticipantRecord> $records */
        $records = $this->entityManager->getRepository(ChallengeParticipantRecord::class)->findBy(
            ['challengeId' => $challengeId],
            ['score' => 'DESC', 'joinedAt' => 'ASC']
        );

        $items = [];
        foreach ($records as $record) {
            $items[] = ChallengeParticipant::fromArray([
                'id' => $record->getId(),
                'challenge_id' => $record->getChallengeId(),
                'user_id' => $record->getUserId(),
                'score' => $record->getScore(),
                'joined_at' => $record->getJoinedAt()->format(DATE_ATOM),
            ]);
        }

        return $items;
    }
}
