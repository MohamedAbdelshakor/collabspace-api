<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

final class ErrorEnvelopeTest extends TestCase
{
    public function test_non_existent_route_returns_standard_error_envelope(): void
    {
        $response = $this->getJson('/api/v1/non-existent-endpoint');

        $response->assertNotFound()
            ->assertJsonStructure([
                'message',
                'code',
            ])
            ->assertJson([
                'code' => 'NOT_FOUND',
                'message' => 'Resource not found.',
            ]);
    }
}
