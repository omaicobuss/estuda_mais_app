<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\ChallengeRepositoryInterface;
use App\Domain\Challenge\Challenge;
use App\Infrastructure\Persistence\Doctrine\Entity\ChallengeRecord;
use App\Shared\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineChallengeRepository implements ChallengeRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function nextId(): string
    {
        return Id::generate('chl');
    }

    public function save(Challenge $challenge): void
    {
        $record = $this->entityManager->find(ChallengeRecord::class, $challenge->id()) ?? new ChallengeRecord();
        $data = $challenge->toArray();

        $record->setId((string) $data['id']);
        $record->setTitle((string) $data['title']);
        $record->setType((string) $data['type']);
        $record->setStartDate(new \DateTimeImmutable((string) $data['start_date']));
        $record->setEndDate(new \DateTimeImmutable((string) $data['end_date']));
        $record->setRewardXp((int) $data['reward_xp']);
        $record->setCreatedAt(new \DateTimeImmutable((string) $data['created_at']));

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Challenge
    {
        /** @var ChallengeRecord|null $record */
        $record = $this->entityManager->find(ChallengeRecord::class, $id);
        if ($record === null) {
            return null;
        }

        return Challenge::fromArray([
            'id' => $record->getId(),
            'title' => $record->getTitle(),
            'type' => $record->getType(),
            'start_date' => $record->getStartDate()->format('Y-m-d'),
            'end_date' => $record->getEndDate()->format('Y-m-d'),
            'reward_xp' => $record->getRewardXp(),
            'created_at' => $record->getCreatedAt()->format(DATE_ATOM),
        ]);
    }

    public function all(): array
    {
        /** @var array<int, ChallengeRecord> $records */
        $records = $this->entityManager->getRepository(ChallengeRecord::class)->findBy([], ['startDate' => 'DESC']);

        $items = [];
        foreach ($records as $record) {
            $items[] = Challenge::fromArray([
                'id' => $record->getId(),
                'title' => $record->getTitle(),
                'type' => $record->getType(),
                'start_date' => $record->getStartDate()->format('Y-m-d'),
                'end_date' => $record->getEndDate()->format('Y-m-d'),
                'reward_xp' => $record->getRewardXp(),
                'created_at' => $record->getCreatedAt()->format(DATE_ATOM),
            ]);
        }

        return $items;
    }
}
