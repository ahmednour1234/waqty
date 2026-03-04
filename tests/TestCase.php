<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        if (empty(config('jwt.secret'))) {
            config(['jwt.secret' => 'test-secret-key-for-jwt-auth-testing-only']);
        }
        
        if (empty(config('app.key'))) {
            config(['app.key' => 'base64:' . base64_encode(str_repeat('a', 32))]);
        }
    }
    
    protected function getTokenFromLoginResponse($response): string
    {
        $response->assertStatus(200);
        $token = $response->json('data.token');
        if (empty($token)) {
            $this->fail('Failed to get token from login response. Response: ' . $response->getContent());
        }
        return $token;
    }
}
