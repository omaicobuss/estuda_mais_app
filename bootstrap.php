<?php

declare(strict_types=1);

use App\Application\Auth\LoginUserHandler;
use App\Application\Auth\RegisterUserHandler;
use App\Application\Deck\CreateDeckHandler;
use App\Application\Deck\ListDecksHandler;
use App\Application\Flashcard\CreateFlashcardHandler;
use App\Application\Ranking\GetGlobalRankingHandler;
use App\Application\Study\AnswerStudyHandler;
use App\Application\Study\FinishStudyHandler;
use App\Application\Study\StartStudyHandler;
use App\Domain\Gamification\XpPolicy;
use App\Infrastructure\Persistence\JsonCardReviewRepository;
use App\Infrastructure\Persistence\JsonDeckRepository;
use App\Infrastructure\Persistence\JsonFileStore;
use App\Infrastructure\Persistence\JsonFlashcardRepository;
use App\Infrastructure\Persistence\JsonStudySessionRepository;
use App\Infrastructure\Persistence\JsonUserRepository;
use App\Infrastructure\Persistence\JsonXpHistoryRepository;
use App\Infrastructure\Security\JsonTokenRepository;
use App\Interface\Http\ApiKernel;
use App\Shared\Clock;

spl_autoload_register(
    static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $path = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
);

function build_api_kernel(): ApiKernel
{
    $clock = new Clock();
    $store = new JsonFileStore(__DIR__ . '/storage');

    $users = new JsonUserRepository($store);
    $tokens = new JsonTokenRepository($store);
    $decks = new JsonDeckRepository($store);
    $flashcards = new JsonFlashcardRepository($store);
    $sessions = new JsonStudySessionRepository($store);
    $reviews = new JsonCardReviewRepository($store);
    $xpHistory = new JsonXpHistoryRepository($store);
    $xpPolicy = new XpPolicy();

    return new ApiKernel(
        new RegisterUserHandler($users, $clock),
        new LoginUserHandler($users, $tokens),
        new CreateDeckHandler($decks, $clock),
        new ListDecksHandler($decks),
        new CreateFlashcardHandler($decks, $flashcards),
        new StartStudyHandler($decks, $flashcards, $reviews, $sessions, $clock),
        new AnswerStudyHandler($sessions, $flashcards, $reviews, $clock),
        new FinishStudyHandler($sessions, $users, $xpHistory, $xpPolicy, $clock),
        new GetGlobalRankingHandler($users),
        $tokens,
        $users
    );
}
