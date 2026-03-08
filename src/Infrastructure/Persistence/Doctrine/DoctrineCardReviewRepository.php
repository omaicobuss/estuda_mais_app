<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\CardReviewRepositoryInterface;
use App\Domain\Study\CardReview;
use App\Infrastructure\Persistence\Doctrine\Entity\CardReviewRecord;
use App\Shared\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCardReviewRepository implements CardReviewRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordMapper $mapper
    ) {
    }

    public function nextId(): string
    {
        return Id::generate('rev');
    }

    public function save(CardReview $review): void
    {
        $record = $this->entityManager->find(CardReviewRecord::class, $review->id()) ?? new CardReviewRecord();
        $this->mapper->toCardReviewRecord($review, $record);
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findByUserAndFlashcard(string $userId, string $flashcardId): ?CardReview
    {
        /** @var CardReviewRecord|null $record */
        $record = $this->entityManager->getRepository(CardReviewRecord::class)->findOneBy([
            'userId' => $userId,
            'flashcardId' => $flashcardId,
        ]);

        return $record ? $this->mapper->toCardReviewDomain($record) : null;
    }
}
