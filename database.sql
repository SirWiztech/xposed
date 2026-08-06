-- ============================================================
-- XPOSED — Database schema + seed
-- Import: mysql -u root < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS xposed
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE xposed;

-- ---------- settings (key/value) ----------
CREATE TABLE IF NOT EXISTS settings (
  skey    VARCHAR(64) PRIMARY KEY,
  svalue  TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- videos (cached YouTube uploads) ----------
CREATE TABLE IF NOT EXISTS videos (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  youtube_id   VARCHAR(32) NULL UNIQUE,
  title        VARCHAR(255) NOT NULL,
  description  TEXT,
  thumb        VARCHAR(255),
  duration     VARCHAR(16)  DEFAULT '0:00',
  view_count   INT UNSIGNED DEFAULT 0,
  published_at DATETIME DEFAULT NULL,
  position     INT UNSIGNED DEFAULT 0,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- posts / tags ----------
CREATE TABLE IF NOT EXISTS posts (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(255) NOT NULL,
  slug         VARCHAR(191) NOT NULL UNIQUE,
  excerpt      VARCHAR(500) DEFAULT '',
  cover_image  VARCHAR(255) DEFAULT '',
  body         MEDIUMTEXT,
  status       ENUM('published','draft') DEFAULT 'published',
  published_at DATETIME DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tags (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(64) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS post_tags (
  post_id INT UNSIGNED NOT NULL,
  tag_id  INT UNSIGNED NOT NULL,
  PRIMARY KEY (post_id, tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- faqs ----------
CREATE TABLE IF NOT EXISTS faqs (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(255) NOT NULL,
  keywords VARCHAR(500) DEFAULT '',
  answer   TEXT NOT NULL,
  category VARCHAR(64)  DEFAULT 'General',
  sort     INT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- products ----------
CREATE TABLE IF NOT EXISTS products (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(255) NOT NULL,
  slug        VARCHAR(191) NOT NULL UNIQUE,
  description TEXT,
  price_cents INT UNSIGNED DEFAULT 0,
  currency    VARCHAR(8)   DEFAULT 'CAD',
  image       VARCHAR(255) DEFAULT '',
  category    VARCHAR(64)  DEFAULT '',
  type        ENUM('apparel','digital') DEFAULT 'apparel',
  featured    TINYINT(1)   DEFAULT 0,
  active      TINYINT(1)   DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_variants (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  name       VARCHAR(64)  NOT NULL,
  sort       INT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- customers / orders ----------
CREATE TABLE IF NOT EXISTS customers (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(191) NOT NULL UNIQUE,
  name       VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_ref         VARCHAR(16) NOT NULL UNIQUE,
  customer_id       INT UNSIGNED NULL,
  email             VARCHAR(191) NOT NULL,
  total_cents       INT UNSIGNED DEFAULT 0,
  currency          VARCHAR(8)   DEFAULT 'CAD',
  status            VARCHAR(24)  DEFAULT 'pending',
  payment_method    VARCHAR(24)  DEFAULT 'pending',
  stripe_session_id VARCHAR(128) DEFAULT '',
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id         INT UNSIGNED NOT NULL,
  product_id       INT UNSIGNED NULL,
  variant_id       INT UNSIGNED NULL,
  product_name     VARCHAR(255) NOT NULL,
  variant_name     VARCHAR(64)  DEFAULT '',
  qty              INT UNSIGNED DEFAULT 1,
  unit_price_cents INT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- security / misc ----------
CREATE TABLE IF NOT EXISTS admins (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(191) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          VARCHAR(24)  DEFAULT 'admin',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rate_limits (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rkey       VARCHAR(32) NOT NULL,
  ip         VARCHAR(45) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_rate (rkey, ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_messages (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip         VARCHAR(45) DEFAULT '',
  question   TEXT,
  answer     TEXT,
  faq_id     INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- contact form submissions (floating widget) ----------
CREATE TABLE IF NOT EXISTS contact_messages (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip         VARCHAR(45) DEFAULT '',
  name       VARCHAR(120) DEFAULT '',
  email      VARCHAR(191) DEFAULT '',
  message    TEXT,
  replied    TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA
-- ============================================================
INSERT INTO settings (skey, svalue) VALUES
  ('is_live', '0'),
  ('youtube_last_sync', ''),
  ('tagline', 'Xposed — 9 years live. No filter. All in.'),
  ('subs', '541000'),
  ('views', '41290000'),
  ('years_live', '9')
ON DUPLICATE KEY UPDATE svalue = VALUES(svalue);

INSERT INTO videos (youtube_id, title, description, thumb, duration, view_count, published_at, position) VALUES
  (NULL, 'I Turned $100 Into This... (Session Recap)',   'Session recap with the numbers.',  '', '18:42', 212000, DATE_SUB(NOW(), INTERVAL 2 DAY), 0),
  (NULL, 'The Highest Multiplier I''ve Ever Hit, Live',   'The one that nearly broke the chat.', '', '24:10', 498000, DATE_SUB(NOW(), INTERVAL 6 DAY), 1),
  (NULL, 'Why Everyone Is Wrong About Volatility',        'A short primer on volatility.',      '', '15:03', 156000, DATE_SUB(NOW(), INTERVAL 9 DAY), 2),
  (NULL, '9 Years Live — What Changed, What Didn''t',     'Reflection on the nearly-a-decade.', '', '21:57', 331000, DATE_SUB(NOW(), INTERVAL 14 DAY), 3),
  (NULL, 'Reading Chat While Down Bad (Not Clickbait)',   'A real one.',                        '', '19:22', 187000, DATE_SUB(NOW(), INTERVAL 21 DAY), 4)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO products (name, slug, description, price_cents, currency, image, category, type, featured, active) VALUES
  ('Reel Hoodie — Oil Black', 'reel-hoodie-oil-black',
   'Fleece-backed hoodie. Reel-tick logo chest print in oil red.',                      3400, 'CAD', 'assets/hoodie-black.jpg', 'Apparel', 'apparel', 1, 1),
  ('Xposed Backpack — Oil Black', 'xposed-backpack-oil-black',
   'Daily-carry backpack with padded side sleeve. Signal-red embroidered logo.',      6800, 'CAD', 'assets/back-pack-black.jpg', 'Apparel', 'apparel', 0, 1),
  ('Signal Cap — Black',   'signal-cap-black',
   'Structured snapback cap, oil-black crown, signal-red Xposed embroidery.',         2400, 'CAD', 'assets/cap-black.jpg', 'Apparel', 'apparel', 0, 1),
  ('Signal Cap — White',   'signal-cap-white',
   'Structured snapback cap, paper-white crown. Signal-red Xposed embroidery.',       2400, 'CAD', 'assets/cap-white.jpg', 'Apparel', 'apparel', 0, 1),
  ('Hydration Bottle — Black', 'hydration-bottle-black',
   'Insulated stainless bottle in oil black with signal-red logo.',                   2600, 'CAD', 'assets/drinking-bottle-black.jpg', 'Apparel', 'apparel', 0, 1),
  ('Reel Hoodie — White',  'reel-hoodie-white',
   'Fleece-backed hoodie. Reel-tick logo chest print in oil black.',                  6200, 'CAD', 'assets/hoodie-white.jpg', 'Apparel', 'apparel', 0, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), image = VALUES(image), price_cents = VALUES(price_cents);

INSERT INTO product_variants (product_id, name, sort) VALUES
  (1, 'S', 0), (1, 'M', 1), (1, 'L', 2), (1, 'XL', 3),
  (2, 'S', 0), (2, 'M', 1), (2, 'L', 2), (2, 'XL', 3),
  (3, 'S', 0), (3, 'M', 1), (3, 'L', 2), (3, 'XL', 3),
  (4, 'S', 0), (4, 'M', 1), (4, 'L', 2), (4, 'XL', 3),
  (5, 'S', 0), (5, 'M', 1), (5, 'L', 2), (5, 'XL', 3),
  (6, 'S', 0), (6, 'M', 1), (6, 'L', 2), (6, 'XL', 3)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO faqs (question, keywords, answer, category, sort) VALUES
  ('When does Xposed stream on Kick?', 'schedule, when, time, live, kick, hours, stream',
   'He goes live most days on Kick — typically evenings Canada time, but the schedule flexes. The pinned LIVE/offline pill in the nav is the fastest way to know if he is on right now.', 'Schedule', 1),
  ('How do I watch the livestream?', 'watch, live, stream, kick, watch live',
   'Everything streams live on Kick at kick.com/xposed. During streams you’ll also see a LIVE badge on this site. Offline? Catch the latest VOD on the Live page.', 'Schedule', 2),
  ('Where can I find past streams / VODs?', 'vod, past, replay, archive, old stream',
   'Recent VODs are available on the Live page when the channel is offline. Long-form uploads and highlights go on YouTube at @XposedLIVE.', 'Schedule', 3),
  ('Are you on Twitch too?', 'twitch, platform, site, other platform',
   'Yes — twitch.tv/xposed is the backup/secondary channel. Kick is the primary daily spot.', 'Platforms', 4),
  ('How can I contact you for business?', 'business, email, contact, sponsor, brand, partnership, enquiries',
   'Business enquiries go to businessxposed@gmail.com — sponsorships, brands, and collabs. That inbox gets read; a reply might just take a couple days.', 'Contact', 5),
  ('Where can I order merch?', 'merch, store, shop, buy, order, shipping',
   'The Store page has apparel and digital tools. Orders are confirmed by email. Shipping details are shown at checkout.', 'Store', 6),
  ('Do you ship internationally?', 'shipping, ship, international, delivery, postal',
   'Apparel ships from Canada and ships internationally where carriers allow it. Digital tools are delivered instantly by email — no shipping needed.', 'Store', 7),
  ('What is the Bankroll Tracker worth?', 'bankroll, tracker, spreadsheet, tool, review, worth, pro',
   'Bankroll Tracker Pro is a downloadable spreadsheet that tracks sessions, pacing, and bankroll drawdown. It’s a flat CAD $12, delivered by email after checkout.', 'Store', 8),
  ('How long have you been streaming?', 'how long, years, started, history, when, began',
   'Since 2017 — 9 years straight. He’s been a Twitch partner for 5 of those years and now streams primarily on Kick.', 'About', 9),
  ('How many subscribers on YouTube?', 'subs, subscribers, youtube, count, followers',
   '541K subscribers and 41M+ views across the channel. Numbers are live-tracked on this site.', 'About', 10),
  ('Do the slots you play pay out?', 'slots, casino, rtp, payout, real money, rtp',
   'All casino content is entertainment, real money, adults 18+. RTP and volatility are discussed openly in YouTube videos and on the Tools page. If it stops being fun, resources are at BeGambleAware.org.', 'Casino', 11),
  ('What casino tools does Xposed use?', 'tools, software, casino tools, tips, guide',
   'Everything he uses is listed on the Tools page — session trackers, RTP glossaries, and bankroll methods. Nothing is a guaranteed win; it’s about bankroll discipline.', 'Casino', 12),
  ('Is gambling allowed on here?', 'gambling, responsible, 18, legal, age, addiction',
   'You must be 18+. Gambling content is for entertainment only and carries real risk. If it stops being fun, help is free at BeGambleAware.org — always.', 'Casino', 13)
ON DUPLICATE KEY UPDATE question = VALUES(question);

INSERT INTO posts (title, slug, excerpt, cover_image, body, status, published_at) VALUES
  ('Nine Years In: What I Wish I Knew on Day One', 'nine-years-in',
   'A partner badge doesn’t change the first hour of an empty stream. Some notes from the years before anyone was watching.',
   'assets/xposed-blog.png',
   '## The room was empty\nIt didn’t drop content. There were four people watching and two of them were me on a second account.\n\n## Show up anyway\nAudience is something you earn in minutes nobody else sees. The viewer count is a lagging indicator — the habit is the leading one.\n\n## Film everything\nNothing from a stream is wasted. Best bits become VODs, clips become Shorts, and the archive becomes proof you never quit. That’s been the whole method since 2017.',
   'published', DATE_SUB(NOW(), INTERVAL 7 DAY))
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO admins (email, password_hash, role) VALUES
  ('admin@xposed.local', '$2y$12$95Ar8B0KcBqFsFhOJUHbjO8rcg0mcY308h3rkim1J/k.9jATCVXoS', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);