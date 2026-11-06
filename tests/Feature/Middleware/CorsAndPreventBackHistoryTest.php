<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorsAndPreventBackHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_origin_api_request_does_not_receive_wildcard_cors_headers()
    {
        $this->call('POST', '/api/login', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost',
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'missing@example.test',
            'password' => 'invalid-password',
        ]))
            ->assertStatus(401)
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_unconfigured_cross_origin_preflight_is_not_allowed()
    {
        $this->call('OPTIONS', '/api/login', [], [], [], [
            'HTTP_ORIGIN' => 'https://untrusted.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type,authorization',
        ])
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_authenticated_web_response_keeps_cache_control_without_cors_headers()
    {
        $this->actingAs(User::factory()->create())
            ->get('/home')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
