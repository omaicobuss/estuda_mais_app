<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\StudySessionRepositoryInterface;
use App\Domain\Study\StudySession;
use App\Infrastructure\Persistence\Doctrine\Entity\StudySessionRecord;
use App\Shared\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineStudySessionRepository implements StudySessionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordMapper $mapper
    ) {
    }

    public function nextId(): string
    {
        return Id::generate('ses');
    }

    public function save(StudySession $session): void
    {
        $record = $this->entityManager->find(StudySessionRecord::class, $session->id()) ?? new StudySessionRecord();
        $this->mapper->toStudySessionRecord($session, $record);
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?StudySession
    {
        /** @var StudySessionRecord|null $record */
        $record = $this->entityManager->find(StudySessionRecord::class, $id);

        return $record ? $this->mapper->toStudySessionDomain($record) : null;
    }
}
