<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface AiCardGeneratorInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function generate(string $topic, int $count, ?string $sourceText = null): array;
}

