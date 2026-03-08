<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

final class JsonFileStore
{
    public function __construct(private string $storagePath)
    {
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0777, true);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(string $table): array
    {
        $path = $this->pathFor($table);
        if (!file_exists($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values($decoded);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function replaceAll(string $table, array $rows): void
    {
        $path = $this->pathFor($table);
        $encoded = json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            throw new \RuntimeException(sprintf('Could not encode table "%s".', $table));
        }

        file_put_contents($path, $encoded . PHP_EOL, LOCK_EX);
    }

    private function pathFor(string $table): string
    {
        return rtrim($this->storagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $table . '.json';
    }
}
