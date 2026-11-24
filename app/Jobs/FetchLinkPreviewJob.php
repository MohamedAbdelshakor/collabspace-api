<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\LinkPreview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

final class FetchLinkPreviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $url) {}

    public function handle(): void
    {
        if (! $this->isSafePublicUrl($this->url)) {
            return;
        }

        $urlHash = hash('sha256', $this->url);

        // Check if already fetched
        if (LinkPreview::where('url_hash', $urlHash)->exists()) {
            return;
        }

        try {
            $response = Http::timeout(3)
                ->withHeaders(['User-Agent' => 'CollabspaceBot/1.0 (+https://collabspace.test)'])
                ->get($this->url);

            if (! $response->successful()) {
                return;
            }

            $html = (string) $response->body();
            if (strlen($html) > 500000) {
                $html = substr($html, 0, 500000);
            }

            $title = $this->extractMetaOrTag($html, 'og:title', 'title');
            $description = $this->extractMetaOrTag($html, 'og:description', 'description');
            $image = $this->extractMetaOrTag($html, 'og:image');

            LinkPreview::updateOrCreate(
                ['url_hash' => $urlHash],
                [
                    'url' => $this->url,
                    'title' => $title ? substr($title, 0, 255) : null,
                    'description' => $description ? substr($description, 0, 1000) : null,
                    'image_url' => $image ? substr($image, 0, 1000) : null,
                    'fetched_at' => now(),
                ]
            );
        } catch (Throwable) {
            // Fail silently on unreachable / non-HTML external links
        }
    }

    /**
     * Ensure the URL is public and protects against SSRF (no localhost, private IPs, or AWS metadata).
     */
    private function isSafePublicUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (! isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'];
        $ip = gethostbyname($host);

        // Validate IP and reject private / reserved ranges (including 127.0.0.1, 10.x, 192.168.x, 169.254.169.254)
        $isPublicIp = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        return $isPublicIp !== false;
    }

    private function extractMetaOrTag(string $html, string $metaProperty, ?string $tagName = null): ?string
    {
        // Try og:property first
        if (preg_match('/<meta[^>]+property=["\']'.preg_quote($metaProperty, '/').'["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Try name="property"
        if (preg_match('/<meta[^>]+name=["\']'.preg_quote($metaProperty, '/').'["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Fallback to HTML tag like <title>...</title>
        if ($tagName === 'title' && preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }
}
