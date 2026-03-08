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
        $server['HTTP_' . strtoupper(str_replace('-', '_', $name))] = $value;
    }

    $content = $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_UNICODE);
    $request = Request::create($path, strtoupper($method), [], [], [], $server, $content ?: null);
    $response = $kernel->handle($request);
    $decoded = json_decode((string) $response->getContent(), true);

    return [
        'status' => $response->getStatusCode(),
        'body' => is_array($decoded) ? $decoded : [],
    ];
}

$email = 'tester@estuda.local';
$password = '123456';
$name = 'Tester MVP';

$register = api_call($kernel, 'POST', '/api/v1/auth/register', [
    'name' => $name,
    'email' => $email,
    'password' => $password,
]);
if (!in_array($register['status'], [201, 409], true)) {
    throw new RuntimeException('Falha no registro de tester: ' . json_encode($register['body']));
}

$login = api_call($kernel, 'POST', '/api/v1/auth/login', [
    'email' => $email,
    'password' => $password,
]);
if ($login['status'] !== 200) {
    throw new RuntimeException('Falha no login de tester: ' . json_encode($login['body']));
}
$token = (string) ($login['body']['data']['access_token'] ?? '');
$auth = ['Authorization' => 'Bearer ' . $token];

$decks = api_call($kernel, 'GET', '/api/v1/decks');
if ($decks['status'] !== 200) {
    throw new RuntimeException('Falha ao listar decks: ' . json_encode($decks['body']));
}

$deckId = null;
$premiumDeckId = null;
$items = $decks['body']['data']['items'] ?? [];
if (is_array($items)) {
    foreach ($items as $item) {
        if (is_array($item) && (($item['title'] ?? null) === 'Deck Demo MVP')) {
            $deckId = (string) ($item['id'] ?? '');
        }
        if (is_array($item) && (($item['title'] ?? null) === 'Deck Premium MVP')) {
            $premiumDeckId = (string) ($item['id'] ?? '');
        }
    }
}

if ($deckId === null || $deckId === '') {
    $deck = api_call($kernel, 'POST', '/api/v1/decks', [
        'title' => 'Deck Demo MVP',
        'description' => 'Deck de teste para validacao do MVP',
    ], $auth);
    if ($deck['status'] !== 201) {
        throw new RuntimeException('Falha ao criar deck demo: ' . json_encode($deck['body']));
    }

    $deckId = (string) ($deck['body']['data']['id'] ?? '');

    $cards = [
        ['question' => '2 + 2', 'answer' => '4'],
        ['question' => '3 + 5', 'answer' => '8'],
        ['question' => '9 - 4', 'answer' => '5'],
    ];

    foreach ($cards as $card) {
        $created = api_call($kernel, 'POST', '/api/v1/flashcards', [
            'deck_id' => $deckId,
            'type' => 'QA',
            'question' => $card['question'],
            'answer' => $card['answer'],
        ], $auth);
        if ($created['status'] !== 201) {
            throw new RuntimeException('Falha ao criar card demo: ' . json_encode($created['body']));
        }
    }
}

if ($premiumDeckId === null || $premiumDeckId === '') {
    $premiumDeck = api_call($kernel, 'POST', '/api/v1/decks', [
        'title' => 'Deck Premium MVP',
        'description' => 'Deck pago para validacao de marketplace',
        'visibility' => 'paid',
        'price' => 14.9,
    ], $auth);
    if ($premiumDeck['status'] !== 201) {
        throw new RuntimeException('Falha ao criar deck premium: ' . json_encode($premiumDeck['body']));
    }

    $premiumDeckId = (string) ($premiumDeck['body']['data']['id'] ?? '');

    $premiumCard = api_call($kernel, 'POST', '/api/v1/flashcards', [
        'deck_id' => $premiumDeckId,
        'type' => 'QA',
        'question' => 'Capital do Brasil',
        'answer' => 'Brasilia',
    ], $auth);
    if ($premiumCard['status'] !== 201) {
        throw new RuntimeException('Falha ao criar card premium: ' . json_encode($premiumCard['body']));
    }
}

$challenges = api_call($kernel, 'GET', '/api/v1/challenges', [], $auth);
if ($challenges['status'] !== 200) {
    throw new RuntimeException('Falha ao listar desafios: ' . json_encode($challenges['body']));
}

$challengeId = null;
$challengeItems = $challenges['body']['data']['items'] ?? [];
if (is_array($challengeItems) && isset($challengeItems[0]) && is_array($challengeItems[0])) {
    $challengeId = (string) ($challengeItems[0]['id'] ?? '');
}

echo "Seed MVP concluido.\n";
echo json_encode([
    'tester_email' => $email,
    'tester_password' => $password,
    'deck_demo_id' => $deckId,
    'deck_premium_id' => $premiumDeckId,
    'challenge_id' => $challengeId,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

$kernel->shutdown();
