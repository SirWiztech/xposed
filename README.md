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
| `XPOSED_ADMIN_PASSWORD` | Admin login password (takes precedence over the `admins`-table hash) |
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

What the site is
Xposed is your creator-site (PHP + MySQL, no framework). Visitors get a homepage, live-stream watch page, store with cart/checkout, video archive, blog, a casino-tools hub, and two chat assistants (FAQ bot + Gemini AI). Behind a login at /admin/ you control the store, blog, videos, FAQ answers, orders, the LIVE/offline toggle, and your headline stats.
---
Public pages (what visitors see/do)
Home (index.php) — landing page: your portrait, animated particle/red-orb background, the LIVE/offline pill, headline stats (subs, views, years live), latest 3 products, latest video + blog post, socials, and a link to Twitch. Data comes from the DB (the stats below are the same numbers you edit in admin).
Live (live.php) — Kick iframe player (primary) + Twitch embed. Twitch only renders on HTTPS/localhost; on your HTTP-only host it shows a graceful "Open Twitch →" fallback card (by design). When offline it shows your latest 3 VODs.
Tools (tools.php) — all client-side, saved in the visitor's browser localStorage (no DB):
- Progression Calculator (6 strategies) + Progression Calculator 2 (8 systems)
- Roulette Spin Simulator (European wheel, CSV export)
- Bankroll Calculator
- "Originals" image boards (Chicken/Keno/Mines/Plinko/Tower/Wheel)
- Session Tracker + Challenge Tracker (log sessions, chart bankroll with Chart.js)
- RTP/volatility glossary + responsible-gambling links (BeGambleAware)
Videos (videos.php) — paginated YouTube archive, newest first, from the videos table (kept fresh by the YouTube sync — see Admin → Videos).
Blog (blog.php / post.php) — paginated posts; article view renders Markdown-ish bodies (## , bold, lists).
Store (store.php → product.php → cart.php → checkout.php → order-confirmation.php) — category chips, product detail with size variants + quantity, session-backed cart (update/remove per line), then checkout. Two payment modes chosen by config:
- If STRIPE_SECRET_KEY is set → Stripe hosted Checkout via server-side cURL.
- Otherwise (current live state) → "email-order" mode: order recorded, confirmation email sent, status set to email-order. That's how orders work until you add a Stripe key.
Contact + Chat widgets (floating, every page):
- chat.php — FAQ bot: keyword-matches your FAQ answers (see Admin → FAQ). Falls back to a polite "not sure" reply. Rate-limited per IP (skipped in local dev).
- ai-chat.php — Gemini assistant (needs GOOGLE_AI_API_KEY in .env). Server-side proxy (key never leaves server), controlled 10-turn history, tuned via the knowledge base in app/helpers/ai-knowledge.php (edit that file to change the bot's voice/rules — no API code touched). Also rate-limited.
- contact.php — lead form: saves to contact_messages + emails businessxposed@gmail.com.
---
Admin area (/admin/index.php) — login & daily ops
Login: admin/index.php (email + password against the admins table; the seed admin is admin@xposed.local with the bcrypt hash from database.sql — change the password). All actions are CSRF-protected and forms rate-limit login attempts.
Section	What you do there
Dashboard	Toggle LIVE/OFFLINE (drives the nav pill + live page state), edit subs / views / years-live that show on the homepage. Shows "YouTube last sync" timestamp.
Videos	Sync from YouTube (button) — pulls the channel RSS keylessly; falls back to the Data API if YOUTUBE_API_KEY is set. Also add videos manually (title, duration, views, date, position), delete, and it flags Shorts.
Blog (posts.php)	List/edit/delete posts; + New post → post-edit.php (Title, slug, excerpt, cover image upload, Markdown-lite body, status Published/Draft, publish date).
Store (products.php)	List/edit/delete products; + New product → product-edit.php (name, slug, description, price in cents, image upload, category, type apparel/digital, featured, active, variant sizes S–XL).
FAQ (faqs.php)	Add/edit/delete answers. Keywords matter — the chat.php bot matches the visitor's question against these comma-separated keywords; category + sort order the list.
Orders (orders.php)	See recent 100 orders (ref, email, total, payment, status); update status pending → email-order → paid → shipped → fulfilled → cancelled for fulfillment.
---
## How the content stays fresh (automation)
- **YouTube**: `index`, `videos`, and `live` pages call `YoutubeSync::ensureFresh()`, which only re-fetches when the cache window (`youtube.cache_minutes`, 60) expires — so the archive updates itself every hour max; failures never break a page.
- **Stats**: hand-edited in Dashboard (no auto API for subs/views yet).
---
Config, secrets & deployment (the parts you manage)
- Everything in config/config.php reads env vars; secrets live in the gitignored .env (template: .env.example).
- DB credentials are XPOSED_DB_HOST/PORT/NAME/USER/PASS, falling back to Wasmer-injected DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_USERNAME/DB_PASSWORD (that's what your live DB uses).
- Other env knobs: XPOSED_BASE_URL, XPOSED_ENV (local skips chat rate limits), XPOSED_DEBUG, GOOGLE_AI_API_KEY/GOOGLE_AI_MODEL, STRIPE_SECRET_KEY/STRIPE_PUBLISHABLE_KEY, XPOSED_SITE_URL, XPOSED_APP_KEY (session integrity), YOUTUBE_API_KEY/YOUTUBE_CHANNEL_ID.
- Shipping note: if Stripe is added, checkout redirects to Stripe and refund/digital-delivery happens from your side after paid.
---
Security built in
CSRF tokens on every form, session_regenerate_id on admin login, per-IP rate limiting (rate_limits table), parameterized SQL everywhere, output escaped with e(), uploads served as static (PHP execution blocked), and errors rendered as a friendly page (full trace only in debug mode).