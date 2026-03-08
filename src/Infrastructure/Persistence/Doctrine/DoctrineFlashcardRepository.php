<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\FlashcardRepositoryInterface;
use App\Domain\Flashcard\Flashcard;
use App\Infrastructure\Persistence\Doctrine\Entity\FlashcardRecord;
use App\Shared\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineFlashcardRepository implements FlashcardRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordMapper $mapper
    ) {
    }

    public function nextId(): string
    {
        return Id::generate('crd');
    }

    public function save(Flashcard $flashcard): void
    {
        $record = $this->entityManager->find(FlashcardRecord::class, $flashcard->id()) ?? new FlashcardRecord();
        $this->mapper->toFlashcardRecord($flashcard, $record);
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Flashcard
    {
        /** @var FlashcardRecord|null $record */
        $record = $this->entityManager->find(FlashcardRecord::class, $id);

        return $record ? $this->mapper->toFlashcardDomain($record) : null;
    }

    public function findByDeckId(string $deckId): array
    {
        /** @var array<int, FlashcardRecord> $records */
        $records = $this->entityManager->getRepository(FlashcardRecord::class)->findBy(
            ['deckId' => $deckId],
            ['id' => 'ASC']
        );

        return array_map(fn (FlashcardRecord $record): Flashcard => $this->mapper->toFlashcardDomain($record), $records);
    }
}
