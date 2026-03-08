<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\PurchaseRepositoryInterface;
use App\Domain\Marketplace\Purchase;
use App\Infrastructure\Persistence\Doctrine\Entity\PurchaseRecord;
use App\Shared\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePurchaseRepository implements PurchaseRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function nextId(): string
    {
        return Id::generate('pur');
    }

    public function save(Purchase $purchase): void
    {
        $data = $purchase->toArray();
        $record = $this->entityManager->find(PurchaseRecord::class, (string) $data['id']) ?? new PurchaseRecord();

        $record->setId((string) $data['id']);
        $record->setUserId((string) $data['user_id']);
        $record->setDeckId((string) $data['deck_id']);
        $record->setPrice((float) $data['price']);
        $record->setStatus((string) $data['status']);
        $record->setPaymentGateway((string) $data['payment_gateway']);
        $record->setCreatedAt(new \DateTimeImmutable((string) $data['created_at']));

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findByUserAndDeck(string $userId, string $deckId): ?Purchase
    {
        /** @var PurchaseRecord|null $record */
        $record = $this->entityManager->getRepository(PurchaseRecord::class)->findOneBy([
            'userId' => $userId,
            'deckId' => $deckId,
        ]);
        if ($record === null) {
            return null;
        }

        return Purchase::fromArray([
            'id' => $record->getId(),
            'user_id' => $record->getUserId(),
            'deck_id' => $record->getDeckId(),
            'price' => $record->getPrice(),
            'status' => $record->getStatus(),
            'payment_gateway' => $record->getPaymentGateway(),
            'created_at' => $record->getCreatedAt()->format(DATE_ATOM),
        ]);
    }

    public function findByUser(string $userId): array
    {
        /** @var array<int, PurchaseRecord> $records */
        $records = $this->entityManager->getRepository(PurchaseRecord::class)->findBy(
            ['userId' => $userId],
            ['createdAt' => 'DESC']
        );

        $items = [];
        foreach ($records as $record) {
            $items[] = Purchase::fromArray([
                'id' => $record->getId(),
                'user_id' => $record->getUserId(),
                'deck_id' => $record->getDeckId(),
                'price' => $record->getPrice(),
                'status' => $record->getStatus(),
                'payment_gateway' => $record->getPaymentGateway(),
                'created_at' => $record->getCreatedAt()->format(DATE_ATOM),
            ]);
        }

        return $items;
    }
}
