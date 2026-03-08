<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\ApiException;
use App\Application\Contracts\UserRepositoryInterface;

final class UpdateAvatarHandler
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(string $userId, array $payload): array
    {
        $avatarId = isset($payload['avatar_id']) ? trim((string) $payload['avatar_id']) : '';
        if ($avatarId === '') {
            throw new ApiException('Campo obrigatorio: avatar_id.', 422);
        }
        if (strlen($avatarId) > 80) {
            throw new ApiException('avatar_id muito longo.', 422);
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new ApiException('Usuario nao encontrado.', 404);
        }

        $user->setAvatarId($avatarId);
        $this->users->save($user);

        return [
            'id' => $user->id(),
            'avatar_id' => $user->avatarId(),
        ];
    }
}
