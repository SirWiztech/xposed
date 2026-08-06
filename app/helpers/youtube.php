<?php
/**
 * XPOSED — YouTube sync (caches uploads into `videos`).
 *
 * Primary path: YouTube RSS feed — works with NO API key.
 * Fallback path: YouTube Data API v3 (when YOUTUBE_API_KEY is set).
 * Runs server-side via curl; stores the last-sync timestamp in settings.
 */

class YoutubeSync
{
    private string $apiKey;
    private string $channelId;
    private int $maxResults;

    public function __construct()
    {
        $this->apiKey    = (string)config('youtube.api_key');
        $this->channelId = (string)config('youtube.channel_id');
        $this->maxResults = 50;
    }

    public function enabled(): bool
    {
        return $this->channelId !== '';
    }

    private function get(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_USERAGENT      => 'Xposed-Site/1.0',
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$body) {
            return null;
        }
        $json = json_decode($body, true);
        return is_array($json) ? $json : null;
    }

    /** Fetch raw channel RSS feed (no API key required). */
    private function fetchRss(): ?string
    {
        $ch = curl_init(
            'https://www.youtube.com/feeds/videos.xml?channel_id=' . urlencode($this->channelId)
        );
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$body) {
            return null;
        }
        return $body;
    }

    private function secondsToClock(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $i = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        if ($h > 0) return sprintf('%d:%02d:%02d', $h, $i, $s);
        return sprintf('%d:%02d', $i, $s);
    }

    /** Sync using the channel RSS feed — keyless and quota-free. */
    private function syncFromRss(): ?string
    {
        $body = $this->fetchRss();
        if ($body === null) {
            return 'YouTube sync failed: could not fetch the channel RSS feed.';
        }

        $xml = @simplexml_load_string($body);
        if ($xml === false || !isset($xml->entry)) {
            return 'YouTube sync failed: could not parse the RSS feed.';
        }

        $media = 'http://search.yahoo.com/mrss/';
        $count = 0;
        foreach ($xml->entry as $entry) {
            if ($count >= $this->maxResults) break;

            $href = (string)$entry->link['href'];
            if (!preg_match('#(?:[?&]v=|/shorts/)([A-Za-z0-9_-]{6,15})#', $href, $m)) continue;
            $vid = $m[1];

            $group   = $entry->children($media)->group;
            $thumb   = (string)$group->thumbnail->attributes()['url'];
            $thumb   = $thumb ?: 'https://img.youtube.com/vi/' . $vid . '/hqdefault.jpg';
            $views   = (int)$group->community->statistics->attributes()['views'];
            $seconds = (int)$group->content->attributes()['duration'];
            $desc    = strip_tags((string)$group->description);

            Video::upsertFromYoutube([
                'youtube_id'   => $vid,
                'title'        => trim((string)$entry->title),
                'description'  => $desc,
                'thumb'        => $thumb,
                'duration'     => $seconds > 0 ? $this->secondsToClock($seconds) : '',
                'view_count'   => $views,
                'published_at' => date('Y-m-d H:i:s', strtotime((string)$entry->published)),
                'position'     => $count,
            ]);
            $count++;
        }

        if ($count === 0) {
            return 'YouTube sync: no videos found in the feed.';
        }

        // Drop seed/manual rows (no youtube_id) so real uploads take over.
        db()->exec("DELETE FROM videos WHERE youtube_id = '' OR youtube_id IS NULL");

        setting_set('youtube_last_sync', now());
        return "Synced {$count} videos from YouTube.";
    }

    /** Resolve the channel's uploads playlist id. */
    private function uploadsPlaylistId(): ?string
    {
        $data = $this->get(
            'https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forHandle=' . urlencode($this->channelId) . '&key=' . $this->apiKey
        );
        return $data['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? null;
    }

    /** Fetch duration for a set of video ids (videos.list part=contentDetails). */
    private function durations(array $ids): array
    {
        $out = [];
        foreach (array_chunk($ids, 50) as $chunk) {
            $data = $this->get(
                'https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id=' . implode(',', $chunk) . '&key=' . $this->apiKey
            );
            foreach ($data['items'] ?? [] as $item) {
                $out[$item['id']] = $this->iso8601ToClock($item['contentDetails']['duration'] ?? 'PT0S');
            }
        }
        return $out;
    }

    private function iso8601ToClock(string $iso): string
    {
        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $iso, $m);
        $h = (int)($m[1] ?? 0);
        $i = (int)($m[2] ?? 0);
        $s = (int)($m[3] ?? 0);
        if ($h > 0) return sprintf('%d:%02d:%02d', $h, $i, $s);
        return sprintf('%d:%02d', $i, $s);
    }

    /** Sync via the YouTube Data API (requires an API key). */
    private function syncFromApi(): ?string
    {
        $playlistId = $this->uploadsPlaylistId();
        if (!$playlistId) {
            return 'YouTube sync failed: could not resolve uploads playlist.';
        }

        $data = $this->get(
            'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,contentDetails&maxResults='
            . $this->maxResults . '&playlistId=' . urlencode($playlistId) . '&key=' . $this->apiKey
        );
        $items = $data['items'] ?? [];
        if (!$items) {
            return 'YouTube sync: no items returned.';
        }

        $ids = array_column(array_map(fn($i) => $i['contentDetails'] ?? [], $items), 'videoId');
        $durations = $this->durations($ids);

        $count = 0;
        foreach ($items as $i => $item) {
            $sn  = $item['snippet'] ?? [];
            $vid = $item['contentDetails']['videoId'] ?? null;
            if (!$vid) continue;

            $thumb = $sn['thumbnails']['maxres']['url']
                  ?? $sn['thumbnails']['high']['url']
                  ?? $sn['thumbnails']['medium']['url']
                  ?? '';

            Video::upsertFromYoutube([
                'youtube_id'   => $vid,
                'title'        => $sn['title'] ?? 'Untitled',
                'description'  => $sn['description'] ?? '',
                'thumb'        => (string)$thumb,
                'duration'     => $durations[$vid] ?? '',
                'view_count'   => 0, // needs videos.list statistics — not fetched here (quota)
                'published_at' => date('Y-m-d H:i:s', strtotime($sn['publishedAt'] ?? 'now')),
                'position'     => $i,
            ]);
            $count++;
        }

        setting_set('youtube_last_sync', now());
        return "Synced {$count} videos from YouTube.";
    }

    /**
     * Auto-sync when the cache is stale. Safe to call on every page load:
     * does nothing (no network) until the cache window elapses.
     */
    public function ensureFresh(): void
    {
        if (!$this->enabled()) return;

        $cacheMinutes = max(1, (int)config('youtube.cache_minutes'));
        $last = (string)setting('youtube_last_sync', '');
        if ($last !== '' && strtotime($last) + $cacheMinutes * 60 > time()) {
            return;
        }

        try {
            $this->run();
        } catch (\Throwable $e) {
            // Never let a sync failure break a public page.
        }
    }

    /**
     * Run the sync. RSS first (keyless); falls back to the API when a key exists.
     */
    public function run(): ?string
    {
        if (!$this->enabled()) {
            return null;
        }

        $rss = $this->syncFromRss();
        if ($rss !== null && strpos($rss, 'failed') === false) {
            return $rss;
        }

        if ($this->apiKey !== '') {
            return $this->syncFromApi() ?? 'YouTube sync failed.';
        }

        return $rss;
    }
}
