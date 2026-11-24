<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\FetchLinkPreviewJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class FetchLinkPreviewJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_private_ips_and_localhost_for_ssrf_protection(): void
    {
        $maliciousUrls = [
            'http://localhost/admin',
            'http://127.0.0.1:8000',
            'http://169.254.169.254/latest/meta-data',
            'http://192.168.1.1/router',
            'http://10.0.0.1/internal',
            'ftp://example.com/file',
        ];

        foreach ($maliciousUrls as $url) {
            $job = new FetchLinkPreviewJob($url);
            $job->handle();

            $this->assertDatabaseMissing('link_previews', [
                'url' => $url,
            ]);
        }
    }

    public function test_fetches_and_persists_valid_public_html_metadata(): void
    {
        $targetUrl = 'https://example.com/article';

        Http::fake([
            $targetUrl => Http::response(
                '<!DOCTYPE html>
                <html>
                <head>
                    <title>Fallback Title</title>
                    <meta property="og:title" content="OpenGraph Article Title">
                    <meta property="og:description" content="Detailed preview of the article.">
                    <meta property="og:image" content="https://example.com/thumbnail.png">
                </head>
                <body>Hello</body>
                </html>',
                200
            ),
        ]);

        $job = new FetchLinkPreviewJob($targetUrl);
        $job->handle();

        $this->assertDatabaseHas('link_previews', [
            'url' => $targetUrl,
            'title' => 'OpenGraph Article Title',
            'description' => 'Detailed preview of the article.',
            'image_url' => 'https://example.com/thumbnail.png',
        ]);
    }
}
