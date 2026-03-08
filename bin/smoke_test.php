<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

if (!isset($_SERVER['APP_ENV'])) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

$kernel = new Kernel((string) $_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

/**
 * @param array<string, mixed> $payload
 * @param array<string, string> $headers
 * @return array{status: int, body: array<string, mixed>}
 */
function api_call(Kernel $kernel, string $method, string $path, array $payload = [], array $headers = []): array
{
    $server = ['CONTENT_TYPE' => 'application/json'];
    foreach ($headers as $name => $value) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $server[$key] = $value;
    }

    $content = $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_UNICODE);
    $request = Request::create($path, strtoupper($method), [], [], [], $server, $content ?: null);
    $response = $kernel->handle($request);

    $decoded = json_decode((string) $response->getContent(), true);
    $body = is_array($decoded) ? $decoded : [];

    return [
        'status' => $response->getStatusCode(),
        'body' => $body,
    ];
}

/**
 * @param array{status: int, body: array<string, mixed>} $response
 */
function expect_status(array $response, int $status, string $context): void
{
    if ($response['status'] !== $status) {
        throw new RuntimeException(sprintf(
            '[%s] status esperado %d, recebido %d: %s',
            $context,
            $status,
            $response['status'],
            json_encode($response['body'], JSON_UNESCAPED_UNICODE)
        ));
    }
}

$registerAlice = api_call($kernel, 'POST', '/api/v1/auth/register', [
    'name' => 'Alice',
    'email' => 'alice@estuda.local',
    'password' => '123456',
]);
expect_status($registerAlice, 201, 'register_alice');

$loginAlice = api_call($kernel, 'POST', '/api/v1/auth/login', [
    'email' => 'alice@estuda.local',
    'password' => '123456',
]);
expect_status($loginAlice, 200, 'login_alice');
$aliceToken = (string) ($loginAlice['body']['data']['access_token'] ?? '');
$aliceRefresh = (string) ($loginAlice['body']['data']['refresh_token'] ?? '');
$aliceAuth = ['Authorization' => 'Bearer ' . $aliceToken];

$refreshAlice = api_call($kernel, 'POST', '/api/v1/auth/refresh', [
    'refresh_token' => $aliceRefresh,
]);
expect_status($refreshAlice, 200, 'refresh_alice');
$aliceTokenRefreshed = (string) ($refreshAlice['body']['data']['access_token'] ?? '');
$aliceRefreshRefreshed = (string) ($refreshAlice['body']['data']['refresh_token'] ?? '');

$aliceOldTokenAfterRefresh = api_call($kernel, 'GET', '/api/v1/auth/me', [], $aliceAuth);
expect_status($aliceOldTokenAfterRefresh, 401, 'alice_old_token_after_refresh');
$aliceAuth = ['Authorization' => 'Bearer ' . $aliceTokenRefreshed];

$publicDeck = api_call($kernel, 'POST', '/api/v1/decks', [
    'title' => 'Matematica Basica',
    'description' => 'Operacoes fundamentais',
], $aliceAuth);
expect_status($publicDeck, 201, 'create_public_deck');
$publicDeckId = (string) ($publicDeck['body']['data']['id'] ?? '');

$publicCardA = api_call($kernel, 'POST', '/api/v1/flashcards', [
    'deck_id' => $publicDeckId,
    'type' => 'QA',
    'question' => '2 + 2',
    'answer' => '4',
], $aliceAuth);
expect_status($publicCardA, 201, 'create_public_card_1');

$publicCardB = api_call($kernel, 'POST', '/api/v1/flashcards', [
    'deck_id' => $publicDeckId,
    'type' => 'QA',
    'question' => '3 + 5',
    'answer' => '8',
], $aliceAuth);
expect_status($publicCardB, 201, 'create_public_card_2');

$publicStudy = api_call($kernel, 'POST', '/api/v1/study/start', [
    'deck_id' => $publicDeckId,
], $aliceAuth);
expect_status($publicStudy, 201, 'public_study_start');
$publicSessionId = (string) ($publicStudy['body']['data']['session_id'] ?? '');
$publicCards = $publicStudy['body']['data']['cards'] ?? [];
if (!is_array($publicCards) || count($publicCards) < 2) {
    throw new RuntimeException('Sessao publica nao retornou cards suficientes.');
}
$answerMap = [
    '2 + 2' => '4',
    '3 + 5' => '8',
];

