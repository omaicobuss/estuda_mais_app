<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\ApiException;
use App\Application\Contracts\TokenRepositoryInterface;
use App\Application\Contracts\UserRepositoryInterface;

final class LoginUserHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private TokenRepositoryInterface $tokens
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(array $payload): array
    {
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($email === '' || $password === '') {
            throw new ApiException('Campos obrigatorios: email e password.', 422);
        }

        $user = $this->users->findByEmail($email);
        if ($user === null || !$user->verifyPassword($password)) {
            throw new ApiException('Credenciais invalidas.', 401);
        }

        $session = $this->tokens->issueTokenPair($user->id());

        return [
            'access_token' => $session['access_token'],
            'refresh_token' => $session['refresh_token'],
            'token_type' => 'Bearer',
            'expires_at' => $session['expires_at'],
            'refresh_expires_at' => $session['refresh_expires_at'],
            'user' => [
                'id' => $user->id(),
                'name' => $user->name(),
                'email' => $user->email(),
                'xp' => $user->xp(),
                'level' => $user->level(),
                'streak' => $user->streak(),
            ],
        ];
    }
}
