<?php

declare(strict_types=1);

namespace App\Application;

final class ApiException extends \RuntimeException
{
    public function __construct(string $message, private int $statusCode = 400)
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
