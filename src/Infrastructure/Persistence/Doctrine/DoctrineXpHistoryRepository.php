<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\XpHistoryRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\XpHistoryRecord;
use App\Shared\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineXpHistoryRepository implements XpHistoryRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(string $userId, int $xp, string $reason, string $createdAt): void
    {
        $record = new XpHistoryRecord();
        $record->setId(Id::generate('xph'));
        $record->setUserId($userId);
        $record->setXp($xp);
        $record->setReason($reason);
        $record->setCreatedAt(new \DateTimeImmutable($createdAt));

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }
}
