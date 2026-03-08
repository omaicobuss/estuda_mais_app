<?php

declare(strict_types=1);

namespace App\Domain\Flashcard;

use DateTimeImmutable;

final class Deck
{
    public function __construct(
        private string $id,
        private string $title,
        private string $description,
        private string $creatorId,
        private string $visibility,
        private float $price,
        private string $createdAt
    ) {
    }

    public static function create(
        string $id,
        string $title,
        string $description,
        string $creatorId,
        string $visibility,
        float $price,
        DateTimeImmutable $createdAt
    ): self {
        $normalizedVisibility = trim($visibility) !== '' ? strtolower(trim($visibility)) : 'public';
        $normalizedPrice = max(0.0, round($price, 2));
        if ($normalizedPrice > 0 && $normalizedVisibility === 'public') {
            $normalizedVisibility = 'paid';
        }

        return new self(
            $id,
            trim($title),
            trim($description),
            $creatorId,
            $normalizedVisibility,
            $normalizedPrice,
            $createdAt->format(DATE_ATOM)
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['title'],
            (string) ($data['description'] ?? ''),
            (string) $data['creator_id'],
            (string) ($data['visibility'] ?? 'public'),
            (float) ($data['price'] ?? 0),
            (string) $data['created_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'creator_id' => $this->creatorId,
            'visibility' => $this->visibility,
            'price' => $this->price,
            'created_at' => $this->createdAt,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function creatorId(): string
    {
        return $this->creatorId;
    }

    public function visibility(): string
    {
        return $this->visibility;
    }

    public function price(): float
    {
        return $this->price;
    }
}
