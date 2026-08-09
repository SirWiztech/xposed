<?php
/**
 * XPOSED — AI chat proxy endpoint.
 * POST { message: string, history: [{role, content}] } → Gemini generateContent.
 *
 * Security: the Gemini API key lives only here / in .env (gitignored + .htaccess-blocked).
 * The frontend never touches the Gemini API directly.
 */

require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_post();

// Skip rate limiting in local dev so testing doesn't lock you out.
// APP_ENV should be 'local' in your .env on your machine and 'production' on the live server.
if (config('app.env') !== 'local') {
    rate_limit('ai_chat', (int)config('google_ai.rate_limit_per_hour'));
}

$raw   = file_get_contents('php://input');
$data  = json_decode($raw ?: '', true);
$valid = is_array($data);

$message = $valid ? mb_substr(trim((string)($data['message'] ?? '')), 0, 4000) : '';

if ($message === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Type a message first.']);
    exit;
}

$key = (string)config('google_ai.api_key');
if ($key === '') {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'AI assistant isn’t configured yet — come back soon.']);
    exit;
}

$model = (string)config('google_ai.model');

// Controlled, length-capped history (client-sent, last 10 turns only).
$history = ($valid && is_array($data['history'] ?? null)) ? array_slice($data['history'], -10) : [];
$contents = [];
foreach ($history as $h) {
    if (!is_array($h)) continue;
    $text = mb_substr(trim((string)($h['content'] ?? '')), 0, 2000);
    if ($text === '') continue;
    $role = (($h['role'] ?? '') === 'assistant') ? 'model' : 'user';
    $contents[] = ['role' => $role, 'parts' => [['text' => $text]]];
}
$contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

// Manual-training knowledge base (see app/helpers/ai-knowledge.php).
require_once __DIR__ . '/app/helpers/ai-knowledge.php';
$system = ai_knowledge_base();

$payload = [
    'systemInstruction' => ['parts' => [['text' => $system]]],
    'contents'          => $contents,
    'generationConfig'  => ['temperature' => 0.7, 'topP' => 0.95, 'maxOutputTokens' => 1200],
];

$url = 'https://generativelanguage.googleapis.com/v1beta/models/'
    . rawurlencode($model) . ':generateContent';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'x-goog-api-key: ' . $key],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => (int)config('google_ai.timeout'),
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 10,
]);

// WAMP's Apache php.ini often omits curl.cainfo → explicit CA bundle fixes "get local issuer certificate".
$cafile = (string)config('google_ai.cafile');
if ($cafile !== '' && is_file($cafile)) {
    curl_setopt($ch, CURLOPT_CAINFO, $cafile);
}

$body = curl_exec($ch);
$code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($curlErr !== '') {
    http_response_code(504);
    echo json_encode(['ok' => false, 'error' => 'Assistant is unreachable right now — try again in a moment.']);
    exit;
}

if ($code === 200) {
    $j = json_decode((string)$body, true);
    $answer = trim((string)($j['candidates'][0]['content']['parts'][0]['text'] ?? ''));
    if ($answer !== '') {
        echo json_encode(['ok' => true, 'answer' => $answer]);
        exit;
    }
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'No reply from the model — try again.']);
    exit;
}

// Map Gemini errors to safe generic client messages (never echo the upstream body).
if ($code === 429) {
    http_response_code(429);
    $error = 'The AI assistant hit its limit right now — try again in a few minutes.';
} else {
    http_response_code(502);
    $error = 'Assistant is unreachable right now — try again in a moment.';
}
echo json_encode(['ok' => false, 'error' => $error]);