<?php

namespace Maruamyu\Core\OAuth2;

class SettingsTest extends \PHPUnit\Framework\TestCase
{
    public function test_createFromOpenIDProviderMetadata()
    {
        # Google's OpenID metadata
        $metadata = [
            'issuer' => 'https://accounts.google.com',
            'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_endpoint' => 'https://www.googleapis.com/oauth2/v4/token',
            'userinfo_endpoint' => 'https://www.googleapis.com/oauth2/v3/userinfo',
            'revocation_endpoint' => 'https://accounts.google.com/o/oauth2/revoke',
            'jwks_uri' => 'https://www.googleapis.com/oauth2/v3/certs',
            'response_types_supported' => ['code', 'token', 'id_token', 'code token', 'code id_token', 'token id_token', 'code token id_token', 'none'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'email', 'profile'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
            'claims_supported' => ['aud', 'email', 'email_verified', 'exp', 'family_name', 'given_name', 'iat', 'iss', 'locale', 'name', 'picture', 'sub'],
            'code_challenge_methods_supported' => ['plain', 'S256']
        ];
        $settings = Settings::createFromOpenIDProviderMetadata($metadata);

        $this->assertInstanceOf(Settings::class, $settings);
        $this->assertEquals('https://accounts.google.com/o/oauth2/v2/auth', $settings->authorizationEndpoint);
        $this->assertEquals('https://www.googleapis.com/oauth2/v4/token', $settings->tokenEndpoint);
        $this->assertNull($settings->tokenIntrospectionEndpoint);
        $this->assertEquals('https://accounts.google.com/o/oauth2/revoke', $settings->revocationEndpoint);
        $this->assertFalse($settings->isRequiredClientCredentialsOnRevocationRequest);
        $this->assertFalse($settings->isUseBasicAuthorizationOnClientCredentialsRequest);
    }
}
