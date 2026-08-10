# Xposed — Official Creator Website

Live creator site for **Xposed** (Cody) — Twitch &amp; Kick partner, 541K+ on YouTube.
Built on **PHP 8 + MySQL + vanilla JS + a token-based CSS system**. No Node build server, no heavy framework — deployable on standard shared / VPS PHP hosting.

## Stack
- **Markup:** Semantic HTML5
- **Styling:** Native CSS3 design tokens (`assets/css/main.css`) — Oil Black `#0A0A0B`, Signal Red `#E10600`
- **Interactivity:** Vanilla JS ES modules (`assets/js/`)
- **Backend:** PHP, procedural + lightweight MVC-ish layout (`/app`, `/config`)
- **DB:** MySQL (PDO prepared statements everywhere)
- **Fonts:** Self-hosted Anton + Inter (`assets/fonts/`, Google CDN no longer required)

## Setup

### 1. Database
```
mysql -u root --default-character-set=utf8mb4 < database.sql
```
> The `--default-character-set=utf8mb4` flag is required so the em-dashes and accents seed correctly — otherwise text like `—` is stored as garbled `ÔÇö`.

Options: deploy on WAMP (this repo), or point `config/config.php` at your host.

### 2. Config
All secrets (database credentials, API keys) live in a gitignored **`.env`** file at the project root — copy `.env.example` and fill in real values; never commit secrets.
`config/config.php` reads these via `getenv()` (loaded by `app/helpers/env.php`). Dev fallbacks in the config assume WAMP local (`root` / no password) only.

Key variables (put these in `.env`):

| Var | Purpose |
|-----|---------|
| `XPOSED_DB_HOST` / `XPOSED_DB_PORT` / `XPOSED_DB_NAME` / `XPOSED_DB_USER` / `XPOSED_DB_PASS` | Database credentials + port (required on any host) |
| `SHIPIT_PHP_VERSION` | PHP version for InfinityFree's "phpix" runtime (e.g. `8.2`); app requires PHP 8+ |
| `XPOSED_BASE_URL` | Site base path (e.g. `/xposed` on WAMP, `/` at domain root) |
| `YOUTUBE_API_KEY` | YouTube Data API v3 key (enables the "Sync from YouTube" button) |
| `YOUTUBE_CHANNEL_ID` | Channel handle, e.g. `XposedLIVE` |
| `STRIPE_SECRET_KEY` / `STRIPE_PUBLISHABLE_KEY` | Stripe hosted checkout. Empty = email-order fallback |
| `XPOSED_APP_KEY` | Passphrase used for session/CSRF integrity (set a random value in production) |
| `XPOSED_DEBUG` | `1` shows errors, `0` hides them (production) |
| `XPOSED_SITE_URL` | Absolute site URL used for Stripe redirect URLs (optional; auto-detected otherwise) |

Change the admin password after first login (see below).

## Admin
Login at `/admin/` — seeded `admin@xposed.local` / `xposed2026`.
From the dashboard you can: toggle **LIVE/OFFLINE**, update homepage stats, sync/curate **videos**, manage **blog posts**, **products**, **chat FAQ answers**, and update **orders**. No code edits needed for day-to-day updates.

## Features (map to the build brief)
- **Homepage** — hero, marquee strip, video rail, about + count-up stats, store teaser, blog teaser, connect grid.
- **Live** — Kick player + chat embed with a graceful offline state and ON-AIR strip.
- **Videos** — paginated archive of cached YouTube uploads; admin "Sync from YouTube" pulls via Data API v3.
- **Store** — product grid, product page with variants, session cart, checkout (Stripe hosted → or email-order fallback), order confirmation + receipt.
- **Tools** — client-side bankroll/session tracker, RTP/volatility glossary, tools-used cards, and a prominent 18+ responsible-gambling notice.
- **Blog** — card index + long-form reader with a safe Markdown-ish renderer.
- **Chat** — floating FAQ widget backed by a rate-limited keyword matcher over the `faqs` table, always offering `businessxposed@gmail.com`.
- **Security** — PDO prepared statements, CSRF on every form, hashed admin auth + login rate-limit, upload whitelist (images only, PHP execution blocked in `/uploads`), `/app` + `/config` denied via `.htaccess`.

## Stripe / Kick notes
- **Stripe:** whenever `STRIPE_SECRET_KEY` is set, checkout creates a real hosted session and the order is recorded. Without it the site falls back to email orders so the MVP works immediately.
- **Kick embed:** the player and chat iframes are configured in `config/config.php` (`kick.player`, `kick.chat`). Kick's embed spec has changed before — verify `https://player.kick.com/xposed` at go-live.
- **Live status** is a manual admin toggle for the MVP. Replace `setting('is_live')` with a Kick API poll later.

## Project layout
```
index.php, live.php, videos.php, store.php, product.php,
cart.php, checkout.php, order-confirmation.php, tools.php,
blog.php, post.php, chat.php        # public entry files
admin/                              # admin panel
app/controllers app/models app/views app/helpers
config/                             # config + PDO (denied via .htaccess)
assets/css assets/js assets/fonts
uploads/                            # admin-uploaded images (PHP blocked)
database.sql                        # schema + seed
```

## Roadmap / not in the MVP
Real Kick live-status API, LLM chat upgrade path, second YouTube channel, shipping/tax configuration for merch, casino-tools monetization.



## Admin Login Credentials 
- URL: http://localhost/xposed/admin/ (default WAMP subfolder; adjust if your vhost differs)

- Email: admin@xposed.local

- Password: xposed2026