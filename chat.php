<?php
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

require_post();

// Skip rate limiting in local dev so testing doesn't lock you out.
// APP_ENV should be 'local' in your .env on your machine and 'production' on the live server.
if (config('app.env') !== 'local') {
    rate_limit('chat', (int)config('chat.rate_limit_per_hour'));
}

$raw = file_get_contents('php://input');
// ... rest unchanged
$data = json_decode($raw ?: '', true);
$question = trim((string)($data['q'] ?? ''));

if ($question === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ask a question and I’ll do my best.']);
    exit;
}

$match = Faq::match($question);

if ($match) {
    [$faq, $score] = $match;
    $answer = $faq['answer'] . "\n\n— Still stuck? Email businessxposed@gmail.com.";
    $faqId = (int)$faq['id'];
} else {
    $answer = 'Not 100% sure on that one — the fastest answer is usually in the pinned LIVE pill or the Connect section. Still stuck? Email businessxposed@gmail.com and Cody’s team will get back to you.';
    $faqId = null;
}

// Log the exchange (keeps FAQ gaps visible in the admin dashboard later).
$st = db()->prepare('INSERT INTO chat_messages (ip, question, answer, faq_id) VALUES (?, ?, ?, ?)');
$st->execute([client_ip(), $question, $answer, $faqId]);

echo json_encode(['ok' => true, 'answer' => $answer, 'faq_id' => $faqId]);
