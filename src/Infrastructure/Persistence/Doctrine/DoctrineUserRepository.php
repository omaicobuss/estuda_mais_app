<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Contracts\UserRepositoryInterface;
use App\Domain\User\User;
use App\Infrastructure\Persistence\Doctrine\Entity\UserRecord;
use App\Shared\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordMapper $mapper
    ) {
    }

    public function nextId(): string
    {
        return Id::generate('usr');
    }

    public function save(User $user): void
    {
        $record = $this->entityManager->find(UserRecord::class, $user->id()) ?? new UserRecord();
        $this->mapper->toUserRecord($user, $record);
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findByEmail(string $email): ?User
    {
        /** @var UserRecord|null $record */
        $record = $this->entityManager->getRepository(UserRecord::class)->findOneBy([
            'email' => strtolower(trim($email)),
        ]);

        return $record ? $this->mapper->toUserDomain($record) : null;
    }

    public function findById(string $id): ?User
    {
        /** @var UserRecord|null $record */
        $record = $this->entityManager->find(UserRecord::class, $id);

        return $record ? $this->mapper->toUserDomain($record) : null;
    }

    public function all(): array
    {
        /** @var array<int, UserRecord> $records */
        $records = $this->entityManager->getRepository(UserRecord::class)->findAll();

        return array_map(fn (UserRecord $record): User => $this->mapper->toUserDomain($record), $records);
    }
}
