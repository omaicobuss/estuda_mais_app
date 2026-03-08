<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Challenge\Challenge;

interface ChallengeRepositoryInterface
{
    public function nextId(): string;

    public function save(Challenge $challenge): void;

    public function findById(string $id): ?Challenge;

    /**
     * @return array<int, Challenge>
     */
    public function all(): array;
}
