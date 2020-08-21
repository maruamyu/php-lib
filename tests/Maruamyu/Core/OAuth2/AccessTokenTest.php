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
        $this->assertEquals('access_token', $accessToken->getToken());
        $this->assertEquals('Bearer', $accessToken->getType());
        $this->assertEquals(3600, $accessToken->getExpiresIn());
        $this->assertNull($accessToken->getExpiresAt());
    }

    public function test_expiresAt_with_iat()
    {
        $tokenData = [
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'iat' => 1234567890,
        ];
        $accessToken = new AccessToken($tokenData);
        $expiresAt = $accessToken->getExpiresAt();
        $this->assertEquals((1234567890 + 3600), $expiresAt->getTimestamp());
    }

    public function test_expiresAt_with_issuedAt()
    {
        $tokenData = [
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
        $issuedAt = \DateTime::createFromFormat('U', 1234567890);
        $accessToken = new AccessToken($tokenData, $issuedAt);
        $expiresAt = $accessToken->getExpiresAt();
        $this->assertEquals((1234567890 + 3600), $expiresAt->getTimestamp());
    }

    public function test_expiresAt_with_exp()
    {
        $tokenData = [
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'exp' => 1234567890,
        ];
        $accessToken = new AccessToken($tokenData);
        $expiresAt = $accessToken->getExpiresAt();
        $this->assertEquals(1234567890, $expiresAt->getTimestamp());
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
