<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Contracts\StudySessionRepositoryInterface;
use App\Domain\Study\StudySession;
use App\Shared\Id;

final class JsonStudySessionRepository implements StudySessionRepositoryInterface
{
    private const TABLE = 'study_sessions';

    public function __construct(private JsonFileStore $store)
    {
    }

    public function nextId(): string
    {
        return Id::generate('ses');
    }

    public function save(StudySession $session): void
    {
        $rows = $this->store->all(self::TABLE);
        $updated = false;

        foreach ($rows as $index => $row) {
            if (($row['id'] ?? null) === $session->id()) {
                $rows[$index] = $session->toArray();
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $rows[] = $session->toArray();
        }

        $this->store->replaceAll(self::TABLE, $rows);
    }

    public function findById(string $id): ?StudySession
    {
        foreach ($this->store->all(self::TABLE) as $row) {
            if (($row['id'] ?? null) === $id) {
                return StudySession::fromArray($row);
            }
        }

        return null;
    }
}
