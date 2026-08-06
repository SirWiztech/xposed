<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xposed · Kick & Twitch</title>
    <style>
        * { box-sizing: border-box; margin: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0a0a0a;
            color: #f0f0f0;
            padding: 24px 20px 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .app {
            max-width: 1400px;
            width: 100%;
        }
        h1 {
            font-weight: 300;
            letter-spacing: 2px;
            font-size: 2rem;
            margin-bottom: 32px;
            border-left: 4px solid #ff0040;
            padding-left: 20px;
            color: #fff;
        }
        .platform-section {
            margin-bottom: 64px;
        }
        /* ---------- main TV card ---------- */
        .stream-card {
            background: #141414;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,0.8);
            border: 1px solid #282828;
        }
        .stream-header {
            padding: 12px 20px;
            background: #0e0e0e;
            border-bottom: 1px solid #262626;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px 14px;
        }
        .platform-badge {
            font-weight: 700;
            font-size: 0.75rem;
            padding: 4px 14px;
            border-radius: 30px;
            letter-spacing: 0.5px;
        }
        .platform-badge.kick { background: #53fc18; color: #0a0a0a; }
        .platform-badge.twitch { background: #9146ff; color: #fff; }
        .live-badge {
            background: #ff0040;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            animation: pulse 1.8s infinite;
        }
        .vod-badge {
            background: #2a2a2a;
            color: #bbb;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 12px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .streamer-name {
            margin-left: auto;
            font-weight: 300;
            color: #aaa;
            font-size: 0.95rem;
        }
        .back-to-live-btn {
            background: transparent;
            border: 1px solid #444;
            color: #ccc;
            font-size: 0.7rem;
            padding: 3px 12px;
            border-radius: 30px;
            cursor: pointer;
            display: none;
            transition: 0.15s;
        }
        .back-to-live-btn:hover { border-color: #aaa; color: #fff; }
        @keyframes pulse {
            0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; }
        }

        /* ----- player wrapper 16:9 ----- */
        .stream-player {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            background: #000;
        }
        .stream-player iframe {
            position: absolute;
            top: 0; left: 0;
            width: 100% !important;
            height: 100% !important;
            border: 0;
        }

        /* ----- playlist row ----- */
        .playlist-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 20px 0 12px 4px;
        }
        .playlist-row {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding: 4px 2px 12px;
            scroll-snap-type: x proximity;
        }
        .playlist-row::-webkit-scrollbar { height: 5px; }
        .playlist-row::-webkit-scrollbar-thumb { background: #2a2a2a; border-radius: 12px; }

        .playlist-card {
            flex: 0 0 auto;
            width: 170px;
            background: #121212;
            border: 1px solid #252525;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.15s, border-color 0.15s;
            scroll-snap-align: start;
            text-decoration: none;
            color: inherit;
        }
        .playlist-card:hover {
            transform: translateY(-4px);
            border-color: #3a3a3a;
        }
        .playlist-card.active {
            border-color: #ff0040;
            box-shadow: 0 0 0 1px #ff0040;
        }
        .playlist-thumb {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            background: #1a1a1a;
            background-image: repeating-linear-gradient(45deg, #1c1c1c 0px, #1c1c1c 4px, #222 4px, #222 8px);
        }
        .playlist-thumb .play-icon {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .playlist-thumb .play-icon svg { width: 16px; height: 16px; fill: #fff; margin-left: 2px; }
        .playlist-thumb .ext-icon {
            position: absolute;
            top: 6px; right: 6px;
            background: rgba(0,0,0,0.65);
            border-radius: 6px;
            padding: 3px;
            display: flex;
        }
        .playlist-thumb .ext-icon svg { width: 12px; height: 12px; fill: #ccc; }
        .playlist-info {
            padding: 10px 12px 12px;
        }
        .playlist-title {
            font-size: 0.8rem;
            font-weight: 500;
            color: #eee;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .playlist-meta {
            font-size: 0.65rem;
            color: #777;
            margin-top: 3px;
        }

        @media (max-width: 600px) {
            .playlist-card { width: 150px; }
            h1 { font-size: 1.6rem; }
        }
    </style>
</head>
<body>
<div class="app">
    <h1>🎮 Xposed · Daily</h1>

    <!-- ===== KICK ===== -->
    <div class="platform-section">
        <div class="stream-card">
            <div class="stream-header">
                <span class="platform-badge kick">KICK</span>
                <span class="live-badge">● Live</span>
                <span class="streamer-name">/xposed</span>
            </div>
            <div class="stream-player">
                <iframe src="https://player.kick.com/xposed?autoplay=false&muted=false" allowfullscreen></iframe>
            </div>
        </div>
        <div class="playlist-label">📼 Recent Sessions</div>
        <div class="playlist-row" id="kick-playlist"></div>
    </div>

    <!-- ===== TWITCH ===== -->
    <div class="platform-section">
        <div class="stream-card">
            <div class="stream-header">
                <span class="platform-badge twitch">TWITCH</span>
                <span class="live-badge" id="twitch-live-badge">● Live</span>
                <span class="vod-badge" id="twitch-vod-badge" style="display:none;">Replay</span>
                <button class="back-to-live-btn" id="twitch-back-live">⬅ Live</button>
                <span class="streamer-name">/xposed</span>
            </div>
            <div class="stream-player">
                <iframe id="twitch-main-player"
                        src="https://player.twitch.tv/?channel=xposed&parent=localhost"
                        allowfullscreen>
                </iframe>
            </div>
        </div>
        <div class="playlist-label">📼 Recent Sessions</div>
        <div class="playlist-row" id="twitch-playlist"></div>
    </div>
</div>

<script>
    // ============================================================
    //  CONFIG —  set your VOD IDs & domains
    // ============================================================
    const PARENT_DOMAINS = ["localhost"];   // add your production domain

    const TWITCH_CHANNEL = "xposed";
    const KICK_CHANNEL = "xposed";

    // ---- Twitch VODS (id from twitch.tv/videos/XXXXX) ----
    const TWITCH_VODS = [
        { id: "2356555568", title: "Ranked grind pt.3", date: "Aug 4" },
        { id: "2353425159", title: "Chill Friday stream", date: "Aug 1" },
        { id: "2349211111", title: "Late night session", date: "Jul 29" },
    ];

    // ---- Kick VODS (full url) ----
    const KICK_VODS = [
        { url: "https://kick.com/xposed/videos/1234567890", title: "Ranked grind pt.3", date: "Aug 4" },
        { url: "https://kick.com/xposed/videos/0987654321", title: "Chill Friday stream", date: "Aug 1" },
        { url: "https://kick.com/xposed/videos/1122334455", title: "Late night session", date: "Jul 29" },
    ];

    // ============================================================
    //  RENDER
    // ============================================================
    const playIcon = `<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>`;
    const extIcon = `<svg viewBox="0 0 24 24"><path d="M14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7zM5 5v14h14v-7h-2v5H7V7h5V5H5z"/></svg>`;

    // ---- TWITCH PLAYLIST ----
    function renderTwitchPlaylist() {
        const row = document.getElementById('twitch-playlist');
        row.innerHTML = '';
        TWITCH_VODS.forEach((vod, idx) => {
            const card = document.createElement('div');
            card.className = 'playlist-card';
            card.dataset.index = idx;
            card.innerHTML = `
                <div class="playlist-thumb">
                    <div class="play-icon">${playIcon}</div>
                </div>
                <div class="playlist-info">
                    <div class="playlist-title">${vod.title}</div>
                    <div class="playlist-meta">${vod.date}</div>
                </div>
            `;
            card.addEventListener('click', () => playTwitchVod(vod, card));
            row.appendChild(card);
        });
    }

    function playTwitchVod(vod, cardEl) {
        const player = document.getElementById('twitch-main-player');
        const parentStr = PARENT_DOMAINS.map(d => `parent=${d}`).join('&');
        player.src = `https://player.twitch.tv/?video=${vod.id}&${parentStr}&autoplay=false`;

        document.getElementById('twitch-live-badge').style.display = 'none';
        const vodBadge = document.getElementById('twitch-vod-badge');
        vodBadge.style.display = 'inline-block';
        vodBadge.textContent = vod.title;
        document.getElementById('twitch-back-live').style.display = 'inline-block';

        document.querySelectorAll('#twitch-playlist .playlist-card')
            .forEach(c => c.classList.remove('active'));
        cardEl.classList.add('active');
    }

    function backToTwitchLive() {
        const player = document.getElementById('twitch-main-player');
        const parentStr = PARENT_DOMAINS.map(d => `parent=${d}`).join('&');
        player.src = `https://player.twitch.tv/?channel=${TWITCH_CHANNEL}&${parentStr}`;

        document.getElementById('twitch-live-badge').style.display = 'inline-block';
        document.getElementById('twitch-vod-badge').style.display = 'none';
        document.getElementById('twitch-back-live').style.display = 'none';

        document.querySelectorAll('#twitch-playlist .playlist-card')
            .forEach(c => c.classList.remove('active'));
    }

    document.getElementById('twitch-back-live').addEventListener('click', backToTwitchLive);

    // ---- KICK PLAYLIST (external links) ----
    function renderKickPlaylist() {
        const row = document.getElementById('kick-playlist');
        row.innerHTML = '';
        KICK_VODS.forEach(vod => {
            const card = document.createElement('a');
            card.className = 'playlist-card';
            card.href = vod.url;
            card.target = '_blank';
            card.rel = 'noopener noreferrer';
            card.innerHTML = `
                <div class="playlist-thumb">
                    <div class="play-icon">${playIcon}</div>
                    <div class="ext-icon">${extIcon}</div>
                </div>
                <div class="playlist-info">
                    <div class="playlist-title">${vod.title}</div>
                    <div class="playlist-meta">${vod.date} · Kick</div>
                </div>
            `;
            row.appendChild(card);
        });
    }

    // ---- INIT ----
    renderTwitchPlaylist();
    renderKickPlaylist();

    console.log('✅ Xposed · Kick & Twitch loaded — VODs ready');
</script>
</body>
</html>