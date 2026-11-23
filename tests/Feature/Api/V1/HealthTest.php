<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

final class HealthTest extends TestCase
{
    public function test_health_check_returns_successful_payload(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'status',
                    'timestamp',
                    'version',
                    'services' => [
                        'database',
                        'cache',
                    ],
                ],
                'message',
            ])
            ->assertJsonPath('data.status', 'healthy')
            ->assertJsonPath('data.services.database', 'connected');
    }

    public function test_api_respects_accept_language_header(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/health');

        $response->assertOk()
            ->assertHeader('Content-Language', 'ar');
    }
}
