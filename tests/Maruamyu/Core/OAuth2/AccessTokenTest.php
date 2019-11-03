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
        $this->assertNull($accessToken->getExpireAt());
    }

    public function test_iat_exp()
    {
        $tokenData = [
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
        $accessToken = new AccessToken($tokenData);
        $this->assertNull($accessToken->getExpireAt());

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

    public function test_toJson()
    {
        $tokenData = [
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
        $accessToken = new AccessToken($tokenData);

        $this->assertJson($accessToken->toJson());

        $expectJson = json_encode($tokenData, JSON_UNESCAPED_SLASHES);
        $this->assertJsonStringEqualsJsonString($expectJson, $accessToken->toJson());
    }

    public function test_toString()
    {
        $tokenData = [
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
        $accessToken = new AccessToken($tokenData);

        $this->assertJson(strval($accessToken));

        $expectJson = json_encode($tokenData, JSON_UNESCAPED_SLASHES);
        $this->assertJsonStringEqualsJsonString($expectJson, strval($accessToken));
    }
}
