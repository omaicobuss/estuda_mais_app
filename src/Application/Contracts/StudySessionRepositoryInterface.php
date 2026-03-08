<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Study\StudySession;

interface StudySessionRepositoryInterface
{
    public function nextId(): string;

    public function save(StudySession $session): void;

    public function findById(string $id): ?StudySession;
}