$answerPublicA = api_call($kernel, 'POST', '/api/v1/study/answer', [
    'session_id' => $publicSessionId,
    'flashcard_id' => (string) $publicCards[0]['id'],
    'user_answer' => (string) ($answerMap[$publicCards[0]['question']] ?? '4'),
], $aliceAuth);
expect_status($answerPublicA, 200, 'public_study_answer_1');

$answerPublicB = api_call($kernel, 'POST', '/api/v1/study/answer', [
    'session_id' => $publicSessionId,
    'flashcard_id' => (string) $publicCards[1]['id'],
    'user_answer' => (string) ($answerMap[$publicCards[1]['question']] ?? '8'),
], $aliceAuth);
expect_status($answerPublicB, 200, 'public_study_answer_2');

$finishPublicStudy = api_call($kernel, 'POST', '/api/v1/study/finish', [
    'session_id' => $publicSessionId,
], $aliceAuth);
expect_status($finishPublicStudy, 200, 'public_study_finish');

$aiDeck = api_call($kernel, 'POST', '/api/v1/decks', [
    'title' => 'Deck IA Fracoes',
    'description' => 'Deck gerado por IA para validacao da fase 3',
], $aliceAuth);
expect_status($aiDeck, 201, 'create_ai_deck');
$aiDeckId = (string) ($aiDeck['body']['data']['id'] ?? '');

$aiGenerate = api_call($kernel, 'POST', '/api/v1/ai/cards/generate', [
    'deck_id' => $aiDeckId,
    'topic' => 'Fracoes',
    'count' => 3,
    'source_text' => 'Fracoes representam partes de um inteiro. Comparar fracoes exige denominador comum. Simplificar fracoes reduz termos.',
    'persist' => true,
], $aliceAuth);
expect_status($aiGenerate, 201, 'ai_generate_cards');
$aiGeneratedCards = $aiGenerate['body']['data']['cards'] ?? [];
if (!is_array($aiGeneratedCards) || count($aiGeneratedCards) !== 3) {
    throw new RuntimeException('IA nao gerou a quantidade esperada de cards.');
}

$aiAnswerMap = [];
foreach ($aiGeneratedCards as $generatedCard) {
    if (!is_array($generatedCard)) {
        continue;
    }
    $cardId = (string) ($generatedCard['id'] ?? '');
    if ($cardId !== '') {
        $aiAnswerMap[$cardId] = (string) ($generatedCard['answer'] ?? '');
    }
}

$aiStudy = api_call($kernel, 'POST', '/api/v1/study/start', [
    'deck_id' => $aiDeckId,
], $aliceAuth);
expect_status($aiStudy, 201, 'ai_study_start');
$aiSessionId = (string) ($aiStudy['body']['data']['session_id'] ?? '');
$aiCards = $aiStudy['body']['data']['cards'] ?? [];
if (!is_array($aiCards) || count($aiCards) < 3) {
    throw new RuntimeException('Sessao do deck IA sem cards suficientes.');
}

foreach ($aiCards as $index => $card) {
    if (!is_array($card)) {
        continue;
    }
    $cardId = (string) ($card['id'] ?? '');
    $answer = (string) ($aiAnswerMap[$cardId] ?? '');
    if ($answer === '') {
        $answer = $index === 1 ? 'Verdadeiro' : 'Conceito principal';
    }

    $aiAnswer = api_call($kernel, 'POST', '/api/v1/study/answer', [
        'session_id' => $aiSessionId,
        'flashcard_id' => $cardId,
        'user_answer' => $answer,
    ], $aliceAuth);
    expect_status($aiAnswer, 200, 'ai_study_answer_' . ($index + 1));
}

$aiFinish = api_call($kernel, 'POST', '/api/v1/study/finish', [
    'session_id' => $aiSessionId,
], $aliceAuth);
expect_status($aiFinish, 200, 'ai_study_finish');

$paidDeck = api_call($kernel, 'POST', '/api/v1/decks', [
    'title' => 'Deck Premium Geografia',
    'description' => 'Deck pago de geografia',
    'visibility' => 'paid',
    'price' => 29.9,
], $aliceAuth);
expect_status($paidDeck, 201, 'create_paid_deck');
$paidDeckId = (string) ($paidDeck['body']['data']['id'] ?? '');

$paidCard = api_call($kernel, 'POST', '/api/v1/flashcards', [
    'deck_id' => $paidDeckId,
    'type' => 'QA',
    'question' => 'Capital do Brasil',
    'answer' => 'Brasilia',
], $aliceAuth);
expect_status($paidCard, 201, 'create_paid_card');

