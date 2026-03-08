<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\DeckRepositoryInterface;
use App\Domain\Flashcard\Deck;
use App\Infrastructure\Persistence\Doctrine\Entity\DeckRecord;
use App\Shared\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineDeckRepository implements DeckRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordMapper $mapper
    ) {
    }

    public function nextId(): string
    {
        return Id::generate('dek');
    }

    public function save(Deck $deck): void
    {
        $record = $this->entityManager->find(DeckRecord::class, $deck->id()) ?? new DeckRecord();
        $this->mapper->toDeckRecord($deck, $record);
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Deck
    {
        /** @var DeckRecord|null $record */
        $record = $this->entityManager->find(DeckRecord::class, $id);

        return $record ? $this->mapper->toDeckDomain($record) : null;
    }

    public function all(): array
    {
        /** @var array<int, DeckRecord> $records */
        $records = $this->entityManager->getRepository(DeckRecord::class)->findAll();

        return array_map(fn (DeckRecord $record): Deck => $this->mapper->toDeckDomain($record), $records);
    }
}
