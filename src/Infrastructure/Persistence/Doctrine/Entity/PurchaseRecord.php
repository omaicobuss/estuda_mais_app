<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'purchases')]
#[ORM\UniqueConstraint(name: 'uk_purchases_user_deck', columns: ['user_id', 'deck_id'])]
#[ORM\Index(name: 'idx_purchases_user_created', columns: ['user_id', 'created_at'])]
final class PurchaseRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 40)]
    private string $id;

    #[ORM\Column(name: 'user_id', type: 'string', length: 40)]
    private string $userId;

    #[ORM\Column(name: 'deck_id', type: 'string', length: 40)]
    private string $deckId;

    #[ORM\Column(type: 'float', options: ['default' => '0'])]
    private float $price = 0.0;

    #[ORM\Column(type: 'string', length: 30)]
    private string $status = 'approved';

    #[ORM\Column(name: 'payment_gateway', type: 'string', length: 40)]
    private string $paymentGateway = 'simulated';

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getDeckId(): string
    {
        return $this->deckId;
    }

    public function setDeckId(string $deckId): void
    {
        $this->deckId = $deckId;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = round(max(0.0, $price), 2);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPaymentGateway(): string
    {
        return $this->paymentGateway;
    }

    public function setPaymentGateway(string $paymentGateway): void
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
