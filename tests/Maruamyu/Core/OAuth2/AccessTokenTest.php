<?php

namespace Maruamyu\Core\OAuth2;

class AccessTokenTest extends \PHPUnit\Framework\TestCase
{
    public function test_initialize()
    {
        $tokenData = [
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
        $accessToken = new AccessToken($tokenData);
        $this->assertEquals($tokenData['access_token'], $accessToken->getToken());
        $this->assertEquals($tokenData['token_type'], $accessToken->getType());
        $this->assertGreaterThanOrEqual(new \DateTime(), $accessToken->getExpireAt());
    }

    public function test_toArray()
    {
        $tokenData = [
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
        $accessToken = new AccessToken($tokenData);
        $this->assertEquals($tokenData, $accessToken->toArray());
    }
}
