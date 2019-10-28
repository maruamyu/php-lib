<?php

namespace Maruamyu\Core\OAuth2;

class JsonWebTokenTest extends \PHPUnit\Framework\TestCase
{
    public function test_validatePayload()
    {
        $payload = [
            'iss' => 'openid.example.jp',
            'aud' => 'client_id',
            'sub' => 'user_id',
            'exp' => (time() + 3600),
        ];
        $this->assertTrue(JsonWebToken::validatePayload($payload, 'openid.example.jp', 'client_id'));

        $invalidPayload1 = [
            'iss' => 'invalid.issuer.example.jp',
            'aud' => 'client_id',
            'sub' => 'user_id',
            'exp' => (time() + 3600),
        ];
        $this->assertFalse(JsonWebToken::validatePayload($invalidPayload1, 'openid.example.jp', 'client_id'));

        $invalidPayload2 = [
            'iss' => 'openid.example.jp',
            'aud' => 'invalid_client_id',
            'sub' => 'user_id',
            'exp' => (time() + 3600),
        ];
        $this->assertFalse(JsonWebToken::validatePayload($invalidPayload2, 'openid.example.jp', 'client_id'));

        $invalidPayload3 = [
            'iss' => 'openid.example.jp',
            'aud' => 'client_id',
            'sub' => '',
            'exp' => (time() + 3600),
        ];
        $this->assertFalse(JsonWebToken::validatePayload($invalidPayload3, 'openid.example.jp', 'client_id'));

        $invalidPayload4 = [
            'iss' => 'openid.example.jp',
            'aud' => 'client_id',
            'sub' => 'user_id',
            'exp' => (time() - 3600),
        ];
        $this->assertFalse(JsonWebToken::validatePayload($invalidPayload4, 'openid.example.jp', 'client_id'));
    }
}
