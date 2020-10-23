<?php

namespace Tests\Feature\v1\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ApiTestCase;

class LoginControllerApiTest extends ApiTestCase {

    public function test_able_to_login() {
        $response = $this->withHeaders($this->headers)
            ->json('POST', '/api/v1/login', [
                'email' => 'alexiuslim1994@gmail.com',
                'password' => '123456789'
            ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'tokenType',
                'expiresIn',
                'accessToken',
                'refreshToken',
                'user'
            ]
        ]);
    }

    public function test_login_with_invalid_email() {
        $response = $this->withHeaders($this->headers)
            ->json('POST', '/api/v1/login', [
                'email' => 'example@gmail.com',
                'password' => '123456789'
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email'
            ],
        ]);
    }

    public function test_login_with_invalid_password() {
        $response = $this->getInvalidPasswordResponse();
        
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_login_with_no_input() {
        $response = $this->withHeaders($this->headers)
            ->json('POST', '/api/v1/login', []);
        
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
                'password'
            ],
        ]);
    }

    public function test_login_throttling() {
        // loop api call 5 times to trigger max attempt
        for ($i = 0; $i < 5; ++$i) {
            // response with invalid password
            $this->getInvalidPasswordResponse();
        }
        
        // 6th time should trigger lockout event
        $response = $this->getInvalidPasswordResponse();
        // 429 - Too Many Requests
        $response->assertStatus(429);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
            ],
        ]);
    }

    protected function getInvalidPasswordResponse() {
        return $this->withHeaders($this->headers)
                ->json('POST', '/api/v1/login', [
                    'email' => 'alexiuslim1994@gmail.com',
                    'password' => 'invalidpassword'
                ]);
    }
}
