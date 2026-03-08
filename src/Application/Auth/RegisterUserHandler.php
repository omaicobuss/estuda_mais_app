<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\ApiException;
use App\Application\Contracts\UserRepositoryInterface;
use App\Domain\User\User;
use App\Shared\Clock;

final class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private Clock $clock
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
        $name = trim((string) ($payload['name'] ?? ''));

        if ($email === '' || $password === '' || $name === '') {
            throw new ApiException('Campos obrigatorios: name, email e password.', 422);
        }

        if ($this->users->findByEmail($email) !== null) {
            throw new ApiException('Email ja cadastrado.', 409);
        }

        $user = User::create(
            $this->users->nextId(),
            $email,
            $password,
            $name,
            $this->clock->now()
        );

        $this->users->save($user);

        return [
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => $user->email(),
            'role' => $user->role(),
            'xp' => $user->xp(),
            'level' => $user->level(),
            'streak' => $user->streak(),
        ];
    }
}