$registerBob = api_call($kernel, 'POST', '/api/v1/auth/register', [
    'name' => 'Bob',
    'email' => 'bob@estuda.local',
    'password' => '123456',
]);
expect_status($registerBob, 201, 'register_bob');

$loginBob = api_call($kernel, 'POST', '/api/v1/auth/login', [
    'email' => 'bob@estuda.local',
    'password' => '123456',
]);
expect_status($loginBob, 200, 'login_bob');
$bobToken = (string) ($loginBob['body']['data']['access_token'] ?? '');
$bobAuth = ['Authorization' => 'Bearer ' . $bobToken];

$bobProfileBefore = api_call($kernel, 'GET', '/api/v1/users/profile', [], $bobAuth);
expect_status($bobProfileBefore, 200, 'bob_profile_before');

$bobAvatar = api_call($kernel, 'PUT', '/api/v1/users/avatar', [
    'avatar_id' => 'robot_blue',
], $bobAuth);
expect_status($bobAvatar, 200, 'bob_avatar_update');

$bobProfileAfter = api_call($kernel, 'GET', '/api/v1/users/profile', [], $bobAuth);
expect_status($bobProfileAfter, 200, 'bob_profile_after');

$marketplaceDecks = api_call($kernel, 'GET', '/api/v1/marketplace/decks');
expect_status($marketplaceDecks, 200, 'marketplace_decks');
$marketplaceItems = $marketplaceDecks['body']['data']['items'] ?? [];
if (!is_array($marketplaceItems)) {
    throw new RuntimeException('Marketplace retornou formato invalido.');
}
$paidDeckAvailable = false;
foreach ($marketplaceItems as $item) {
    if (is_array($item) && ($item['id'] ?? null) === $paidDeckId) {
        $paidDeckAvailable = true;
        break;
    }
}
if (!$paidDeckAvailable) {
    throw new RuntimeException('Deck pago nao encontrado no marketplace.');
}

$bobStudyBeforeBuy = api_call($kernel, 'POST', '/api/v1/study/start', [
    'deck_id' => $paidDeckId,
], $bobAuth);
expect_status($bobStudyBeforeBuy, 403, 'bob_study_paid_before_buy');

$buyPaidDeck = api_call($kernel, 'POST', '/api/v1/marketplace/buy', [
    'deck_id' => $paidDeckId,
], $bobAuth);
expect_status($buyPaidDeck, 201, 'marketplace_buy');

$buyPaidDeckAgain = api_call($kernel, 'POST', '/api/v1/marketplace/buy', [
    'deck_id' => $paidDeckId,
], $bobAuth);
expect_status($buyPaidDeckAgain, 409, 'marketplace_buy_again');

$bobPurchases = api_call($kernel, 'GET', '/api/v1/marketplace/purchases', [], $bobAuth);
expect_status($bobPurchases, 200, 'marketplace_purchases');
$purchaseItems = $bobPurchases['body']['data']['items'] ?? [];
if (!is_array($purchaseItems) || count($purchaseItems) < 1) {
    throw new RuntimeException('Compra nao registrada para o usuario Bob.');
}

$bobStudyAfterBuy = api_call($kernel, 'POST', '/api/v1/study/start', [
    'deck_id' => $paidDeckId,
], $bobAuth);
expect_status($bobStudyAfterBuy, 201, 'bob_study_paid_after_buy');
$bobSessionId = (string) ($bobStudyAfterBuy['body']['data']['session_id'] ?? '');
$bobCards = $bobStudyAfterBuy['body']['data']['cards'] ?? [];
if (!is_array($bobCards) || count($bobCards) < 1) {
    throw new RuntimeException('Sessao de estudo do deck pago sem cards.');
}

$bobAnswer = api_call($kernel, 'POST', '/api/v1/study/answer', [
    'session_id' => $bobSessionId,
    'flashcard_id' => (string) $bobCards[0]['id'],
    'user_answer' => 'Brasilia',
], $bobAuth);
expect_status($bobAnswer, 200, 'bob_answer_paid_deck');

$bobFinish = api_call($kernel, 'POST', '/api/v1/study/finish', [
    'session_id' => $bobSessionId,
], $bobAuth);
expect_status($bobFinish, 200, 'bob_finish_paid_deck');

