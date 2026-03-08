<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Contracts\UserRepositoryInterface;
use App\Domain\User\User;
use App\Shared\Id;

final class JsonUserRepository implements UserRepositoryInterface
{
    private const TABLE = 'users';

    public function __construct(private JsonFileStore $store)
    {
    }

    public function nextId(): string
    {
        return Id::generate('usr');
    }

    public function save(User $user): void
    {
        $rows = $this->store->all(self::TABLE);
        $updated = false;

        foreach ($rows as $index => $row) {
            if (($row['id'] ?? null) === $user->id()) {
                $rows[$index] = $user->toArray();
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $rows[] = $user->toArray();
        }

        $this->store->replaceAll(self::TABLE, $rows);
    }

    public function findByEmail(string $email): ?User
    {
        $target = strtolower(trim($email));
        foreach ($this->store->all(self::TABLE) as $row) {
            if (($row['email'] ?? null) === $target) {
                return User::fromArray($row);
            }
        }

        return null;
    }

    public function findById(string $id): ?User
    {
        foreach ($this->store->all(self::TABLE) as $row) {
            if (($row['id'] ?? null) === $id) {
                return User::fromArray($row);
            }
        }

        return null;
    }

    public function all(): array
    {
        return array_map(
            static fn (array $row): User => User::fromArray($row),
            $this->store->all(self::TABLE)
        );
    }
}
