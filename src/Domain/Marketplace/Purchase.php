<?php

declare(strict_types=1);

namespace App\Domain\Marketplace;

use DateTimeImmutable;

final class Purchase
{
    public function __construct(
        private string $id,
        private string $userId,
        private string $deckId,
        private float $price,
        private string $status,
        private string $paymentGateway,
        private string $createdAt
    ) {
    }

    public static function createApproved(
        string $id,
        string $userId,
        string $deckId,
        float $price,
        string $gateway,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            $id,
            $userId,
            $deckId,
            round(max(0.0, $price), 2),
            'approved',
            trim($gateway) !== '' ? trim($gateway) : 'simulated',
            $createdAt->format(DATE_ATOM)
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['user_id'],
            (string) $data['deck_id'],
            (float) ($data['price'] ?? 0),
            (string) ($data['status'] ?? 'pending'),
            (string) ($data['payment_gateway'] ?? 'simulated'),
            (string) $data['created_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'deck_id' => $this->deckId,
            'price' => $this->price,
            'status' => $this->status,
            'payment_gateway' => $this->paymentGateway,
            'created_at' => $this->createdAt,
        ];
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function deckId(): string
    {
        return $this->deckId;
    }
}
