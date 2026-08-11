<?php
/**
 * XPOSED — Site configuration
 *
 * Override any value with a real environment variable. Never commit secrets.
 */

// Load local secrets from a gitignored .env (see .env.example).
require_once __DIR__ . '/../app/helpers/env.php';
load_env(__DIR__ . '/../.env');

// Auto-detect the install subfolder. The app root is the directory holding
// config/ — deriving its URL prefix from DOCUMENT_ROOT is correct on every
// page, including /admin/* (SCRIPT_NAME-based detection would wrongly treat
// the current script's directory, so admin pages came out with a doubled
// "admin" segment). Override with XPOSED_BASE_URL.
$autoBase = '';
$appRoot  = str_replace('\\', '/', dirname(__DIR__)); // e.g. C:/wamp64/www/xposed

if (!empty($_SERVER['DOCUMENT_ROOT'])) {
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    if ($docRoot !== '' && str_starts_with($appRoot, $docRoot)) {
        $rel = ltrim(substr($appRoot, strlen($docRoot)), '/');
        $autoBase = $rel !== '' ? '/' . $rel : '';
    }
}

// Fallback for hosts that don't set DOCUMENT_ROOT: use the request URI path up
// to the root-level public file where possible (root pages only — admin pages
// require DOCUMENT_ROOT or an explicit XPOSED_BASE_URL).
if ($autoBase === ''
    && isset($_SERVER['SCRIPT_NAME'])
    && !str_contains((string)$_SERVER['SCRIPT_NAME'], '/admin')) {
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if ($dir !== '' && $dir !== '.') {
        $autoBase = $dir;
    }
}

return [
    'app' => [
        'name'    => 'Xposed',
        'base_url' => rtrim(getenv('XPOSED_BASE_URL') ?: $autoBase, '/'),
        'env'     => getenv('XPOSED_ENV') ?: 'local',
        'debug'   => (getenv('XPOSED_DEBUG') ?: '0') === '1',
    ],

    // Database — credentials are sourced from the gitignored `.env` file
    // (XPOSED_DB_*). When those aren't set, fall back to the vars Wasmer
    // injects for its auto-provisioned MySQL (DB_HOST/DB_PORT/DB_NAME/
    // DB_USER/DB_USERNAME/DB_PASSWORD). The last fallbacks are WAMP-local
    // dev defaults only; never put real production credentials in this file.
    'db' => [
        'host'    => getenv('XPOSED_DB_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => getenv('XPOSED_DB_PORT') ?: getenv('DB_PORT') ?: '3306',
        'name'    => getenv('XPOSED_DB_NAME') ?: getenv('DB_NAME') ?: 'xposed',
        'user'    => getenv('XPOSED_DB_USER') ?: (getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: 'root')),
        'pass'    => getenv('XPOSED_DB_PASS') ?: getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],

    // Secret used for CSRF/session integrity. Override in production.
    'app_key' => getenv('XPOSED_APP_KEY') ?: 'change-me-xposed-2026',

    // Admin login password (from the gitignored .env). When set it takes
    // precedence over the bcrypt hash in the admins table.
    'admin' => [
        'password' => getenv('XPOSED_ADMIN_PASSWORD') ?: '',
    ],

    // YouTube sync (RSS works without a key; API key optional for the fallback path).
    'youtube' => [
        'api_key' => getenv('YOUTUBE_API_KEY') ?: '',
        'channel_id' => getenv('YOUTUBE_CHANNEL_ID') ?: 'UC0U5sBczEr2ASp4T6hSLvag',
        'cache_minutes' => 60,
    ],

    // Stripe (optional — hosted checkout). Empty = email-order fallback.
    'stripe' => [
        'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
        'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
        'currency' => 'cad',
    ],

    // Business email shown across the site + order fallback recipient.
    'business_email' => 'businessxposed@gmail.com',
    'site_url'       => rtrim(getenv('XPOSED_SITE_URL') ?: '', '/'),

    // SEO + social-sharing defaults (override via env / admin later).
    'seo' => [
        'site_name'          => 'Xposed',
        'default_title'      => 'Xposed — 9 Years Live. No Filter. All In.',
        'default_description' => 'Xposed (Cody) — Twitch & Kick partner, 541K+ on YouTube. Watch live, catch the latest uploads, and go behind the reel.',
        'og_image'           => rtrim(getenv('XPOSED_OG_IMAGE') ?: '/assets/Cody-image.jpg', '/'),
        'twitter_handle'     => getenv('XPOSED_TWITTER_HANDLE') ?: '@Xposed',
        'locale'             => 'en_US',
    ],

    // Kick channel + embed targets (embed spec can change — verify at build).
    'kick' => [
        'channel' => 'xposed',
        'player'  => 'https://player.kick.com/xposed',
        'chat'    => 'https://kick.com/xposed/chatroom',
    ],

    // Twitch embed (Twitch Embed API requires the exact parent domain).
    'twitch' => [
        'channel' => 'xposed',
        'parents' => ['localhost', '127.0.0.1'],
    ],

    'chat' => [
        'rate_limit_per_hour' => 20,
    ],

    // Google AI Studio (Gemini) — the AI chat assistant. Key stays server-side.
    'google_ai' => [
        'api_key' => getenv('GOOGLE_AI_API_KEY') ?: '',
        'model'   => getenv('GOOGLE_AI_MODEL') ?: 'gemini-3.6-flash',
        'timeout' => 30,
        'rate_limit_per_hour' => 40,
        // CA bundle used by cURL (WAMP ships one here). Set elsewhere on other hosts.
        'cafile'  => getenv('GOOGLE_AI_CAFILE') ?: 'C:/wamp64/bin/php/php8.5.0/cacert.pem',
    ],
];