$challenges = api_call($kernel, 'GET', '/api/v1/challenges', [], $bobAuth);
expect_status($challenges, 200, 'challenges_list');
$challengeItems = $challenges['body']['data']['items'] ?? [];
if (!is_array($challengeItems) || count($challengeItems) < 1) {
    throw new RuntimeException('Nenhum desafio disponivel para validacao.');
}

$challengeId = null;
foreach ($challengeItems as $challenge) {
    if (!is_array($challenge)) {
        continue;
    }
    if (($challenge['status'] ?? null) === 'active') {
        $challengeId = (string) ($challenge['id'] ?? '');
        break;
    }
}
if ($challengeId === null || $challengeId === '') {
    $challengeId = (string) ($challengeItems[0]['id'] ?? '');
}
if ($challengeId === '') {
    throw new RuntimeException('Desafio sem id valido.');
}

$joinChallenge = api_call($kernel, 'POST', '/api/v1/challenges/join', [
    'challenge_id' => $challengeId,
], $bobAuth);
expect_status($joinChallenge, 201, 'challenge_join');

$joinChallengeAgain = api_call($kernel, 'POST', '/api/v1/challenges/join', [
    'challenge_id' => $challengeId,
], $bobAuth);
expect_status($joinChallengeAgain, 409, 'challenge_join_again');

$challengeDetails = api_call($kernel, 'GET', '/api/v1/challenges/' . $challengeId, [], $bobAuth);
expect_status($challengeDetails, 200, 'challenge_details');
if (($challengeDetails['body']['data']['joined'] ?? false) !== true) {
    throw new RuntimeException('Detalhe do desafio nao marcou o usuario como inscrito.');
}

$tutorAssist = api_call($kernel, 'POST', '/api/v1/tutor/assist', [
    'deck_id' => $paidDeckId,
    'question' => 'Como memorizar a capital do Brasil com mais rapidez?',
], $bobAuth);
expect_status($tutorAssist, 200, 'tutor_assist');
$tutorActions = $tutorAssist['body']['data']['next_actions'] ?? [];
if (!is_array($tutorActions) || count($tutorActions) < 1) {
    throw new RuntimeException('Tutor nao retornou plano de acao.');
}

$aliceAnalytics = api_call($kernel, 'GET', '/api/v1/analytics/overview', [], $aliceAuth);
expect_status($aliceAnalytics, 200, 'analytics_overview_alice');
$aliceOverview = $aliceAnalytics['body']['data']['overview'] ?? [];
if (!is_array($aliceOverview) || (int) ($aliceOverview['sessions_finished'] ?? 0) < 2) {
    throw new RuntimeException('Analytics da Alice sem sessoes suficientes apos estudos.');
}

$bobAnalytics = api_call($kernel, 'GET', '/api/v1/analytics/overview', [], $bobAuth);
expect_status($bobAnalytics, 200, 'analytics_overview_bob');

$ranking = api_call($kernel, 'GET', '/api/v1/rankings/global');
expect_status($ranking, 200, 'ranking_global');

$logoutAlice = api_call($kernel, 'POST', '/api/v1/auth/logout', [], $aliceAuth);
expect_status($logoutAlice, 200, 'logout_alice');

$meAfterLogout = api_call($kernel, 'GET', '/api/v1/auth/me', [], $aliceAuth);
expect_status($meAfterLogout, 401, 'alice_me_after_logout');

$refreshAfterLogout = api_call($kernel, 'POST', '/api/v1/auth/refresh', [
    'refresh_token' => $aliceRefreshRefreshed,
]);
expect_status($refreshAfterLogout, 401, 'alice_refresh_after_logout');

echo "Smoke test Fase 3 OK.\n";
echo json_encode([
    'phase1_xp_gained' => $finishPublicStudy['body']['data']['xp_gained'] ?? null,
    'phase3_ai_generated' => $aiGenerate['body']['data']['generated_count'] ?? null,
    'phase3_ai_xp_gained' => $aiFinish['body']['data']['xp_gained'] ?? null,
    'bob_avatar' => $bobProfileAfter['body']['data']['avatar_id'] ?? null,
    'marketplace_purchase_id' => $buyPaidDeck['body']['data']['id'] ?? null,
    'challenge_id' => $challengeId,
    'challenge_participants' => $challengeDetails['body']['data']['participants_count'] ?? null,
    'tutor_stage' => $tutorAssist['body']['data']['diagnosis']['stage'] ?? null,
    'alice_sessions_finished' => $aliceOverview['sessions_finished'] ?? null,
    'ranking_top_1' => $ranking['body']['data']['items'][0] ?? null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

$kernel->shutdown();
