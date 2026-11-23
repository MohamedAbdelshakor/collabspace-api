<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ExampleTest extends TestCase
{
    public function test_root_redirects_to_api_documentation(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/docs/api');
    }
}
