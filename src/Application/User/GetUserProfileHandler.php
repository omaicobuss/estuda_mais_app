<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\ApiException;
use App\Application\Contracts\UserRepositoryInterface;

final class GetUserProfileHandler
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(string $userId): array
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new ApiException('Usuario nao encontrado.', 404);
        }

        return [
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => $user->email(),
            'role' => $user->role(),
            'avatar_id' => $user->avatarId(),
            'xp' => $user->xp(),
            'level' => $user->level(),
            'streak' => $user->streak(),
            'last_study_date' => $user->lastStudyDate(),
        ];
    }
}
