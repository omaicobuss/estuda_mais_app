<?php

declare(strict_types=1);

namespace App\Interface\Http;

use App\Application\ApiException;
use App\Application\Contracts\TokenRepositoryInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Domain\User\User;
use Symfony\Component\HttpFoundation\Request;

final class AuthenticatedUserResolver
{
    public function __construct(
        private TokenRepositoryInterface $tokens,
        private UserRepositoryInterface $users
    ) {
    }

    public function requireUser(Request $request): User
    {
        $token = $this->requireToken($request);

        $userId = $this->tokens->findUserIdByToken($token);
        if ($userId === null) {
            throw new ApiException('Token invalido.', 401);
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new ApiException('Usuario nao encontrado.', 401);
        }

        return $user;
    }

    public function requireToken(Request $request): string
    {
        $header = $this->resolveAuthorizationHeader($request);
        $token = $this->extractBearerToken($header);
        if ($token === null) {
            throw new ApiException('Token nao informado.', 401);
        }

        return $token;
    }

    private function resolveAuthorizationHeader(Request $request): string
    {
        $headerCandidates = [
            'Authorization',
            'X-Authorization',
        ];

        foreach ($headerCandidates as $header) {
            $direct = trim((string) $request->headers->get($header, ''));
            if ($direct !== '') {
                return $direct;
            }
        }

        $fallbacks = [
            'HTTP_AUTHORIZATION',
            'REDIRECT_HTTP_AUTHORIZATION',
            'HTTP_X_AUTHORIZATION',
            'REDIRECT_HTTP_X_AUTHORIZATION',
            'Authorization',
            'X-Authorization',
            'X_HTTP_AUTHORIZATION',
        ];

        foreach ($fallbacks as $key) {
            $value = trim((string) $request->server->get($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function extractBearerToken(string $header): ?string
    {
        if ($header === '') {
            return null;
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return null;
        }

        $token = trim((string) ($matches[1] ?? ''));
        if ($token === '') {
            return null;
        }

        return $token;
    }
}
