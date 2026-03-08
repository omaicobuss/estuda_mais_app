<?php

declare(strict_types=1);

namespace App\Interface\Http;

use App\Application\ApiException;
use App\Application\Auth\LoginUserHandler;
use App\Application\Auth\RegisterUserHandler;
use App\Application\Contracts\TokenRepositoryInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Application\Deck\CreateDeckHandler;
use App\Application\Deck\ListDecksHandler;
use App\Application\Flashcard\CreateFlashcardHandler;
use App\Application\Ranking\GetGlobalRankingHandler;
use App\Application\Study\AnswerStudyHandler;
use App\Application\Study\FinishStudyHandler;
use App\Application\Study\StartStudyHandler;
use App\Domain\User\User;

final class ApiKernel
{
    public function __construct(
        private RegisterUserHandler $registerUser,
        private LoginUserHandler $loginUser,
        private CreateDeckHandler $createDeck,
        private ListDecksHandler $listDecks,
        private CreateFlashcardHandler $createFlashcard,
        private StartStudyHandler $startStudy,
        private AnswerStudyHandler $answerStudy,
        private FinishStudyHandler $finishStudy,
        private GetGlobalRankingHandler $globalRanking,
        private TokenRepositoryInterface $tokens,
        private UserRepositoryInterface $users
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     * @return array{status: int, body: array<string, mixed>}
     */
    public function handle(string $method, string $path, array $payload = [], array $headers = []): array
    {
        try {
            if ($method === 'POST' && $path === '/api/v1/auth/register') {
                return $this->ok($this->registerUser->handle($payload), 201);
            }

            if ($method === 'POST' && $path === '/api/v1/auth/login') {
                return $this->ok($this->loginUser->handle($payload));
            }

            if ($method === 'GET' && $path === '/api/v1/auth/me') {
                $user = $this->requireUser($headers);
                return $this->ok([
                    'id' => $user->id(),
                    'name' => $user->name(),
                    'email' => $user->email(),
                    'role' => $user->role(),
                    'xp' => $user->xp(),
                    'level' => $user->level(),
                    'streak' => $user->streak(),
                    'last_study_date' => $user->lastStudyDate(),
                ]);
            }

            if ($method === 'POST' && $path === '/api/v1/decks') {
                $user = $this->requireUser($headers);
                return $this->ok($this->createDeck->handle($user->id(), $payload), 201);
            }

            if ($method === 'GET' && $path === '/api/v1/decks') {
                return $this->ok(['items' => $this->listDecks->handle()]);
            }

            if ($method === 'POST' && $path === '/api/v1/flashcards') {
                $this->requireUser($headers);
                return $this->ok($this->createFlashcard->handle($payload), 201);
            }

            if ($method === 'POST' && $path === '/api/v1/study/start') {
                $user = $this->requireUser($headers);
                return $this->ok($this->startStudy->handle($user->id(), $payload), 201);
            }

            if ($method === 'POST' && $path === '/api/v1/study/answer') {
                $user = $this->requireUser($headers);
                return $this->ok($this->answerStudy->handle($user->id(), $payload));
            }

            if ($method === 'POST' && $path === '/api/v1/study/finish') {
                $user = $this->requireUser($headers);
                return $this->ok($this->finishStudy->handle($user->id(), $payload));
            }

            if ($method === 'GET' && $path === '/api/v1/rankings/global') {
                return $this->ok(['items' => $this->globalRanking->handle()]);
            }

            return $this->error('Rota nao encontrada.', 404);
        } catch (ApiException $exception) {
            return $this->error($exception->getMessage(), $exception->statusCode());
        } catch (\Throwable $exception) {
            return $this->error('Erro interno.', 500, ['details' => $exception->getMessage()]);
        }
    }

    /**
     * @param array<string, string> $headers
     */
    private function requireUser(array $headers): User
    {
        $token = $this->extractBearerToken($headers);
        if ($token === null) {
            throw new ApiException('Token nao informado.', 401);
        }

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

    /**
     * @param array<string, string> $headers
     */
    private function extractBearerToken(array $headers): ?string
    {
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'authorization') {
                $prefix = 'bearer ';
                if (str_starts_with(strtolower($value), $prefix)) {
                    return trim(substr($value, strlen($prefix)));
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $body
     * @return array{status: int, body: array<string, mixed>}
     */
    private function ok(array $body, int $status = 200): array
    {
        return [
            'status' => $status,
            'body' => ['data' => $body],
        ];
    }

    /**
     * @param array<string, mixed> $extra
     * @return array{status: int, body: array<string, mixed>}
     */
    private function error(string $message, int $status, array $extra = []): array
    {
        return [
            'status' => $status,
            'body' => ['error' => $message] + $extra,
        ];
    }
}
