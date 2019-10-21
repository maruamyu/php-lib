<?php

namespace Maruamyu\Core\OAuth2;

class JsonWebTokenTest extends \PHPUnit\Framework\TestCase
{
    public function test_validatePayload()
    {
        $metadata = $this->getOpenIDProviderMetadata();
        $payload = [
            'iss' => $metadata->issuer,
            'aud' => $metadata->clientId,
            'sub' => 'user_id',
            'exp' => (time() + 3600),
        ];
        $this->assertTrue(JsonWebToken::validatePayload($payload, $metadata));

        $invalidPayload1 = [
            'iss' => 'invalid.issuer.example.jp',
            'aud' => $metadata->clientId,
            'sub' => 'user_id',
            'exp' => (time() + 3600),
        ];
        $this->assertFalse(JsonWebToken::validatePayload($invalidPayload1, $metadata));

        $invalidPayload2 = [
            'iss' => $metadata->issuer,
            'aud' => 'invalid_client_id',
            'sub' => 'user_id',
            'exp' => (time() + 3600),
        ];
        $this->assertFalse(JsonWebToken::validatePayload($invalidPayload2, $metadata));

        $invalidPayload3 = [
            'iss' => $metadata->issuer,
            'aud' => $metadata->clientId,
            'sub' => '',
            'exp' => (time() + 3600),
        ];
        $this->assertFalse(JsonWebToken::validatePayload($invalidPayload3, $metadata));

        $invalidPayload4 = [
            'iss' => $metadata->issuer,
            'aud' => $metadata->clientId,
            'sub' => 'user_id',
            'exp' => (time() - 3600),
        ];
        $this->assertFalse(JsonWebToken::validatePayload($invalidPayload4, $metadata));
    }

    /**
     * @return OpenIDProviderMetadata
     */
    private function getOpenIDProviderMetadata()
    {
        $metadata = new OpenIDProviderMetadata();
        $metadata->issuer = 'openid.example.jp';
        $metadata->clientId = 'client_id';
        $metadata->clientSecret = 'client_secret';
        $metadata->authorizationEndpoint = 'https://openid.example.jp/oauth2/authorization';
        $metadata->tokenEndpoint = 'https://openid.example.jp/oauth2/token';
        return $metadata;
    }
}
