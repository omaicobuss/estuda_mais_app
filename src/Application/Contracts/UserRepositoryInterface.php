<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\User\User;

interface UserRepositoryInterface
{
    public function nextId(): string;

    public function save(User $user): void;

    public function findByEmail(string $email): ?User;

    public function findById(string $id): ?User;

    /**
     * @return array<int, User>
     */
    public function all(): array;
}
