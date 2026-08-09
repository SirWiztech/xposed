<?php
/**
 * XPOSED — Site configuration
 *
 * Override any value with a real environment variable. Never commit secrets.
 */

// Load local secrets from a gitignored .env (see .env.example).
require_once __DIR__ . '/../app/helpers/env.php';
load_env(__DIR__ . '/../.env');

return [
    'app' => [
        'name'    => 'Xposed',
        'base_url' => rtrim(getenv('XPOSED_BASE_URL') ?: '/xposed', '/'), // WAMP subfolder
        'env'     => getenv('XPOSED_ENV') ?: 'local',
        'debug'   => (getenv('XPOSED_DEBUG') ?: '0') === '1',
    ],

    'db' => [
        'host'    => getenv('XPOSED_DB_HOST') ?: '127.0.0.1',
        'name'    => getenv('XPOSED_DB_NAME') ?: 'xposed',
        'user'    => getenv('XPOSED_DB_USER') ?: 'root',
        'pass'    => getenv('XPOSED_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],

    // Secret used for CSRF/session integrity. Override in production.
    'app_key' => getenv('XPOSED_APP_KEY') ?: 'change-me-xposed-2026',

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
