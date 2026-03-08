<?php

declare(strict_types=1);

namespace App\Application\Study;

use App\Application\ApiException;
use App\Application\Contracts\StudySessionRepositoryInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Application\Contracts\XpHistoryRepositoryInterface;
use App\Domain\Gamification\XpPolicy;
use App\Shared\Clock;

final class FinishStudyHandler
{
    public function __construct(
        private StudySessionRepositoryInterface $sessions,
        private UserRepositoryInterface $users,
        private XpHistoryRepositoryInterface $xpHistory,
        private XpPolicy $xpPolicy,
        private Clock $clock
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(string $userId, array $payload): array
    {
        $sessionId = (string) ($payload['session_id'] ?? '');
        if ($sessionId === '') {
            throw new ApiException('Campo obrigatorio: session_id.', 422);
        }

        $session = $this->sessions->findById($sessionId);
        if ($session === null || $session->userId() !== $userId) {
            throw new ApiException('Sessao de estudo nao encontrada.', 404);
        }

        if ($session->isFinished()) {
            throw new ApiException('Sessao de estudo ja finalizada.', 409);
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new ApiException('Usuario nao encontrado.', 404);
        }

        $session->finish($this->clock->now());
        $this->sessions->save($session);

        $firstStudyOfTheDay = $user->markStudyOn($this->clock->today());
        $rewards = $this->xpPolicy->rewardsForSession($session, $firstStudyOfTheDay);

        $totalXp = 0;
        foreach ($rewards as $reward) {
            $totalXp += $reward['xp'];
            $user->addXp($reward['xp']);
            $this->xpHistory->add(
                $user->id(),
                $reward['xp'],
                $reward['reason'],
                $this->clock->now()->format(DATE_ATOM)
            );
        }

        $this->users->save($user);

        return [
            'session_id' => $session->id(),
            'completed' => $session->isCompleted(),
            'accuracy' => $session->accuracy(),
            'xp_gained' => $totalXp,
            'rewards' => $rewards,
            'user' => [
                'id' => $user->id(),
                'xp' => $user->xp(),
                'level' => $user->level(),
                'streak' => $user->streak(),
                'last_study_date' => $user->lastStudyDate(),
            ],
        ];
    }
}
