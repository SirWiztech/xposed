<?php
/**
 * XPOSED — Contact form endpoint (floating widget)
 * POST JSON { name, email, message } → JSON
 * Rate-limited per IP. Saves a lead and emails the business inbox.
 */

require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

require_post();

rate_limit('contact', (int)config('chat.rate_limit_per_hour'));

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);

$name    = trim((string)($data['name'] ?? ''));
$email   = trim((string)($data['email'] ?? ''));
$message = trim((string)($data['message'] ?? ''));

$errors = [];

if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
    $errors[] = 'Tell us your name (2–120 chars).';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 191) {
    $errors[] = 'That email doesn’t look right.';
}
if (mb_strlen($message) < 2 || mb_strlen($message) > 2000) {
    $errors[] = 'Message needs to be between 2 and 2000 chars.';
}

if ($errors) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => implode(' ', $errors)]);
    exit;
}

$st = db()->prepare('INSERT INTO contact_messages (ip, name, email, message) VALUES (?, ?, ?, ?)');
$st->execute([client_ip(), $name, $email, $message]);

$to      = config('business_email');
$subject = 'Xposed contact form: ' . mb_substr($name, 0, 60);
$body    = "Name: {$name}\nEmail: {$email}\nIP: " . client_ip() . "\n\n{$message}";
$headers = 'From: no-reply@xposed.local' . "\r\n" . 'Reply-To: ' . $email;
$sent    = $to ? @mail($to, $subject, $body, $headers) : false;

echo json_encode([
    'ok'    => true,
    'sent'  => (bool)$sent,
    'note'  => 'Message received — Cody’s team will get back to you at ' . $email . '.',
]);