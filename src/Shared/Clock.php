<?php

declare(strict_types=1);

namespace App\Shared;

use DateTimeImmutable;

final class Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }

    public function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('today');
    }
}
