<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\ApiException;
use App\Application\Ai\GenerateAiFlashcardsHandler;
use App\Application\Analytics\GetAnalyticsOverviewHandler;
use App\Application\Auth\LoginUserHandler;
use App\Application\Auth\LogoutUserHandler;
use App\Application\Auth\RefreshSessionHandler;
use App\Application\Auth\RegisterUserHandler;
use App\Application\Challenge\GetChallengeDetailsHandler;
use App\Application\Challenge\JoinChallengeHandler;
use App\Application\Challenge\ListChallengesHandler;
use App\Application\Deck\CreateDeckHandler;
use App\Application\Deck\ListDecksHandler;
use App\Application\Flashcard\CreateFlashcardHandler;
use App\Application\Marketplace\BuyMarketplaceDeckHandler;
use App\Application\Marketplace\ListMarketplaceDecksHandler;
use App\Application\Marketplace\ListPurchasesHandler;
use App\Application\Ranking\GetGlobalRankingHandler;
use App\Application\Study\AnswerStudyHandler;
use App\Application\Study\FinishStudyHandler;
use App\Application\Study\StartStudyHandler;
use App\Application\Tutor\AssistTutorHandler;
use App\Application\User\GetUserProfileHandler;
use App\Application\User\UpdateAvatarHandler;
use App\Interface\Http\AuthenticatedUserResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
final class MvpController extends AbstractController
{
    public function __construct(
        private RegisterUserHandler $registerUser,
        private LoginUserHandler $loginUser,
        private LogoutUserHandler $logoutUser,
        private RefreshSessionHandler $refreshSession,
        private CreateDeckHandler $createDeck,
        private ListDecksHandler $listDecks,
        private CreateFlashcardHandler $createFlashcard,
        private StartStudyHandler $startStudy,
        private AnswerStudyHandler $answerStudy,
        private FinishStudyHandler $finishStudy,
        private GetGlobalRankingHandler $globalRanking,
        private GetUserProfileHandler $getUserProfile,
        private UpdateAvatarHandler $updateAvatar,
        private ListMarketplaceDecksHandler $listMarketplaceDecks,
        private BuyMarketplaceDeckHandler $buyMarketplaceDeck,
        private ListPurchasesHandler $listPurchases,
        private ListChallengesHandler $listChallenges,
        private JoinChallengeHandler $joinChallenge,
        private GetChallengeDetailsHandler $getChallengeDetails,
        private GenerateAiFlashcardsHandler $generateAiFlashcards,
        private AssistTutorHandler $assistTutor,
        private GetAnalyticsOverviewHandler $analyticsOverview,
        private AuthenticatedUserResolver $authenticatedUser
    ) {
    }

    #[Route('/auth/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        return $this->json(['data' => $this->registerUser->handle($this->payload($request))], 201);
    }

    #[Route('/auth/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        return $this->json(['data' => $this->loginUser->handle($this->payload($request))]);
    }

    #[Route('/auth/me', methods: ['GET'])]
    public function me(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => [
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => $user->email(),
            'role' => $user->role(),
            'avatar_id' => $user->avatarId(),
            'xp' => $user->xp(),
            'level' => $user->level(),
            'streak' => $user->streak(),
            'last_study_date' => $user->lastStudyDate(),
        ]]);
    }

    #[Route('/auth/logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $token = $this->authenticatedUser->requireToken($request);
        $this->logoutUser->handle($token);

        return $this->json(['data' => ['message' => 'Logout realizado com sucesso.']]);
    }

    #[Route('/auth/refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        return $this->json(['data' => $this->refreshSession->handle($this->payload($request))]);
    }

    #[Route('/decks', methods: ['POST'])]
    public function createDeck(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->createDeck->handle($user->id(), $this->payload($request))], 201);
    }

    #[Route('/decks', methods: ['GET'])]
    public function listDecks(): JsonResponse
    {
        return $this->json(['data' => ['items' => $this->listDecks->handle()]]);
    }

    #[Route('/flashcards', methods: ['POST'])]
    public function createFlashcard(Request $request): JsonResponse
    {
        $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->createFlashcard->handle($this->payload($request))], 201);
    }

    #[Route('/study/start', methods: ['POST'])]
    public function startStudy(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->startStudy->handle($user->id(), $this->payload($request))], 201);
    }

    #[Route('/study/answer', methods: ['POST'])]
    public function answerStudy(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->answerStudy->handle($user->id(), $this->payload($request))]);
    }

    #[Route('/study/finish', methods: ['POST'])]
    public function finishStudy(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->finishStudy->handle($user->id(), $this->payload($request))]);
    }

    #[Route('/rankings/global', methods: ['GET'])]
    public function globalRanking(): JsonResponse
    {
        return $this->json(['data' => ['items' => $this->globalRanking->handle()]]);
    }

    #[Route('/users/profile', methods: ['GET'])]
    public function userProfile(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->getUserProfile->handle($user->id())]);
    }

    #[Route('/users/avatar', methods: ['PUT'])]
    public function updateAvatar(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->updateAvatar->handle($user->id(), $this->payload($request))]);
    }

    #[Route('/marketplace/decks', methods: ['GET'])]
    public function listMarketplaceDecks(): JsonResponse
    {
        return $this->json(['data' => ['items' => $this->listMarketplaceDecks->handle()]]);
    }

    #[Route('/marketplace/buy', methods: ['POST'])]
    public function buyMarketplaceDeck(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->buyMarketplaceDeck->handle($user->id(), $this->payload($request))], 201);
    }

    #[Route('/marketplace/purchases', methods: ['GET'])]
    public function listPurchases(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => ['items' => $this->listPurchases->handle($user->id())]]);
    }

    #[Route('/challenges', methods: ['GET'])]
    public function listChallenges(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => ['items' => $this->listChallenges->handle($user->id())]]);
    }

    #[Route('/challenges/join', methods: ['POST'])]
    public function joinChallenge(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->joinChallenge->handle($user->id(), $this->payload($request))], 201);
    }

    #[Route('/challenges/{id}', methods: ['GET'])]
    public function challengeDetails(Request $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->getChallengeDetails->handle($user->id(), $id)]);
    }

    #[Route('/ai/cards/generate', methods: ['POST'])]
    public function generateAiCards(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->generateAiFlashcards->handle($user->id(), $this->payload($request))], 201);
    }

    #[Route('/tutor/assist', methods: ['POST'])]
    public function tutorAssist(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->assistTutor->handle($user->id(), $this->payload($request))]);
    }

    #[Route('/analytics/overview', methods: ['GET'])]
    public function analyticsOverview(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser->requireUser($request);

        return $this->json(['data' => $this->analyticsOverview->handle($user->id())]);
    }

    #[Route('/health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json([
            'data' => [
                'status' => 'ok',
                'service' => 'estuda-plus-api',
                'version' => 'fase3',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $content = trim((string) $request->getContent());
        if ($content === '') {
            return [];
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            throw new ApiException('JSON invalido.', 400);
        }

        return $decoded;
    }
}
