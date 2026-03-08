<?php

declare(strict_types=1);

namespace App\Shared;

final class Id
{
    public static function generate(string $prefix): string
    {
        return sprintf('%s_%s', $prefix, bin2hex(random_bytes(8)));
    }
}
